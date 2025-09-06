

(function () {
  document.addEventListener('DOMContentLoaded', () => {
    // ----- Estado global -----
    let tablero = null;                   // instancia de chessboard.js
    let juego = null;                     // instancia de chess.js
    let modoEdicion = false;              // true = colocar/borrar piezas libremente
    let grabando = false;                 // true = acumular SAN/PGN
    let fenInicial = null;                // FEN pieza-placement inicial visual (para volver a "inicio")
    const pilaUndo = [];                  // histórico de FEN (pieza-placement)
    const pilaRedo = [];
    let fenGrabacionInicial = null;
    // Locks
    let _recordBusy = false;              // evita reentradas/doble click
    let _suspendChange = false;
    let isSaving = false;   // ← nuevo: congelar handlers mientras guardamos

    // ----- DOM -----
    const form = document.getElementById('formGuardarEjercicio');
    const elTablero = document.getElementById('board');
    const btnEditar = document.getElementById('btnEdit');
    const btnGrabar = document.getElementById('btnRecord');
    const btnInicio = document.getElementById('btnStart');
    const btnAtras = document.getElementById('btnBack');
    const btnAdel = document.getElementById('btnForward');
    const btnPaste = document.getElementById('btnPasteFen');
    const btnCopyFen = document.getElementById('btnCopyFen');
    const btnCopyPgn = document.getElementById('btnCopyPgn');

    const selTurno = document.getElementById('sideToMove'); // 'w' o 'b'
    const inFen = document.getElementById('fen_inicial');   // FEN completo que guardas en BD
    const inPgn = document.getElementById('pgn_solucion');


    if (!elTablero) return;

    // ----- Librerías -----
    const ctorChess = window.Chess;
    if (typeof ctorChess !== 'function') {
      console.error('⚠️ chess.js no está disponible (window.Chess). Cárgalo antes de este script.');
      return;
    }

    // ----- SweetAlert2 helpers -----
    const SA_TOAST = window.Swal ? window.Swal.mixin({
      toast: true, position: 'top-end', timer: 1500, timerProgressBar: true, showConfirmButton: false
    }) : null;
    function toastInfo(msg) { SA_TOAST ? SA_TOAST.fire({ icon: 'info', title: msg }) : console.log('[toast]', msg); }
    function toastOk(msg) { SA_TOAST ? SA_TOAST.fire({ icon: 'success', title: msg }) : console.log('[ok]', msg); }
    function toastErr(msg) { SA_TOAST ? SA_TOAST.fire({ icon: 'error', title: msg }) : console.error('[err]', msg); }

    // ----- Rutas / CSRF -----
    const RUTA_CREAR = (window.BASE_URL || '/') + 'admin/ejerciciosCrear';
    const RUTA_SOLUCION = (window.BASE_URL || '/') + 'admin/ejerciciosSolucionGuardar';
    function getCsrfToken() {
      return document.querySelector('input[name="csrf"]')?.value
        || document.querySelector('meta[name="csrf-token"]')?.content
        || '';
    }

    // ----- Helpers generales -----
    // Devuelve FEN COMPLETO usando el tablero visible y el selector de turno
    function fenFromBoardAndTurn() {
      const rawPieces = Chessboard.objToFen(tablero.position());
      const pieces = normPieces(rawPieces);
      const turno = (selTurno?.value === 'b') ? 'b' : 'w';
      return `${pieces} ${turno} - - 0 1`;
    }

    const fenSoloPiezas = () => {
      const raw = Chessboard.objToFen(tablero.position());
      return normPieces(raw);
    };

    const fenCompleto = (fenPieces, turno = (selTurno?.value === 'b' ? 'b' : 'w')) =>
      `${normPieces(fenPieces)} ${turno} - - 0 1`;

    function parseFenParts(fen) {
      const p = (fen || '').trim().split(/\s+/);
      return { pieces: p[0] || 'start', turn: (p[1] === 'b' ? 'b' : 'w') };
    }
    function cargarDesdeFen(fen) {
      try { juego.load(fen); } catch (e) { console.warn('FEN inválido:', fen, e); return false; }
      const parts = parseFenParts(juego.fen());
      // Dibuja tablero
      tablero.position(parts.pieces, true);
      // Ajusta turno + orientación
      if (selTurno) selTurno.value = parts.turn;
      if (typeof tablero.orientation === 'function')
        tablero.orientation(parts.turn === 'b' ? 'black' : 'white');
      // Normaliza input FEN
      if (inFen) inFen.value = `${parts.pieces} ${parts.turn} - - 0 1`;
      return true;
    }


    function normPieces(p) {
      return (!p || p === 'start')
        ? 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR'
        : p;
    }

    // Devuelve PGN limpio (sin cabeceras), colapsado en una línea
    function pgnLimpio(game) {
      if (!game || typeof game.pgn !== 'function') return '';
      const raw = game.pgn(); // chess.js
      return String(raw)
        .replace(/\[[^\]]*\]\s*/g, ' ') // quita [Headers]
        .replace(/\s+/g, ' ')
        .trim();
    }



    const setBotonesNav = () => {
      if (btnAtras) btnAtras.disabled = pilaUndo.length <= 1;
      if (btnAdel) btnAdel.disabled = pilaRedo.length === 0;
    };

    const pushEstado = (fenPieces) => {
      if (pilaUndo[pilaUndo.length - 1] !== fenPieces) {
        pilaUndo.push(fenPieces);
        pilaRedo.length = 0;
      }
      setBotonesNav();
      if (inFen && !grabando) inFen.value = fenCompleto(fenPieces);
    };
    function parseFenParts(fen) {
      const p = (fen || '').trim().split(/\s+/);
      return {
        pieces: p[0] || 'start',
        turn: (p[1] === 'b' ? 'b' : 'w')
      };
    }

    function validarFenPiecesBasico(fenPieces) {
      const errores = [];
      const filas = fenPieces.trim().split('/');
      if (filas.length !== 8) { errores.push('El FEN debe tener 8 filas separadas por “/”.'); return errores; }
      let wK = 0, bK = 0, wP = 0, bP = 0;
      for (let idxFila = 0; idxFila < 8; idxFila++) {
        const fila = filas[idxFila];
        let ancho = 0;
        for (const ch of fila) {
          if (/[1-8]/.test(ch)) {
            ancho += parseInt(ch, 10);
          } else if (/[prnbqkPRNBQK]/.test(ch)) {
            ancho += 1;
            if (ch === 'K') wK++;
            if (ch === 'k') bK++;
            if (ch === 'P') { wP++; const rank = 8 - idxFila; if (rank === 1 || rank === 8) errores.push(`Peón blanco en fila ${rank} (no permitido).`); }
            if (ch === 'p') { bP++; const rank = 8 - idxFila; if (rank === 1 || rank === 8) errores.push(`Peón negro en fila ${rank} (no permitido).`); }
          } else {
            errores.push(`Carácter inválido en FEN: “${ch}”.`);
          }
        }
        if (ancho !== 8) errores.push(`La fila ${8 - idxFila} no suma 8 casillas (suma ${ancho}).`);
      }
      if (wK !== 1) errores.push(`Debe haber exactamente 1 rey blanco (hay ${wK}).`);
      if (bK !== 1) errores.push(`Debe haber exactamente 1 rey negro (hay ${bK}).`);
      if (wP > 8) errores.push(`Demasiados peones blancos (${wP} > 8).`);
      if (bP > 8) errores.push(`Demasiados peones negros (${bP} > 8).`);
      return errores;
    }

    function uiGrabacion(enCurso) {
      grabando = !!enCurso;
      if (!btnGrabar) return;
      // Estado persistente en el DOM
      btnGrabar.dataset.recording = enCurso ? '1' : '0';
      btnGrabar.classList.toggle('btn-info', enCurso);
      btnGrabar.classList.toggle('btn-outline-info', !enCurso);
      btnGrabar.textContent = enCurso ? 'Grabando…' : 'Grabar solución';
    }

    function getEjercicioId() {
      const v = document.getElementById('ejercicio_id')?.value?.trim();
      const id = v ? parseInt(v, 10) : NaN;
      return Number.isFinite(id) && id > 0 ? id : null;
    }

    function normalizaNivel(v) {
      const key = (v || '').toString().toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');
      const map = { iniciacion: 'Iniciación', iniciación: 'Iniciación', intermedio: 'Intermedio', avanzado: 'Avanzado' };
      return map[key] || 'Iniciación';
    }

    // ====== Guardados (crear vs editar) ======
    async function guardarEjercicioFetch() {
      if (!form) throw new Error('Falta <form id="form-ejercicio">');
      const fd = new FormData(form); // usa name="*" del formulario

      // Título obligatorio
      const tituloInput = form.querySelector('[name="titulo"]');
      if (!tituloInput || !tituloInput.value.trim()) {
        await window.Swal?.fire({ icon: 'warning', title: 'Título requerido', text: 'Escribe un título.' });
        throw new Error('Título vacío');
      }

      // Nivel normalizado (por si el value del select no coincide)
      const nivelInput = form.querySelector('[name="nivel"]');
      const nivel = normalizaNivel(nivelInput?.value || 'Iniciación');
      fd.set('nivel', nivel);

      // FEN/PGN desde UI/motor
      const fen = (inFen?.value?.trim()) || (typeof juego?.fen === 'function' ? juego.fen() : '');
      const pgn = (inPgn?.value?.trim()) || '';
      fd.set('fen_inicial', fen);
      fd.set('pgn_solucion', pgn);

      // Turno (compat con controlador)
      const turnoSel = (selTurno?.value === 'b') ? 'b' : 'w';
      fd.set('turno', turnoSel);
      fd.set('sideToMove', turnoSel);

      if (!fd.has('csrf')) fd.set('csrf', getCsrfToken());

      console.debug('[save:create] POST', RUTA_CREAR, {
        titulo: fd.get('titulo'), nivel: fd.get('nivel'), pub: fd.get('es_publico'), turno: fd.get('turno')
      });

      const res = await fetch(RUTA_CREAR, { method: 'POST', body: fd, redirect: 'follow', credentials: 'same-origin' });

      if (res.redirected) {
        try { await window.Swal?.fire({ toast: true, position: 'top-end', timer: 900, showConfirmButton: false, icon: 'success', title: 'Ejercicio creado' }); } catch { }
        window.location.assign(res.url);
        return;
      }

      const html = await res.text().catch(() => '');
      const mFlash = html.match(/id=["']?flash_error["']?[^>]*>([\s\S]*?)<\/div>/i);
      const msg = mFlash ? mFlash[1].replace(/<[^>]+>/g, ' ').trim() : 'El servidor devolvió el formulario sin redirigir. Comprueba título, nivel y FEN.';
      await window.Swal?.fire({ icon: 'warning', title: 'Revisa el formulario', text: msg });
      throw new Error('Validación servidor');
    }

    async function guardarSolucion(id) {
      if (!id) { console.warn('[save:update] sin id -> crear'); return guardarEjercicioFetch(); }
      const fen = (inFen?.value?.trim()) || (typeof juego?.fen === 'function' ? juego.fen() : '');
      const pgn = (inPgn?.value?.trim()) || '';

      console.debug('[save:update] POST', RUTA_SOLUCION, { id });
      const res = await fetch(RUTA_SOLUCION, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch' },
        credentials: 'same-origin',
        body: JSON.stringify({ id: Number(id), fen_inicial: fen, pgn_solucion: pgn, _token: getCsrfToken() })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) throw new Error(data?.error || `HTTP ${res.status}`);
      toastOk('¡Solución guardada!');
    }

    async function guardarAuto() {
      const id = getEjercicioId();
      console.debug('[save] modo =', id ? 'edicion' : 'crear', 'id=', id);
      if (id) await guardarSolucion(id); else await guardarEjercicioFetch();
    }

    // ====== Grabación ======
    function iniciarGrabacion() {
      // FEN base: el del input si existe; si no, el del tablero+turno
      const baseFen = (inFen?.value?.trim()) || fenFromBoardAndTurn();
      fenGrabacionInicial = baseFen; // ← foto inicial

      // Carga en motor y posiciona tablero en SOLO piezas del FEN base
      try { juego.load(baseFen); } catch { juego.reset(); }
      const basePieces = juego.fen().split(' ')[0];
      tablero.position(basePieces, false);

      // Limpia PGN solo al iniciar grabación
      if (inPgn) inPgn.value = '';

      // Ajusta selector de turno (no forcemos orientación aquí)
      if (selTurno) selTurno.value = juego.turn();

      uiGrabacion(true);
      (window.Swal?.mixin({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false }) || { fire: () => { } }).fire({ icon: 'info', title: 'Grabación iniciada' });
    }




    // Antes: detenía y llamaba a guardarAuto() → redirigía
    // Sustituye tu window.detenerGrabacion por esto:
    window.detenerGrabacion = async function () {
      try {
        const movs = (typeof juego.history === 'function') ? juego.history() : [];
        const limpio = pgnLimpio(juego);
        if (inPgn) inPgn.value = limpio || '';

        const base = fenGrabacionInicial || fenFromBoardAndTurn();
        uiGrabacion(false);

        // Fuerza restauración al FEN base (y normaliza input)
        cargarDesdeFen(base);

        if (!movs.length) {
          await Swal.fire({ icon: 'info', title: 'Sin jugadas grabadas', text: 'Haz al menos una jugada.' });
        } else {
          await Swal.mixin({ toast: true, position: 'top-end', timer: 1500, showConfirmButton: false })
            .fire({ icon: 'success', title: 'PGN volcado' });
        }
      } catch (e) {
        console.error(e);
        Swal.fire({ icon: 'error', title: 'Error al finalizar la grabación' });
      }
    };



    // ====== Tablero ======
    function recrearTablero({ draggable, sparePieces }) {
      const posActual = tablero ? Chessboard.objToFen(tablero.position()) : 'start';
      elTablero.innerHTML = '';

      tablero = Chessboard(elTablero, {
        draggable,
        position: posActual,
        sparePieces,
        dropOffBoard: sparePieces ? 'trash' : 'snapback',
        pieceTheme: (window.BASE_URL || '/') + 'Assets/vendor/chessboard/piezas/{piece}.png',

        onDragStart: (source, piece) => {
          if (modoEdicion) return true;
          if (!piece || juego.game_over?.()) return false;
          return true;
        },

        onDrop: (from, to) => {
          if (modoEdicion) return;
          if (!from || !to || from === to || from === 'offboard' || to === 'offboard' || from === 'spare' || to === 'spare') return 'snapback';

          let mv; try { mv = juego.move({ from, to, promotion: 'q' }); } catch { mv = null; }
          if (!grabando && inFen) inFen.value = juego.fen();   // ← solo cuando no grabas
          if (grabando && inPgn) inPgn.value = pgnLimpio(juego);  // ← PGN en vivo
          if (!mv) return 'snapback';

          const fenPieces = juego.fen().split(' ')[0];
          tablero.position(fenPieces, false);
          if (selTurno) selTurno.value = juego.turn();
          if (!grabando && inFen) {
            // solo fuera de grabación actualizamos el FEN del formulario
            inFen.value = juego.fen();
          }
          if (grabando && inPgn && typeof juego?.pgn === 'function') {
            // durante la grabación, solo actualizamos el PGN en vivo
            inPgn.value = pgnLimpio(juego);
          }
        },

        onChange: (oldPos, newPos) => {
          if (isSaving) return;                           // ← añade esto
          const fenPieces = Chessboard.objToFen(newPos);
          pushEstado(fenPieces);
          if (modoEdicion && inPgn) inPgn.value = '';
        }



      });

      if (!fenInicial) fenInicial = posActual;
      if (pilaUndo.length === 0) pushEstado(posActual);
    }

    // ----- Arranque -----
    (function iniciar() {
      juego = new ctorChess();
      recrearTablero({ draggable: true, sparePieces: false });

      const fenAttr = elTablero?.dataset?.fen || '';
      const ok = fenAttr ? cargarDesdeFen(fenAttr) : (inFen?.value ? cargarDesdeFen(inFen.value) : false);
      if (!ok) cargarDesdeFen("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1");

      console.log('[init] FEN cargado =>', inFen?.value);
    })();


    // ===== Botón: Posición inicial =====
    btnInicio?.addEventListener('click', () => {
      modoEdicion = false;
      juego.reset();
      recrearTablero({ draggable: true, sparePieces: false });
      tablero.start();
      pilaUndo.length = 0; pilaRedo.length = 0;
      pushEstado(fenSoloPiezas());
      if (inPgn) inPgn.value = '';
      if (selTurno) selTurno.value = 'w';
      tablero.orientation('white');
    });

    // ===== Botón: Modo edición (toggle) =====
    btnEditar?.addEventListener('click', () => {
      modoEdicion = !modoEdicion;
      if (modoEdicion) {
        // ENTRANDO en edición
        recrearTablero({ draggable: true, sparePieces: true });
        tablero.clear();
        if (inPgn) inPgn.value = '';                  // ← mover aquí el borrado
        btnEditar.textContent = 'En edición';
        toastInfo('Modo edición activo');
      } else {
        // SALIENDO de edición (no borres PGN aquí)
        let fenFinal = (inFen?.value?.trim()) || fenFromBoardAndTurn();
        if (!cargarDesdeFen(fenFinal)) {
          return Swal.fire({ icon: 'error', title: 'FEN inválido', text: 'Revisa el FEN.' });
        }
        recrearTablero({ draggable: true, sparePieces: false });
        tablero.position(juego.fen().split(' ')[0], false);
        btnEditar.classList.add('btn-outline-light');
        btnEditar.classList.remove('btn-light');
        btnEditar.textContent = 'Modo edición';
        SA_TOAST?.fire({ icon: 'success', title: 'Edición desactivada' });
      }
    });


    // ===== Botón: Grabar solución (toggle) =====
    // Evita enlazar dos veces si el script se carga repetido
    if (btnGrabar) btnGrabar.dataset.recording = '0';

    if (btnGrabar && btnGrabar.dataset.bound !== '1') {
      btnGrabar.dataset.bound = '1';
      btnGrabar.addEventListener('click', () => {
        if (_recordBusy) return; _recordBusy = true;
        try {
          if (modoEdicion) {
            Swal.fire({ icon: 'warning', title: 'Estás en edición', text: 'Sal del modo edición para grabar.' });
            return;
          }
          const estaGrabando = btnGrabar.dataset.recording === '1';
          if (!estaGrabando) iniciarGrabacion(); else window.detenerGrabacion();
        } finally { _recordBusy = false; }
      });
    }



    // ===== UNDO/REDO =====
    btnAtras?.addEventListener('click', () => {
      if (pilaUndo.length > 1) {
        const actual = pilaUndo.pop();
        pilaRedo.push(actual);
        const previa = pilaUndo[pilaUndo.length - 1];
        tablero.position(previa);
        if (!modoEdicion) {
          try { juego.load(fenCompleto(previa)); } catch { }
          if (grabando && inPgn) inPgn.value = pgnLimpio(juego);
        }
        setBotonesNav();
      } else {
        window.Swal?.fire('⚠️ Sin movimientos', 'No hay más movimientos para deshacer.', 'warning');
      }
    });

    btnAdel?.addEventListener('click', () => {
      if (pilaRedo.length > 0) {
        const fen = pilaRedo.pop();
        pilaUndo.push(fen);
        tablero.position(fen);
        if (!modoEdicion) {
          try { juego.load(fenCompleto(fen)); } catch { }
          if (grabando && inPgn) inPgn.value = pgnLimpio(juego);
        }
        setBotonesNav();
      } else {
        window.Swal?.fire('⚠️ Sin movimientos', 'No hay movimientos para rehacer.', 'warning');
      }
    });
    btnFlip?.addEventListener('click', () => {
      if (!tablero || typeof tablero.orientation !== 'function') return;
      const ori = tablero.orientation();
      tablero.orientation(ori === 'white' ? 'black' : 'white');
    });

    // ===== Cambiar turno manualmente =====
    selTurno?.addEventListener('change', () => {
      const turno = (selTurno.value === 'b') ? 'b' : 'w';
      if (tablero) tablero.orientation(turno === 'b' ? 'black' : 'white');
      const fenPiezas = Chessboard.objToFen(tablero.position());
      const fenCompletoTurno = fenCompleto(fenPiezas, turno);
      if (!grabando && inFen) inFen.value = obtenerFenActualCompleto();
      if (!modoEdicion) {
        try { juego.load(fenCompletoTurno); } catch { }
        if (grabando && inPgn) inPgn.value = ''; // reinicia PGN si cambiaste el turno en mitad de grabación
        // ❌ no meter headers nunca
      }
    });
    // ----- Auto-cargar FEN manualmente escrito -----
    document.getElementById('btnLoadFen')?.addEventListener('click', () => {
      inFen.dispatchEvent(new Event('change'));
    });
    if (inFen) {
      inFen.addEventListener('change', () => {
        const fenText = inFen.value.trim();
        if (!fenText) return;

        try {
          // validar/cargar en chess.js
          juego.load(fenText);
          // actualizar tablero con solo la parte de piezas
          const fenPieces = juego.fen().split(' ')[0];
          tablero.position(fenPieces, false);
          // ajustar turno en el selector
          if (selTurno) selTurno.value = juego.turn();
          console.log('[panel] FEN cargado desde input:', fenText);
        } catch (e) {
          console.warn('[panel] FEN inválido desde input:', fenText, e);
          window.Swal?.fire({
            icon: 'error',
            title: 'FEN inválido',
            text: 'El tablero no pudo cargar la posición escrita.'
          });
        }
      });
    }
    // --- Guardar: asegúrate de fijar FEN/PGN antes de enviar ---
    if (form && !form.dataset.submitBound) {
      form.dataset.submitBound = '1';
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Si estaba grabando, primero paramos y restauramos al FEN base
        if (btnGrabar?.dataset.recording === '1') {
          try { await window.detenerGrabacion(); } catch { }
        }

        // Congela handlers visuales hasta que navegue
        isSaving = true;

        // Asegura valores definitivos
        if (!inFen?.value?.trim()) inFen.value = fenFromBoardAndTurn();
        if (!inPgn?.value?.trim()) {
             try { inPgn.value = pgnLimpio(juego) || ''; } catch { }
           }

        form.submit(); // navega
      });
    }





    // ===== Pegar FEN =====
    btnPaste?.addEventListener('click', async () => {
      if (!tablero || !juego) return;
      const { value: texto } = await window.Swal.fire({
        title: 'Pegar FEN', input: 'text', inputLabel: 'Pega un FEN completo o solo la parte de piezas',
        inputPlaceholder: 'Ej: rnbqkbnr/pppppppp/8/... w KQkq - 0 1', inputValue: '', showCancelButton: true,
        confirmButtonText: 'Cargar', cancelButtonText: 'Cancelar', inputValidator: (v) => !v?.trim() ? 'Introduce un FEN' : undefined
      });
      if (!texto) return;

      let fenPieces = ''; let fenFull = '';
      const trozos = texto.trim().split(/\s+/);
      if (trozos.length >= 2) { fenPieces = trozos[0]; fenFull = texto.trim(); if (selTurno && (trozos[1] === 'w' || trozos[1] === 'b')) selTurno.value = trozos[1]; }
      else { fenPieces = texto.trim(); fenFull = fenCompleto(fenPieces); }

      const errs = validarFenPiecesBasico(fenPieces);
      if (errs.length) { window.Swal.fire({ icon: 'error', title: 'FEN inválido', html: '<ul style="text-align:left;margin:0;padding-left:18px">' + errs.map(e => `<li>${e}</li>`).join('') + '</ul>' }); return; }
      try { juego.load(fenFull); } catch (e) { window.Swal.fire({ icon: 'error', title: 'FEN inválido', text: 'El motor no pudo cargar ese FEN.' }); return; }

      tablero.position(juego.fen().split(' ')[0]);
      if (inFen) inFen.value = juego.fen();
      if (inPgn) inPgn.value = '';
      pilaUndo.length = 0; pilaRedo.length = 0; pilaUndo.push(juego.fen().split(' ')[0]); setBotonesNav?.();
      tablero.orientation(juego.turn() === 'b' ? 'black' : 'white');
      SA_TOAST?.fire({ icon: 'success', title: 'FEN cargado' });
    });

    // ===== Copiar FEN/PGN =====
    async function copiarAlPortapapeles(texto, tituloOK = 'Copiado') {
      try {
        await navigator.clipboard.writeText(texto);
        SA_TOAST?.fire({ icon: 'success', title: tituloOK });
      } catch (e) {
        const ta = document.createElement('textarea');
        ta.value = texto; ta.style.position = 'fixed'; ta.style.left = '-9999px';
        document.body.appendChild(ta); ta.focus(); ta.select();
        const ok = document.execCommand('copy'); ta.remove();
        if (ok) SA_TOAST?.fire({ icon: 'success', title: tituloOK }); else window.Swal?.fire({ icon: 'error', title: 'No se pudo copiar', text: 'Cópialo manualmente.' });
      }
    }

    function obtenerFenActualCompleto() {
      if (modoEdicion) {
        const fenPiezas = Chessboard.objToFen(tablero.position());
        const turno = (selTurno && selTurno.value === 'b') ? 'b' : 'w';
        return `${fenPiezas} ${turno} - - 0 1`;
      } else { return juego.fen(); }
    }

    btnCopyFen?.addEventListener('click', async () => {
      const fen = fenFromBoardAndTurn();
      if (inFen) inFen.value = fen;
      await copiarAlPortapapeles(fen, 'FEN copiado al formulario');
    });



    btnCopyPgn?.addEventListener('click', async () => {
      // Prioriza lo que ya volcamos al textarea al parar la grabación
      const txtPgn = (inPgn?.value || '').trim();
      const desdeMotor = pgnLimpio(juego);
      const texto = txtPgn || desdeMotor;

      if (!texto) {
        return window.Swal?.fire({
          icon: 'info',
          title: 'Sin PGN',
          text: 'Graba al menos una jugada entre “Grabar” y “Parar”.'
        });
      }
      await copiarAlPortapapeles(texto, 'PGN copiado');
    });



    // ===== Debug helper =====
    window._rec = {
      get grabando() { return grabando; },
      start() { if (!modoEdicion) iniciarGrabacion(); },
      stop() { window.detenerGrabacion(); },
      toggle() { if (modoEdicion) return; grabando ? window.detenerGrabacion() : iniciarGrabacion(); },
      state() { return { grabando, modoEdicion, fen: juego?.fen?.(), pgn: juego?.pgn?.() }; }
    };

  });
})();

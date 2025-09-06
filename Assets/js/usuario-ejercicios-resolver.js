// Assets/js/usuario-ejercicios-resolver.js
(function () {
  'use strict';

  const $ = (s, p=document) => p.querySelector(s);

  const BASE_URL = (window.BASE_URL || '/');
  const ASIG_ID  = Number(window.ASIG_ID || 0);
  const FEN_FULL = (window.EJ_FEN || 'start');
  const TURN     = (window.EJ_TURNO === 'b') ? 'b' : 'w';
  const CSRF     = window.CSRF || window.CSRF_TOKEN || '';

  let juego = null;        // chess.js
  let tablero = null;      // chessboard.js
  let segundos = 0;        // cronómetro
  let timerId = null;

  document.addEventListener('DOMContentLoaded', init);

  function init() {
    const boardEl = $('#board');
    if (!boardEl || !window.Chess || !window.Chessboard) {
      console.error('Falta #board o librerías (chess.js / chessboard.js)');
      return;
    }

    // UI auxiliar (si no existe, la creo)
    ensureToolbar(boardEl);

    // Inicia motor
    juego = new window.Chess();
    try {
      juego.load(FEN_FULL);
    } catch {
      // si viene "start" o un FEN corto
      if (FEN_FULL === 'start') juego.reset();
      else { try { juego.load(FEN_FULL + ' ' + TURN + ' - - 0 1'); } catch { juego.reset(); } }
    }

    // Inicia tablero
    const fenPieces = juego.fen().split(' ')[0];
    tablero = window.Chessboard(boardEl, {
      position: fenPieces,
      draggable: true,
      orientation: (TURN === 'b') ? 'black' : 'white',
      pieceTheme: (BASE_URL) + 'Assets/vendor/chessboard/piezas/{piece}.png',
      onDragStart: (_src, piece) => {
        if (!piece || juego.game_over?.()) return false;
        return true;
      },
      onDrop: (from, to) => {
        if (!from || !to || from === to) return 'snapback';
        let mv;
        try { mv = juego.move({ from, to, promotion: 'q' }); } catch { mv = null; }
        if (!mv) return 'snapback';
        const fp = juego.fen().split(' ')[0];
        tablero.position(fp, false);
        updatePgn();
      }
    });

    // Cronómetro
    startTimer();

    // Botones
    $('#btn-undo')?.addEventListener('click', undoOne);
    $('#btn-reset')?.addEventListener('click', resetPosition);
    $('#btn-copy')?.addEventListener('click', copyPgn);
    $('#btn-submit')?.addEventListener('click', entregar);

    updatePgn();
  }

  function ensureToolbar(boardEl) {
    // Crea una mini barra si no existe
    let wrap = boardEl.parentElement;
    if (!wrap) return;

    // Reutiliza si ya existe
    if ($('.resolver-toolbar', wrap)) return;

    const bar = document.createElement('div');
    bar.className = 'resolver-toolbar d-flex flex-wrap align-items-center gap-2 my-2';
    bar.innerHTML = `
      <button id="btn-undo"  type="button" class="btn btn-outline-light btn-sm">◀ Deshacer</button>
      <button id="btn-reset" type="button" class="btn btn-secondary btn-sm">Reiniciar</button>
      <button id="btn-copy"  type="button" class="btn btn-outline-secondary btn-sm">Copiar PGN</button>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="badge bg-dark"><i class="bi bi-stopwatch"></i> <span id="timer">00:00</span></span>
        <button id="btn-submit" type="button" class="btn btn-primary btn-sm">
          <i class="bi bi-send"></i> Entregar
        </button>
      </div>
      <textarea id="pgn_user" class="form-control mt-2 bg-dark text-light" rows="2" readonly
        placeholder="PGN generado automáticamente..."></textarea>
    `;
    wrap.appendChild(bar);
  }

  function updatePgn() {
    const ta = $('#pgn_user');
    if (!ta || !juego) return;
    // PGN sin cabeceras
    const raw = (typeof juego.pgn === 'function') ? juego.pgn() : '';
    const limpio = String(raw).replace(/\[[^\]]*\]\s*/g, ' ').replace(/\s+/g, ' ').trim();
    ta.value = limpio;
  }

  function undoOne() {
    if (!juego) return;
    juego.undo();
    const fp = juego.fen().split(' ')[0];
    tablero?.position(fp, false);
    updatePgn();
  }

  function resetPosition() {
    if (!juego) return;
    try { juego.load(FEN_FULL); } catch { juego.reset(); }
    const fp = juego.fen().split(' ')[0];
    tablero?.position(fp, false);
    updatePgn();
  }

  function startTimer() {
    stopTimer();
    timerId = setInterval(() => {
      segundos++;
      const m = Math.floor(segundos / 60);
      const s = segundos % 60;
      const t = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
      const el = $('#timer');
      if (el) el.textContent = t;
    }, 1000);
  }
  function stopTimer() { if (timerId) clearInterval(timerId); timerId = null; }

  async function copyPgn() {
    try {
      await navigator.clipboard.writeText($('#pgn_user')?.value || '');
      window.Swal?.fire({toast:true,position:'top-end',timer:1200,showConfirmButton:false,icon:'success',title:'PGN copiado'});
    } catch {
      window.Swal?.fire({icon:'info',title:'No se pudo copiar',text:'Cópialo manualmente'});
    }
  }

  async function entregar() {
    if (!ASIG_ID) {
      return window.Swal?.fire({icon:'error',title:'No se puede entregar',text:'Falta ID de asignación'});
    }
    const pgn = ($('#pgn_user')?.value || '').trim();
    if (!pgn) {
      return window.Swal?.fire({icon:'info',title:'Aún no hay jugadas',text:'Realiza al menos un movimiento.'});
    }

    // Confirmar
    const ok = await window.Swal?.fire({
      title: '¿Entregar ejercicio?',
      text: 'Después no podrás editar este intento.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, entregar',
      cancelButtonText: 'Cancelar'
    });
    if (ok && ok.isDismissed) return;

    try {
      const res = await fetch(BASE_URL + 'UsuarioEjercicios/entregarPost', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: new URLSearchParams({
          csrf: CSRF,
          asignacion_id: String(ASIG_ID),
          pgn_intento: pgn,
          tiempo_segundos: String(segundos)
        })
      });
      const j = await res.json();
      if (!j.ok) throw new Error(j.error || 'No se pudo entregar');

      const icon = j.correcto ? 'success' : 'info';
      const tit  = j.correcto ? '¡Correcto!' : 'Enviado';
      const txt  = j.correcto
        ? 'Tu solución coincide con la oficial.'
        : (j.mensaje || 'Tu intento se ha enviado al profesor.');

      await window.Swal?.fire({ icon, title: tit, text: txt });
      window.location.assign(BASE_URL + 'UsuarioEjercicios/index');
    } catch (e) {
      window.Swal?.fire({icon:'error',title:'Error al entregar',text:String(e.message||e)});
    }
  }
})();

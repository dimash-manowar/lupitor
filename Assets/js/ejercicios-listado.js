'use strict';

document.addEventListener('DOMContentLoaded', () => {
  // ========= Referencias de la página =========
  const doc = document;
  const formulario = doc.getElementById('form-filtros');
  const contenedorLista = doc.getElementById('ex-list');
  const contenedorPaginacion = doc.getElementById('ex-pag');
  const contador = doc.getElementById('ex-contador');

  if (!formulario || !contenedorLista || !contenedorPaginacion) return;

  // ========= Utilidades generales =========
  const hayChessboard = () => typeof window.Chessboard === 'function';
  const hayChess = () => typeof window.Chess === 'function';

  function esFinDePartidaCompatible(juego) {
    if (juego && typeof juego.isGameOver === 'function') return juego.isGameOver();
    if (juego && typeof juego.game_over === 'function') return juego.game_over();
    return false;
  }

  function ponerCargando(valor) {
    contenedorLista.classList.toggle('is-loading', !!valor);
    contenedorPaginacion.classList.toggle('is-loading', !!valor);
  }

  function calcularRango(total, pagina, porPagina) {
    if (!total) return { ini: 0, fin: 0 };
    const ini = (pagina - 1) * porPagina + 1;
    const fin = Math.min(pagina * porPagina, total);
    return { ini, fin };
  }

  function actualizarContador(total, pagina, porPagina) {
    if (!contador) return;
    const { ini, fin } = calcularRango(Number(total || 0), Number(pagina || 1), Number(porPagina || 9));
    contador.textContent =
      total > 0 ? `Mostrando ${ini}–${fin} de ${total} ejercicios` : 'No hay ejercicios que coincidan con tu búsqueda';
  }

  function desplazarAResultados() {
    const y = formulario.getBoundingClientRect().top + window.scrollY - 12;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }

  // ========= Parser de PGN de solución (en español) =========
  // Devuelve { movimientos: SAN[], fenBase: string|null }
  function parsearPgnSolucion(pgnBruto, fenCompleto) {
    const Chess = window.Chess;
    if (typeof Chess !== 'function') return { movimientos: [], fenBase: null };
    if (!pgnBruto || !String(pgnBruto).trim()) return { movimientos: [], fenBase: null };

    const crudo = String(pgnBruto);

    // 1) Si el PGN trae cabecera FEN, úsala como base
    const mFen = crudo.match(/\[FEN\s+"([^"]+)"\]/i);
    const fenBase = mFen ? mFen[1] : null;

    // 2) Limpia cabeceras y comentarios, normaliza espacios
    const cuerpo = crudo
      .replace(/\[[^\]]*\]\s*/g, ' ')
      .replace(/\{[^}]*\}/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();

    // 3) Tokeniza
    const tokens = cuerpo.split(' ');

    // 4) Mapeo SAN español → inglés (D,Q / T,R / A,B / C,N / R,K) sólo si empieza por letra de pieza
    const mapearSAN = (tok) => tok.replace(/^[DTACR]/, (c) => ({ D: 'Q', T: 'R', A: 'B', C: 'N', R: 'K' }[c]));

    // 5) Aplica sobre base
    const tmp = new Chess();
    try {
      tmp.load(fenBase || fenCompleto);
    } catch {
      return { movimientos: [], fenBase: fenBase || null };
    }

    const movimientos = [];
    for (const t of tokens) {
      const t0 = t.trim();
      // Ignora números de jugada y resultados
      if (!t0 || /^(\d+\.*|\.{3}|1-0|0-1|1\/2-1\/2|\*)$/.test(t0)) continue;
      const san = mapearSAN(t0);
      const mv = tmp.move(san, { sloppy: true });
      if (!mv) break;
      movimientos.push(mv.san);
    }

    return { movimientos, fenBase: fenBase || null };
  }




  // ========= Tableros en las cards =========
  function inicializarTablerosEjercicio(scope = doc) {
    if (!hayChessboard() || !hayChess()) {
      console.warn('[tableros] Faltan Chessboard.js / chess.js');
      return;
    }

    const Chess = window.Chess;
    const raiz = scope instanceof Element ? scope : doc;
    const tarjetas = raiz.querySelectorAll('.exercise-card[data-fen]');

    tarjetas.forEach((tarjeta) => {
      if (tarjeta.dataset.boardInited === '1') return; // evitar doble init

      try {
        const fenCrudo = (tarjeta.dataset.fen || '').trim();
        const pgnCrudo = (tarjeta.dataset.pgn || '').trim();
        const elTablero = tarjeta.querySelector('.exercise-board');
        if (!fenCrudo || !elTablero) return;

        // ---- FEN inicial
        let partes = fenCrudo.split(/\s+/);
        let disposicion = partes[0];
        let turno = partes[1] === 'b' ? 'b' : 'w';
        let fenCompleto = partes.length >= 2 ? fenCrudo : `${disposicion} ${turno} - - 0 1`;

        // ---- Estado
        const SA = window.Swal || null;
        const Toast = SA?.mixin({
          toast: true,
          position: 'top-end',
          timer: 1400,
          timerProgressBar: true,
          showConfirmButton: false,
        });

        const juego = new Chess();
        try {
          juego.load(fenCompleto);
        } catch (e) {
          console.warn('[tablero] FEN inválido', fenCompleto, e);
          return;
        }

        let ilegalesSeguidas = 0;
        let bloqueado = false;
        let paso = 0;
        let intentos = 0;

        function aviso(icono, titulo) {
          if (Toast) Toast.fire({ icon: icono, title: titulo });
        }
        function modal(icono, titulo, texto = '') {
          if (SA) SA.fire({ icon: icono, title: titulo, text: texto });
        }

        // Solución (PGN → SAN sobre FEN)
        let movimientosSol = [];
        try {
          const r = parsearPgnSolucion(pgnCrudo, fenCompleto);
          movimientosSol = Array.isArray(r?.movimientos) ? r.movimientos : [];
          if (r?.fenBase) {
            fenCompleto = r.fenBase;
            const hdr = fenCompleto.split(/\s+/);
            disposicion = hdr[0];
            turno = hdr[1] === 'b' ? 'b' : 'w';
            try {
              juego.load(fenCompleto);
            } catch { }
          }
        } catch {
          movimientosSol = [];
        }

        // Crear tablero
        const tablero = window.Chessboard(elTablero, {
          draggable: true,
          position: disposicion, // sólo placement (sin campos extra)
          orientation: turno === 'b' ? 'black' : 'white',
          pieceTheme:
            (window.BASE_URL || '/') + 'Assets/vendor/chessboard/piezas/{piece}.png',

          onDragStart(origen, pieza) {
            if (bloqueado) return false;
            if (esFinDePartidaCompatible(juego)) return false;
            const color = pieza && pieza[0] === 'w' ? 'w' : 'b';
            return color === juego.turn();
          },

          onDrop(origen, destino) {
            try {
              if (bloqueado) return 'snapback';
              const mov = juego.move({ from: origen, to: destino, promotion: 'q' });
              if (!mov) {
                ilegalesSeguidas++;
                if (ilegalesSeguidas >= 2) {
                  bloqueado = true;
                  SA?.fire({
                    icon: 'error',
                    title: 'Dos jugadas ilegales',
                    text: 'Pulsa "Nuevo intento" para reintentar.',
                  });
                } else {
                  SA?.mixin({
                    toast: true,
                    position: 'top-end',
                    timer: 1400,
                    showConfirmButton: false,
                  }).fire({ icon: 'warning', title: `Movimiento ilegal (${ilegalesSeguidas}/2)` });
                }
                return 'snapback';
              }
              ilegalesSeguidas = 0;

              // Avance según solución
              if (movimientosSol.length && mov.san === movimientosSol[paso]) {
                paso = Math.min(paso + 1, movimientosSol.length);
                if (paso === movimientosSol.length) {
                  bloqueado = true;
                  SA?.fire({ icon: 'success', title: '¡Correcto!', text: 'Has completado la solución.' });
                }
              } else if (movimientosSol.length) {
                SA?.mixin({
                  toast: true,
                  position: 'top-end',
                  timer: 1400,
                  showConfirmButton: false,
                }).fire({ icon: 'info', title: 'Jugada legal, pero no es la solución' });
              }
            } catch (e) {
              console.error('[onDrop] error', e);
              return 'snapback';
            }
          },

          onSnapEnd() {
            try {
              tablero.position(juego.fen().split(' ')[0]);
            } catch { }
          },
        });

        // Controles UI
        const btnAtras = tarjeta.querySelector('.js-ex-back');
        const btnAdelante = tarjeta.querySelector('.js-ex-forward');
        const btnPista = tarjeta.querySelector('.js-ex-hint');
        const btnSolucion = tarjeta.querySelector('.js-ex-solution');
        const btnReintentar = tarjeta.querySelector('.js-ex-retry');
        const spanIntentos = tarjeta.querySelector('.js-ex-attempts');
        if (spanIntentos) spanIntentos.textContent = String(intentos);

        // Habilitar/deshabilitar según si hay solución reconocible
        function pareceSecuenciaMovidas(s) {
          if (!s) return false;
          const body = s.replace(/\[[^\]]*\]/g, ' ').trim();
          return /O-O(?:-O)?|[KQRBN]?[a-h]?[1-8]?x?[a-h][1-8](?:=[QRBN])?[+#]?/.test(body);
        }
        const hayUIdeSolucion = movimientosSol.length > 0 || pareceSecuenciaMovidas(pgnCrudo);
        [btnAtras, btnAdelante, btnPista, btnSolucion].forEach((b) => b && (b.disabled = !hayUIdeSolucion));

        // Utilidades de control
        function reproducirHastaPaso(n) {
          if (!movimientosSol.length) return;
          n = Math.max(0, Math.min(n, movimientosSol.length));
          const tmp = new Chess();
          try {
            tmp.load(fenCompleto);
          } catch {
            return;
          }
          for (let i = 0; i < n; i++) {
            try {
              tmp.move(movimientosSol[i], { sloppy: true });
            } catch {
              break;
            }
          }
          juego.load(tmp.fen());
          tablero.position(juego.fen().split(' ')[0]);
          paso = n;
        }

        function reintentarEjercicio(incrementar = true) {
          try {
            juego.load(fenCompleto);
          } catch (e) {
            console.warn('[reset] FEN inválido', e);
          }
          tablero.orientation(turno === 'b' ? 'black' : 'white');
          tablero.position(disposicion);
          paso = 0;
          ilegalesSeguidas = 0;
          bloqueado = false;
          if (incrementar) {
            intentos++;
            if (spanIntentos) spanIntentos.textContent = String(intentos);
          }
        }

        function reproducirSiguientePaso() {
          if (bloqueado || !movimientosSol.length) return;

          // si te has desviado, no avanzamos
          const objetivo = (function fenSolucionEn(k) {
            const t = new Chess();
            try {
              t.load(fenCompleto);
            } catch {
              return null;
            }
            for (let i = 0; i < k; i++) {
              try {
                t.move(movimientosSol[i], { sloppy: true });
              } catch {
                return null;
              }
            }
            return t.fen();
          })(paso);

          if (!objetivo || juego.fen().split(' ')[0] !== objetivo.split(' ')[0]) {
            SA?.fire({
              icon: 'info',
              title: 'Te has desviado',
              text: 'Pulsa "Nuevo intento" para reintentar.',
            });
            return;
          }

          if (paso >= movimientosSol.length) return;

          try {
            const mv = juego.move(movimientosSol[paso], { sloppy: true });
            if (!mv) return;
            paso++;
            tablero.position(juego.fen().split(' ')[0]);
            if (paso === movimientosSol.length) {
              bloqueado = true;
              SA?.fire({ icon: 'success', title: '¡Correcto!', text: 'Has completado la solución.' });
            }
          } catch { }
        }

        // Listeners botones
        btnAtras?.addEventListener('click', () => {
          if (!bloqueado && movimientosSol.length) {
            const n = Math.max(0, paso - 1);
            reproducirHastaPaso(n);
          }
        });
        btnAdelante?.addEventListener('click', () => reproducirSiguientePaso());
        btnPista?.addEventListener('click', () => reproducirSiguientePaso());
        btnSolucion?.addEventListener('click', () => {
          if (!movimientosSol.length) return;
          reproducirHastaPaso(movimientosSol.length);
          bloqueado = true;
          SA?.fire({ icon: 'success', title: '¡Correcto!', text: 'Has completado la solución.' });
        });
        btnReintentar?.addEventListener('click', () => {
          reintentarEjercicio(true);
          SA?.mixin({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false }).fire({
            icon: 'info',
            title: 'Nuevo intento',
          });
        });

        // Marca para evitar reinicializaciones
        tarjeta.dataset.boardInited = '1';
      } catch (err) {
        console.error('[tablero] Error en tarjeta:', err);
      }
    });
  }

  // ========= Listado + filtros (AJAX) =========
  function inicializarListadoEjerciciosPublicos(scope = doc) {
    const inputNivel = formulario.querySelector('select[name="nivel"]');
    const inputBusqueda = formulario.querySelector('input[name="q"]');
    const inputOrden = formulario.querySelector('select[name="orden"]');
    const inputPorPagina = formulario.querySelector('select[name="per"]');

    let temporizador = null;
    const esperar = (fn, ms = 350) => (...args) => {
      clearTimeout(temporizador);
      temporizador = setTimeout(() => fn(...args), ms);
    };
    // Lee el endpoint base del data-attr (fallback por si faltara)
    function obtenerBaseAjax() {
      const base = formulario?.dataset?.base || (window.BASE_URL + 'Home/index/');
      return base.endsWith('/') ? base : base + '/';
    }
    function urlPagina1() {
      const base = obtenerBaseAjax();
      return new URL(base + '1', window.location.origin).toString();
    }

    function construirUrl(desdeUrl = null) {
      const base = obtenerBaseAjax();
      // si me pasan una URL (por ejemplo, desde la paginación del servidor), úsala;
      // si no, parte de la base + página 1 (el número real lo pondrán los enlaces)
      const u = new URL(desdeUrl || base, window.location.origin);

      const nivel = (inputNivel?.value || '').trim();
      const q = (inputBusqueda?.value || '').trim();
      const ord = (inputOrden?.value || 'recientes').trim();
      const per = (inputPorPagina?.value || '9').trim();

      if (nivel) u.searchParams.set('nivel', nivel); else u.searchParams.delete('nivel');
      if (q) u.searchParams.set('q', q); else u.searchParams.delete('q');
      u.searchParams.set('orden', ord);
      u.searchParams.set('per', per);
      u.searchParams.set('ajax', '1');
      return u.toString();
    }

    async function cargarAjax(destinoUrl, reemplazarHistorial = false) {
      ponerCargando(true);
      const url = construirUrl(destinoUrl);
      try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' });

        // ——— debug preventivo: si no es JSON, muestra el principio de la respuesta
        const tipo = (res.headers.get('content-type') || '').toLowerCase();
        if (!tipo.includes('application/json')) {
          const texto = await res.text();
          throw new Error('Respuesta no JSON. Inicio: ' + texto.slice(0, 200));
        }

        const datos = await res.json();
        contenedorLista.innerHTML = datos.cards || '';
        contenedorPaginacion.innerHTML = datos.pagination || '';
        inicializarTablerosEjercicio(contenedorLista);
        actualizarContador(datos.total, datos.page, datos.per);
        desplazarAResultados();

        const limpia = new URL(url); limpia.searchParams.delete('ajax');
        if (reemplazarHistorial) history.replaceState({}, '', limpia.toString());
        else history.pushState({}, '', limpia.toString());
      } catch (e) {
        console.error('[ajax] fallo', e);
        contenedorLista.innerHTML = '<div class="alert alert-warning">No se pudo cargar el listado.</div>';
      } finally {
        ponerCargando(false);
      }
    }

    // Eventos de filtros
    inputNivel?.addEventListener('change', () => cargarAjax(urlPagina1()));
    inputOrden?.addEventListener('change', () => cargarAjax(urlPagina1()));
    inputPorPagina?.addEventListener('change', () => cargarAjax(urlPagina1()));
    inputBusqueda?.addEventListener('input', esperar(() => cargarAjax(urlPagina1()), 400));

    // Envío del formulario (aplica y va a página 1)
    formulario.addEventListener('submit', (ev) => {
      ev.preventDefault();
      cargarAjax(urlPagina1());
    });

    // Paginación (intercepta enlaces)
    contenedorPaginacion.addEventListener('click', (ev) => {
      const a = ev.target.closest('a.page-link');
      if (!a) return;
      const href = a.getAttribute('href');
      if (!href) return;
      // sólo intercepta si es misma origen
      const u = new URL(href, window.location.origin);
      if (u.origin !== window.location.origin) return;
      ev.preventDefault();
      cargarAjax(href);
    });

    // Historial (back/forward)
    window.addEventListener('popstate', () => cargarAjax(window.location.href, true));
  }

  // ========= Arranque =========
  try {
    inicializarTablerosEjercicio(doc);
  } catch (e) {
    console.error('[inicializarTablerosEjercicio] fallo', e);
  }
  try {
    inicializarListadoEjerciciosPublicos(doc);
  } catch (e) {
    console.error('[inicializarListadoEjerciciosPublicos] fallo', e);
  }

  // Exponer por si necesitas depurar desde consola
  window.inicializarTablerosEjercicio = inicializarTablerosEjercicio;
  window.inicializarListadoEjerciciosPublicos = inicializarListadoEjerciciosPublicos;
  window.parsearPgnSolucion = parsearPgnSolucion;
});

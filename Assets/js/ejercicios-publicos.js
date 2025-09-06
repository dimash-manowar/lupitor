// Assets/js/ejercicios-publicos.js — modo resolver
(() => {
    'use strict';

    const PIEZAS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/chessboard.js/1.0.0/img/chesspieces/wikipedia/{piece}.png';
    const START_PIECES = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR';

    // ---------- utilidades ----------
    function inyectarCSS() {
        if (document.getElementById('ex-css')) return;
        const s = document.createElement('style');
        s.id = 'ex-css';
        s.textContent = `
      .ex-hl { box-shadow: inset 0 0 0 3px rgba(255,215,0,.9) !important; }
      .exercise-board { position:relative }
    `;
        document.head.appendChild(s);
    }

    function placementDesdeFen(fenOStart) {
        if (!fenOStart || fenOStart === 'start') return START_PIECES;
        return fenOStart.split(' ')[0];
    }

    function fenCompleto(placement, turno) {
        return `${placement} ${turno} - - 0 1`;
    }

    function juegoSeguroDesdeFen(fenFullOStart) {
        const g = new Chess();
        if (!fenFullOStart || fenFullOStart === 'start') return g;
        try { g.load(fenFullOStart); } catch { g.reset(); }
        return g;
    }

    function pgnASAN(fenFull, pgn) {
        if (!pgn || !pgn.trim()) return [];
        const tmp = juegoSeguroDesdeFen(fenFull);
        const ok = tmp.load_pgn(pgn, { sloppy: true });
        if (!ok) return [];
        return tmp.history(); // array SAN
    }

    function colorOpuesto(c) { return c === 'w' ? 'b' : 'w'; }

    function buscarMovPorDesdeHasta(g, desde, hasta, promo = null) {
        const leg = g.moves({ verbose: true });
        return leg.find(m => m.from === desde && m.to === hasta && (promo ? m.promotion === promo : true));
    }

    function sanEsperadaAFromTo(fenFull, san) {
        const tmp = juegoSeguroDesdeFen(fenFull);
        const mv = tmp.move(san, { sloppy: true });
        if (!mv) return null;
        return { from: mv.from, to: mv.to, promotion: mv.promotion || null };
    }

    function resaltarMovimiento(boardEl, from, to, ms = 900) {
        const a = boardEl.querySelector(`.square-${from}`),
            b = boardEl.querySelector(`.square-${to}`);
        [a, b].forEach(el => el && el.classList.add('ex-hl'));
        setTimeout(() => [a, b].forEach(el => el && el.classList.remove('ex-hl')), ms);
    }

    // ---------- una tarjeta ----------
    function initTarjeta(cardEl) {
        const boardEl = cardEl.querySelector('.exercise-board');
        if (!boardEl) return null;

        const fenAttr = (cardEl.dataset.fen || 'start').trim();
        const pgnAttr = (cardEl.dataset.pgn || '').trim();

        // turno inicial
        let turnoIni = 'w';
        if (fenAttr.includes(' ')) {
            const t = fenAttr.split(/\s+/)[1];
            turnoIni = (t === 'b') ? 'b' : 'w';
        }

        const placementIni = placementDesdeFen(fenAttr);
        const fenFullIni = fenAttr.includes(' ') ? fenAttr : fenCompleto(placementIni, turnoIni);

        // motor y tablero
        let juego = juegoSeguroDesdeFen(fenFullIni);
        let tablero = Chessboard(boardEl, {
            draggable: true, // MODO RESOLVER: se puede arrastrar
            position: placementIni,
            orientation: (turnoIni === 'b') ? 'black' : 'white',
            pieceTheme: PIEZAS_CDN,
            onDragStart: (origen, pieza/*wP*/, pos, orient) => {
                // Bloqueos: sin solución o ya completado → evitar líos
                if (completado || solucionSAN.length === 0) return false;
                // Solo quien mueve según el índice actual de solución
                const turno = juego.turn();              // 'w' | 'b'
                const colorPieza = pieza ? pieza[0] : null; // 'w' | 'b'
                if (colorPieza !== turno) {
                    avisoTurno(turno);
                    return false;
                }
                // Además: que el color de la pieza coincida con el esperado por la solución
                const esperadoColor = colorEsperado(idxSol, turnoIni);
                if (turno !== esperadoColor) {
                    avisoTurno(esperadoColor);
                    return false;
                }
                return true;
            },
            onDrop: async (desde, hasta) => {
                if (completado || solucionSAN.length === 0) return 'snapback';

                // Si hay promoción en la SAN esperada, pregúntala para comparar correctamente
                const sanExp = solucionSAN[idxSol]; // SAN esperada en este paso
                const requierePromo = /=([QRBN])/.exec(sanExp);
                let promoElegida = null;
                if (requierePromo) {
                    // solo si el movimiento del usuario es un peón que llega a última fila
                    const esPeonA8 = /^([a-h]7)[a-h]8$/i.test(desde + hasta);
                    const esPeonA1 = /^([a-h]2)[a-h]1$/i.test(desde + hasta);
                    if (esPeonA8 || esPeonA1) {
                        promoElegida = await pedirPromocion(requierePromo[1]);
                    }
                }

                // Buscar un movimiento legal que coincida con desde/hasta (+ promoción si aplica)
                const cand = buscarMovPorDesdeHasta(juego, desde, hasta, promoElegida && promoElegida.toLowerCase());
                if (!cand) {
                    // ILEGAL según reglas
                    aviso('Movimiento ilegal', 'error');
                    return 'snapback';
                }

                // SAN que intenta el usuario
                const sanUsuario = cand.san;

                // ¿Coincide con la solución esperada en este índice?
                if (sanUsuario !== sanExp) {
                    aviso('No es el movimiento correcto', 'warning');
                    // pista rápida (1s)
                    const pair = sanEsperadaAFromTo(juego.fen(), sanExp);
                    if (pair) resaltarMovimiento(boardEl, pair.from, pair.to, 900);
                    return 'snapback';
                }

                // ✅ Correcto: aplicar en el motor y en el tablero
                juego.move(cand);
                sincronizarTablero(tablero, juego);
                idxSol++;

                // ¿Queda respuesta del rival en la solución? → auto-jugarla
                if (idxSol < solucionSAN.length) {
                    const sanRival = solucionSAN[idxSol];
                    const mvRival = juego.move(sanRival, { sloppy: true });
                    if (mvRival) {
                        setTimeout(() => {
                            sincronizarTablero(tablero, juego);
                        }, 250);
                        idxSol++;
                    }
                }

                // ¿Hemos terminado?
                if (idxSol >= solucionSAN.length) {
                    completado = true;
                    aviso('¡Completado! ✔️', 'success');
                }

                // evitar snapback (ya movimos)
            }
        });

        function sincronizarTablero(board, game) {
            const place = game.fen().split(' ')[0];
            board.position(place);
        }

        function avisoTurno(turno) {
            const quien = (turno === 'w') ? 'blancas' : 'negras';
            window.Swal?.fire({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false, icon: 'info', title: `Ahora mueven las ${quien}` });
        }

        function aviso(titulo, icono) {
            window.Swal?.fire({ toast: true, position: 'top-end', timer: 1200, showConfirmButton: false, icon: icono, title: titulo });
        }

        async function pedirPromocion(defectoMayus = 'Q') {
            // SweetAlert2 selector rápido
            const { value } = await window.Swal.fire({
                title: 'Promoción',
                input: 'select',
                inputOptions: { 'q': 'Dama', 'r': 'Torre', 'b': 'Alfil', 'n': 'Caballo' },
                inputValue: defectoMayus.toLowerCase(),
                showCancelButton: false,
                confirmButtonText: 'Aceptar'
            });
            return value || 'q';
        }

        function colorEsperado(idx, turnoInicial) {
            // idx 0 → mueve turnoInicial, idx 1 → rival, etc.
            return (idx % 2 === 0) ? turnoInicial : colorOpuesto(turnoInicial);
        }

        // Controles UI de la tarjeta
        const btnAtras = cardEl.querySelector('.js-ex-back');
        const btnAdel = cardEl.querySelector('.js-ex-forward');
        const btnSol = cardEl.querySelector('.js-ex-solution');
        const btnRetry = cardEl.querySelector('.js-ex-retry');
        const btnHint = cardEl.querySelector('.js-ex-hint');   // <-- NUEVO
        const attemptsSpan = cardEl.querySelector('.js-ex-attempts');


        let solucionSAN = pgnASAN(juego.fen(), pgnAttr); // SANs desde la posición actual
        let idxSol = 0;
        let completado = false;
        let playing = false;
        let timer = null;
        let intentos = 0;

        function actualizarIntentos() { if (attemptsSpan) attemptsSpan.textContent = String(intentos); }
        function pararAuto() { clearInterval(timer); timer = null; playing = false; btnSol?.classList.remove('active'); }

        function resetear() {
            pararAuto();
            juego = juegoSeguroDesdeFen(fenFullIni);
            solucionSAN = pgnASAN(juego.fen(), pgnAttr);
            idxSol = 0; completado = false;
            tablero.orientation(turnoIni === 'b' ? 'black' : 'white');
            sincronizarTablero(tablero, juego);
            actualizarIntentos();
        }

        // Semilla
        resetear();

        // --- listeners de botones ---
        btnAtras?.addEventListener('click', () => {
            pararAuto();
            if (juego.history().length > 0) {
                juego.undo();
                // si el paso que deshicimos era del rival dentro de la solución, también retrocede el índice
                if (idxSol > 0) idxSol--;
                sincronizarTablero(tablero, juego);
            }
        });
        btnHint?.addEventListener('click', () => {
            // parar autoplay si estaba en marcha
            pararAuto();

            if (solucionSAN.length === 0) {
                aviso('Este ejercicio no tiene solución', 'info');
                return;
            }
            if (idxSol >= solucionSAN.length) {
                aviso('Ya has completado la solución', 'info');
                return;
            }

            // SAN esperada en el paso actual
            const sanExp = solucionSAN[idxSol];
            // Calcula desde/hasta SIN mover piezas
            const pair = sanEsperadaAFromTo(juego.fen(), sanExp);
            if (!pair) {
                aviso('No puedo mostrar la pista ahora', 'warning');
                return;
            }

            // Resalta casillas y muestra toast
            resaltarMovimiento(boardEl, pair.from, pair.to, 1200);
            window.Swal?.fire({
                toast: true, position: 'top-end', timer: 1400, showConfirmButton: false,
                icon: 'info', title: `Pista: ${sanExp}`
            });
        });
        btnAdel?.addEventListener('click', () => {
            pararAuto();
            if (solucionSAN.length === 0) {
                aviso('Este ejercicio no tiene solución', 'info'); return;
            }
            if (idxSol < solucionSAN.length) {
                const ok = juego.move(solucionSAN[idxSol], { sloppy: true });
                if (ok) { idxSol++; sincronizarTablero(tablero, juego); }
            }
        });

        btnSol?.addEventListener('click', () => {
            if (solucionSAN.length === 0) {
                aviso('Este ejercicio no tiene solución', 'info'); return;
            }
            if (playing) { pararAuto(); return; }
            playing = true; btnSol.classList.add('active');
            if (idxSol >= solucionSAN.length) { // si ya estaba al final
                resetear();
            }
            timer = setInterval(() => {
                if (idxSol >= solucionSAN.length) { pararAuto(); return; }
                const ok = juego.move(solucionSAN[idxSol], { sloppy: true });
                if (!ok) { pararAuto(); return; }
                idxSol++; sincronizarTablero(tablero, juego);
            }, 650);
        });

        btnRetry?.addEventListener('click', () => {
            intentos++; resetear();
        });

        // API pública mínima
        return {
            board: tablero,
            resize: () => tablero.resize()
        };
    }

    // ---------- inicializador reutilizable (para AJAX también) ----------
    window.initEjerciciosPublicos = function initEjerciciosPublicos(ctx = document) {
        inyectarCSS();
        const cards = ctx.querySelectorAll('.exercise-card');
        const instancias = [];
        cards.forEach(card => {
            const i = initTarjeta(card);
            if (i) instancias.push(i);
        });

        // Resize general
        window.addEventListener('resize', () => instancias.forEach(i => i.resize()));

        // Carrusel de Bootstrap (si existe)
        document.querySelectorAll('.carousel').forEach(car => {
            car.addEventListener('slid.bs.carousel', () => {
                setTimeout(() => instancias.forEach(i => i.resize()), 60);
            });
        });
    };

    document.addEventListener('DOMContentLoaded', () => window.initEjerciciosPublicos(document));
})();

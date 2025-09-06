(function(){
  'use strict';

  const $ = s => document.querySelector(s);

  document.addEventListener('DOMContentLoaded', () => {
    const elBoard   = $('#board');
    const btnStart  = $('#btnStart');
    const btnFlip   = $('#btnFlip');
    const btnRec    = $('#btnRec');
    const btnStop   = $('#btnStop');
    const btnEnviar = $('#btnEnviar');
    const taPGN     = $('#pgn_actual');
    const timerEl   = $('#timer');

    if (!elBoard) return;

    const ChessCtor = window.Chess;
    if (typeof ChessCtor !== 'function') {
      console.error('chess.js no disponible');
      return;
    }

    let juego = new ChessCtor();
    let tablero = null;
    let grabando = false;
    let t0 = 0, tInt = null;

    // Inicialización posición
    const fen = window.EJ_FEN || 'start';
    const turno = (window.EJ_TURNO === 'b' ? 'b' : 'w');

    function setTimer(on){
      if (on) {
        t0 = Date.now();
        clearInterval(tInt);
        tInt = setInterval(()=>{
          const s = Math.floor((Date.now() - t0)/1000);
          timerEl.textContent = s+'s';
        }, 250);
      } else {
        clearInterval(tInt);
      }
    }

    function pgnLimpio(game) {
      const raw = (game && typeof game.pgn==='function') ? game.pgn() : '';
      return String(raw).replace(/\[[^\]]*\]\s*/g,' ').replace(/\s+/g,' ').trim();
    }

    function cargarFenInicial() {
      try {
        if (fen === 'start' || fen.trim()==='') {
          juego.reset();
          if (turno === 'b') { /* no afecta a chess.js reset, pero giramos tablero */ }
        } else {
          juego.load(fen);
        }
      } catch { juego.reset(); }
      if (tablero) tablero.position(juego.fen().split(' ')[0], false);
    }

    function crearTablero() {
      const pos = fen==='start' ? 'start' : juego.fen().split(' ')[0];
      tablero = Chessboard(elBoard, {
        draggable: true,
        position: pos,
        orientation: (turno==='b' ? 'black' : 'white'),
        pieceTheme: (window.BASE_URL || '/') + 'Assets/vendor/chessboard/piezas/{piece}.png',
        onDragStart: (src,piece) => !juego.game_over?.(),
        onDrop: (from, to) => {
          if (!grabando) return 'snapback';
          let mv; try {
            mv = juego.move({ from, to, promotion: 'q' });
          } catch { mv = null; }
          if (!mv) return 'snapback';
          tablero.position(juego.fen().split(' ')[0], false);
          taPGN.value = pgnLimpio(juego);
        }
      });
    }

    function startGrab() {
      // Reinicia desde la posición de partida
      cargarFenInicial();
      taPGN.value = '';
      grabando = true;
      btnRec.disabled = true;
      btnStop.disabled = false;
      btnEnviar.disabled = true;
      setTimer(true);
    }
    function stopGrab() {
      grabando = false;
      btnRec.disabled = false;
      btnStop.disabled = true;
      btnEnviar.disabled = (taPGN.value.trim()==='');
      setTimer(false);
    }

    // Botones
    btnStart?.addEventListener('click', () => {
      cargarFenInicial();
      taPGN.value = '';
    });
    btnFlip?.addEventListener('click', () => {
      tablero?.flip();
    });
    btnRec?.addEventListener('click', startGrab);
    btnStop?.addEventListener('click', stopGrab);

    btnEnviar?.addEventListener('click', async () => {
      const pgn = taPGN.value.trim();
      if (!pgn) {
        return (window.Swal ? Swal.fire({icon:'info',title:'No hay jugadas'}) : alert('No hay jugadas'));
      }

      const cuerpo = {
        csrf: (window.CSRF || ''),
        asignacion_id: (window.ASIG_ID || 0),
        pgn_enviado: pgn,
        tiempo_seg: (timerEl.textContent||'0').replace('s','')|0
      };

      btnEnviar.disabled = true;

      try {
        const res = await fetch((window.BASE_URL||'/') + 'UsuarioEjercicios/entregarPost', {
          method: 'POST',
          headers: { 'Content-Type':'application/json', 'X-Requested-With':'fetch' },
          credentials: 'same-origin',
          body: JSON.stringify(cuerpo)
        });
        const j = await res.json();

        if (!j.ok) throw new Error(j.error || 'No se pudo entregar');

        if (window.Swal) {
          Swal.fire({
            icon: j.correcto ? 'success' : 'info',
            title: j.correcto ? '¡Correcto!' : 'Intento registrado',
            text: j.mensaje || (j.correcto ? 'Bien hecho' : 'La secuencia no coincide')
          });
        } else {
          alert(j.correcto ? '¡Correcto!' : 'Intento registrado');
        }

        if (j.intentos_max !== null && j.intentos_usados >= j.intentos_max) {
          btnRec.disabled = true;
          btnStop.disabled = true;
          btnEnviar.disabled = true;
        } else {
          btnEnviar.disabled = false;
        }
      } catch (e) {
        (window.Swal ? Swal.fire({icon:'error',title:String(e.message||e)}) : alert(String(e.message||e)));
        btnEnviar.disabled = false;
      }
    });

    // Inicio
    crearTablero();
    cargarFenInicial();
    btnEnviar.disabled = true;
  });
})();

/* Assets/js/ejercicio-editor.js */
(function () {
  'use strict';

  // ========= Helpers DOM =========
  const $ = (sel, ctx = document) => ctx.querySelector(sel);

  // ========= Elementos esperados en la vista =========
  const boardEl    = $('#board-ej1');
  const fenInput   = $('#fen_inicial');     // <input name="fen_inicial">
  const pgnInput   = $('#pgn_solucion');    // <textarea name="pgn_solucion">
  const sideSelect = $('#sideToMove');      // <select id="sideToMove"> (w/b)

  const btnEdit    = $('#btnEdit');
  const btnRecord  = $('#btnRecord');
  const btnStart   = $('#btnStart');
  const btnClear   = $('#btnClear');
  const btnFlip    = $('#btnFlip');
  const btnBack    = $('#btnBack');
  const btnForward = $('#btnForward');

  // Tema de piezas (inyectado por chess_assets_tags(); fallback local)
  const PIECE_THEME =
    (window.__PIECE_THEME_FINAL__ || window.__PIECE_THEME__ ||
      (window.__BASE_URL__ ? (window.__BASE_URL__ + 'Assets/vendor/chessboard/img/chesspieces/wikipedia/{piece}.png')
                           : '/Assets/vendor/chessboard/img/chesspieces/wikipedia/{piece}.png'));

  // ========= Estado del editor =========
  let mode = 'edit';         // 'edit' (colocar piezas) | 'record' (grabar solución)
  let board = null;          // instancia de Chessboard
  let chess = null;          // instancia de Chess (solo en modo 'record')
  let movesSAN = [];         // array de SAN para reproducción
  let step = 0;              // índice de reproducción (0..movesSAN.length)
  let baseFenFull = null;    // FEN completo base (incluye side), usado en 'record'

  // ========= Utilidades de FEN =========
  const onlyPlacement = (fen) => (fen || '').trim().split(/\s+/)[0] || ''; // solo la 1ª parte
  const currentSide = () => (sideSelect ? sideSelect.value : 'w');         // 'w' | 'b'

  // Construye FEN completo a partir de:
  // - placement: "8/8/8/8/8/8/8/8"
  // - side: 'w' | 'b'
  // Usamos campos por defecto para castle/en-passant/half/full: "- - 0 1"
  const makeFullFen = (placement, side) => {
    const p = (placement || '').trim();
    return p ? `${p} ${side || 'w'} - - 0 1` : '';
  };

  // Convierte un objeto de posición (Chessboard.position()) a placement FEN
  function objToPlacement(posObj) {
    const files = 'abcdefgh'.split('');
    let fen = '';
    for (let r = 8; r >= 1; r--) {
      let empty = 0;
      for (let f = 0; f < 8; f++) {
        const sq = files[f] + r;
        const p = posObj[sq];
        if (!p) { empty++; continue; }
        if (empty > 0) { fen += empty; empty = 0; }
        const color = p[0] === 'w' ? 'w' : 'b';
        const type = p[1].toLowerCase(); // kqrbnp
        fen += (color === 'w') ? type.toUpperCase() : type;
      }
      if (empty > 0) fen += empty;
      if (r > 1) fen += '/';
    }
    return fen;
  }

  // ========= Modo =========
  function highlightMode() {
    if (btnEdit)   btnEdit.classList.toggle('btn-primary', mode === 'edit');
    if (btnRecord) btnRecord.classList.toggle('btn-primary', mode === 'record');
  }

  function rebuildBoardEdit(position) {
    const opts = {
      pieceTheme: PIECE_THEME,
      draggable: true,
      sparePieces: true,
      dropOffBoard: 'trash',
      position: position || 'start'
    };
    if (board) board.destroy();
    board = Chessboard(boardEl, opts);
    chess = null;
    movesSAN = [];
    step = 0;
  }

  function rebuildBoardRecord(placement, side) {
    // Base FEN completo para chess.js
    baseFenFull = placement ? makeFullFen(placement, side) : undefined;
    chess = new Chess(baseFenFull); // undefined = start
    movesSAN = chess.history(); // inicialmente vacío
    step = 0;

    const opts = {
      pieceTheme: PIECE_THEME,
      draggable: true,
      position: placement || 'start',
      onDrop: onDropRecord
    };
    if (board) board.destroy();
    board = Chessboard(boardEl, opts);

    // Sincroniza FEN y PGN visibles
    if (fenInput) fenInput.value = baseFenFull ? baseFenFull : '';
    if (pgnInput) pgnInput.value = chess.pgn();
  }

  function setMode(newMode) {
    mode = newMode;
    // Obtener placement y side actuales como punto de partida
    let placement = 'start';
    let side = currentSide();

    // Si venimos de edición, convertimos obj -> placement
    if (mode === 'record') {
      const pos = board ? board.position() : 'start';
      placement = (typeof pos === 'string') ? (pos === 'start' ? 'start' : onlyPlacement(pos)) : objToPlacement(pos);
      rebuildBoardRecord(placement === 'start' ? null : placement, side);
    } else {
      // En edición: si había FEN en input, úsalo
      const fenNow = (fenInput && fenInput.value.trim()) ? fenInput.value.trim() : null;
      if (fenNow) {
        placement = onlyPlacement(fenNow);
      } else if (board) {
        const pos = board.position();
        placement = (typeof pos === 'string') ? pos : objToPlacement(pos);
      }
      rebuildBoardEdit(placement || 'start');
    }
    highlightMode();
  }

  // ========= Eventos/movimientos en modo record =========
  function onDropRecord(source, target) {
    if (!chess) return 'snapback';
    const move = chess.move({ from: source, to: target, promotion: 'q' });
    if (move === null) return 'snapback'; // jugada ilegal → volver
    movesSAN = chess.history();
    step = movesSAN.length;
    if (pgnInput) pgnInput.value = chess.pgn();
  }

  function goToStep(n) {
    if (!chess) return;
    const base = new Chess(baseFenFull); // reconstruye desde base
    const upto = Math.max(0, Math.min(n, movesSAN.length));
    for (let i = 0; i < upto; i++) base.move(movesSAN[i]);
    board.position(onlyPlacement(base.fen()), true);
    step = upto;
  }

  // ========= Botonera =========
  function bindButtons() {
    if (btnEdit)   btnEdit.addEventListener('click',  () => setMode('edit'));
    if (btnRecord) btnRecord.addEventListener('click', () => setMode('record'));

    if (btnStart)  btnStart.addEventListener('click', () => {
      if (mode === 'edit') {
        board.start();
        if (fenInput) fenInput.value = '';
      } else {
        chess.reset();
        board.start();
        movesSAN = [];
        step = 0;
        if (pgnInput) pgnInput.value = '';
      }
    });

    if (btnClear)  btnClear.addEventListener('click', () => {
      // Limpiar = pasar a edición y vaciar
      setMode('edit');
      board.clear(false);
      movesSAN = [];
      step = 0;
      if (fenInput) fenInput.value = '';
      if (pgnInput) pgnInput.value = '';
    });

    if (btnFlip)   btnFlip.addEventListener('click',  () => board.flip());

    if (btnBack)   btnBack.addEventListener('click',  () => { if (mode === 'record' && step > 0) goToStep(step - 1); });
    if (btnForward)btnForward.addEventListener('click', () => { if (mode === 'record' && step < movesSAN.length) goToStep(step + 1); });

    if (sideSelect) sideSelect.addEventListener('change', () => {
      // Solo afecta a FEN base / guardado; no reacomoda piezas automáticamente
      if (mode === 'record' && baseFenFull) {
        // actualiza side en FEN base
        const placement = onlyPlacement(baseFenFull);
        baseFenFull = makeFullFen(placement, currentSide());
        if (fenInput) fenInput.value = baseFenFull;
      }
    });
  }

  // ========= Submit del formulario =========
  function bindFormSubmit() {
    const form = boardEl ? boardEl.closest('form') : null;
    if (!form) return;

    form.addEventListener('submit', (e) => {
      try {
        if (mode === 'edit') {
          // Construimos placement desde tablero y side actual
          const pos = board.position();
          const placement = (typeof pos === 'string')
            ? (pos === 'start' ? '' : onlyPlacement(pos))
            : objToPlacement(pos);
          const side = currentSide();
          if (fenInput) fenInput.value = placement ? makeFullFen(placement, side) : '';
        } else {
          // record: usamos fen actual de chess + side elegido
          if (chess) {
            const placement = onlyPlacement(chess.fen());
            const side = currentSide();
            if (fenInput) fenInput.value = makeFullFen(placement, side);
            if (pgnInput) pgnInput.value = chess.pgn();
          }
        }
      } catch (err) {
        console.warn('[Editor] Error construyendo FEN/PGN al enviar:', err);
      }
    });
  }

  // ========= Inicialización =========
  function init() {
    if (!boardEl) return;

    // Asegura un ancho razonable para que chessboard calcule tamaño
    const wrap = boardEl.parentElement;
    const desired = Math.min(520, Math.max(260, (wrap?.clientWidth || 520) - 20));
    boardEl.style.width = desired + 'px';

    // Preload desde PHP (si existe)
    const preload = window.EJERCICIO_PRELOAD || { fen: 'start', pgn: '' };

    // FEN inicial (puede ser full o placement); extrae placement
    let placement = null;
    let side = currentSide();
    if (preload.fen && typeof preload.fen === 'string') {
      if (preload.fen.toLowerCase() === 'start' || preload.fen.trim() === '') {
        placement = 'start';
      } else {
        const parts = preload.fen.trim().split(/\s+/);
        placement = parts[0]; // placement
        if (parts[1] === 'w' || parts[1] === 'b') side = parts[1];
        if (sideSelect) sideSelect.value = side;
      }
    } else {
      placement = 'start';
    }

    // ¿Hay PGN? Si lo hay, arrancamos en modo 'record' y lo cargamos
    if (preload.pgn && preload.pgn.trim() !== '') {
      setTimeout(() => {
        rebuildBoardRecord(placement === 'start' ? null : placement, side);
        try {
          // Reproduce PGN
          chess.load_pgn(preload.pgn);
          movesSAN = chess.history();
          step = movesSAN.length;
          board.position(onlyPlacement(chess.fen()), true);
          if (pgnInput) pgnInput.value = preload.pgn;
          if (fenInput) fenInput.value = baseFenFull || '';
        } catch (e) {
          console.warn('[Editor] PGN inválido en preload:', e);
        }
        highlightMode();
      }, 0);
      mode = 'record';
    } else {
      // Sin PGN → edición por defecto
      rebuildBoardEdit(placement || 'start');
      highlightMode();
    }

    // Eventos
    bindButtons();
    bindFormSubmit();

    // Resize responsivo
    const onResize = () => { try { board.resize(); } catch (_) {} };
    window.addEventListener('resize', onResize);
    setTimeout(onResize, 120);
  }

  // Espera a que todo (CSS/imagenes) esté listo para medir bien
  if (document.readyState === 'complete') init();
  else window.addEventListener('load', init);
})();

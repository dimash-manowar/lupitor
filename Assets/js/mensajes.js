(function () {
  'use strict';

  function init() {
    const form = document.getElementById('msgForm');
    if (!form) return;

    const btnSend = document.getElementById('btnSend');
    const destSel = document.getElementById('destSelect');
    const destRes = document.getElementById('destResults');
    const thread = document.getElementById('thread');
    const list = document.getElementById('messagesList');

    const dz = form.querySelector('.js-dropzone');
    const input = form.querySelector('.js-file');
    const ta = form.querySelector('.js-textarea');
    const prev = form.querySelector('.js-preview');
    const clear = form.querySelector('.js-clear');

    const progWrap = document.getElementById('uploadProgressWrap');
    const progBar = document.getElementById('uploadProgressBar');

    let queued = [];
    const MAX_SIZE = 150 * 1024 * 1024;

    function kindOf(file) {
      const t = (file.type || '').toLowerCase();
      if (t.indexOf('image/') === 0) return 'imagen';
      if (t.indexOf('video/') === 0) return 'video';
      if (t.indexOf('audio/') === 0) return 'audio';
      return 'archivo';
    }

    function addFiles(files) {
      const arr = Array.prototype.slice.call(files || []);
      let rej = 0;
      for (let i = 0; i < arr.length; i++) {
        const f = arr[i];
        if (!f || f.size > MAX_SIZE) { rej++; continue; }
        const k = kindOf(f);
        if (k === 'archivo') { rej++; continue; }
        queued.push(f);
      }
      syncInput();
      if (rej > 0) {
        if (window.Swal) Swal.fire({ icon: 'warning', title: 'Se descartaron ' + rej + ' archivo(s) por tipo/tamaño' });
        else console.warn('Descartados', rej);
      }
    }

    function syncInput() {
      const dt = new DataTransfer();
      for (let i = 0; i < queued.length; i++) dt.items.add(queued[i]);
      input.files = dt.files;
      renderPreview();
    }

    function renderPreview() {
      prev.innerHTML = '';
      for (let i = 0; i < queued.length; i++) {
        (function (idx) {
          const f = queued[idx];
          const k = kindOf(f);
          const it = document.createElement('div');
          it.className = 'preview-item d-inline-block me-2 mb-2 position-relative';

          const rm = document.createElement('button');
          rm.type = 'button';
          rm.className = 'btn btn-sm btn-outline-light position-absolute top-0 end-0';
          rm.innerHTML = '&times;';
          rm.onclick = function () { queued.splice(idx, 1); syncInput(); };
          it.appendChild(rm);

          if (k === 'imagen') {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            img.width = 160; img.height = 120; img.loading = 'lazy';
            img.className = 'rounded border';
            it.appendChild(img);
          } else if (k === 'video') {
            const v = document.createElement('video');
            v.src = URL.createObjectURL(f);
            v.width = 200; v.controls = true;
            v.className = 'rounded border';
            it.appendChild(v);
          } else if (k === 'audio') {
            const a = document.createElement('audio');
            a.src = URL.createObjectURL(f);
            a.controls = true;
            it.appendChild(a);
          } else {
            const p = document.createElement('div');
            p.className = 'small text-secondary';
            p.textContent = '📎 ' + f.name;
            it.appendChild(p);
          }

          prev.appendChild(it);
        })(i);
      }
    }

    // Dropzone / pegar / selector
    dz.addEventListener('click', function () { input.click(); });
    ['dragenter', 'dragover'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('is-drag'); });
    });
    ['dragleave', 'dragend', 'drop'].forEach(function (ev) {
      dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('is-drag'); });
    });
    dz.addEventListener('drop', function (e) {
      const fs = (e.dataTransfer && e.dataTransfer.files) ? e.dataTransfer.files : [];
      if (fs.length) addFiles(fs);
    });
    input.addEventListener('change', function () {
      if (input.files && input.files.length) addFiles(input.files);
      input.value = '';
    });
    ta.addEventListener('paste', function (e) {
      const items = (e.clipboardData && e.clipboardData.items) ? e.clipboardData.items : [];
      const files = [];
      for (let i = 0; i < items.length; i++) {
        const it = items[i];
        if (it.kind === 'file') {
          const f = it.getAsFile();
          if (f) files.push(f);
        }
      }
      if (files.length) { e.preventDefault(); addFiles(files); }
    });
    clear.addEventListener('click', function () { queued = []; syncInput(); });

    // Enviar desde botón (sin submit)
    if (btnSend) btnSend.addEventListener('click', function () {
      // destinatario
      var toId = '';
      if (destSel && destSel.value) toId = String(destSel.value).trim();
      if (!toId && destRes) {
        var first = destRes.querySelector('option');
        if (first) {
          toId = first.value;
          if (destSel) destSel.value = toId; // ← asignación válida
        }
      }
      if (!toId) {
        if (window.Swal) Swal.fire({ icon: 'info', title: 'Selecciona un destinatario' });
        else alert('Selecciona un destinatario');
        return;
      }

      // progreso visible
      if (progWrap && progBar) {
        progWrap.hidden = false;
        progBar.style.width = '0%';
        progBar.textContent = '0%';
        progBar.setAttribute('aria-valuenow', '0');
      }
      btnSend.setAttribute('disabled', 'disabled');

      // 1) Parte importante: construir el FormData copiando el form, pero
      //    REESCRIBIENDO los archivos con la cola `queued` (multi imagen+video+audio)
      const fd = new FormData(form);
      fd.set('to_id', toId);

      // si hay cola, elimina lo que venga del <input> y mete la cola explícitamente
      if (queued.length > 0) {
        try { fd.delete('files[]'); } catch (_) { }
        queued.forEach(f => fd.append('files[]', f, f.name));
      }

      const xhr = new XMLHttpRequest();
      xhr.open('POST', form.action, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.responseType = 'json';

      xhr.upload.onprogress = function (ev) {
        if (!ev.lengthComputable || !progBar) return;
        var pct = Math.max(0, Math.min(100, Math.round(ev.loaded * 100 / ev.total)));
        progBar.style.width = pct + '%';
        progBar.textContent = pct + '%';
        progBar.setAttribute('aria-valuenow', String(pct));
      };

      xhr.onload = function () {
        if (progWrap) progWrap.hidden = true;
        btnSend.removeAttribute('disabled');

        try {
          var j = xhr.response || JSON.parse(xhr.responseText || '{}');
          if (!j.ok) throw new Error(j.error || 'No se pudo enviar');

          if (j.html && list) {
            var wrap = document.createElement('div');
            wrap.innerHTML = j.html;
            var children = Array.prototype.slice.call(wrap.children);
            for (var i = 0; i < children.length; i++) list.appendChild(children[i]);
            if (thread) thread.scrollTop = thread.scrollHeight;
          }

          var t = form.querySelector('textarea[name="body"]'); if (t) t.value = '';
          queued = []; syncInput();
          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Mensaje enviado',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 1300,
              timerProgressBar: true
            });
          }
        } catch (e) {
          var msg = e && e.message ? e.message : String(e);
          if (window.Swal) Swal.fire({ icon: 'error', title: msg }); else alert(msg);
        }
      };

      xhr.onerror = function () {
        if (progWrap) progWrap.hidden = true;
        btnSend.removeAttribute('disabled');
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Error de red al enviar' });
        else alert('Error de red al enviar');
      };

      xhr.send(fd);
    });

    console.debug('mensajes.js listo');
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();

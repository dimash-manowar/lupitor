(function(){
  'use strict';
  const $ = (s, c)=> (c||document).querySelector(s);

  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]); }
  function fmtDate(iso){ try{ const d=new Date(iso.replace(' ','T')); return d.toLocaleString(); }catch(_){ return iso||''; } }

  async function getJSON(url){
    const res = await fetch(url, { credentials:'same-origin' });
    try { return await res.json(); } catch { return null; }
  }
  async function postForm(url, data){
    const res = await fetch(url, { method:'POST', credentials:'same-origin',
      headers:{ 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
      body: new URLSearchParams(data)
    });
    try { return await res.json(); } catch { return null; }
  }

  function renderList(items){
    const box = $('#notifList'); if (!box) return;
    if (!items || !items.length) { box.innerHTML = '<div class="p-3 text-secondary small">Sin notificaciones.</div>'; return; }

    box.innerHTML = items.map(n=>{
      const titulo = escapeHtml(n.titulo||'');
      const cuerpo = n.cuerpo ? escapeHtml(n.cuerpo) : '';
      const fecha  = fmtDate(n.creada_en || n.created_at);
      const leida  = !!n.leida_en;
      const btnVer = `<a href="${n.link_url || '#'}" class="btn btn-sm btn-primary" data-action="ver" data-id="${n.id}" ${n.link_url ? '' : 'tabindex="-1" aria-disabled="true"'}>Ver</a>`;
      const btnOk  = leida ? '<span class="badge bg-secondary">Leída</span>' :
                             `<button class="btn btn-sm btn-outline-secondary" data-action="read-one" data-id="${n.id}">✓</button>`;
      return `
        <div class="notif-item" data-id="${n.id}">
          <div class="flex-grow-1">
            <div class="tit">${titulo}</div>
            ${cuerpo ? `<div class="text-secondary small">${cuerpo}</div>`:''}
            <div class="meta text-muted">${fecha}</div>
          </div>
          <div class="d-flex flex-column gap-1">
            ${btnVer}
            ${btnOk}
          </div>
        </div>`;
    }).join('');
  }

  async function refreshCount(){
    const j = await getJSON(BASE_URL+'Notificaciones/count');
    const b = $('#notifBadge');
    if (!b) return;
    const c = (j && j.ok) ? (j.count|0) : 0;
    b.textContent = c;
    b.classList.toggle('d-none', c<=0);
  }

  async function refreshList(){
    const j = await getJSON(BASE_URL+'Notificaciones/listar');
    if (j && j.ok) renderList(j.items);
  }

  async function markAll(csrf){
    const j = await postForm(BASE_URL+'Notificaciones/marcarTodas', { csrf });
    if (j && j.ok) {
      await refreshList(); await refreshCount();
      if (window.Swal) Swal.fire({ icon:'success', title:'Notificaciones marcadas', toast:true, position:'top-end', showConfirmButton:false, timer:1100 });
    }
  }
  async function markOne(id, csrf){
    const j = await postForm(BASE_URL+'Notificaciones/marcarLeida', { id, csrf });
    if (j && j.ok) { await refreshList(); await refreshCount(); }
  }

  function init(){
    const drop   = $('#notifDrop');
    const btnAll = $('#notifMarkAll');
    const csrf   = btnAll?.dataset.csrf || '';
    const list   = $('#notifList');

    // Badge inicial y polling
    refreshCount(); setInterval(refreshCount, 30000);

    // Al abrir, cargar lista
    drop?.addEventListener('click', ()=> { refreshList(); });

    // Marcar todas
    btnAll?.addEventListener('click', ()=> { if (csrf) markAll(csrf); });

    // Delegación en el dropdown
    list?.addEventListener('click', async (e)=>{
      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (btn.dataset.action === 'read-one' && id && csrf) {
        e.preventDefault();
        await markOne(id, csrf);
      } else if (btn.dataset.action === 'ver') {
        // marca como leída al clicar en "Ver"
        if (id && csrf) await markOne(id, csrf);
        // deja que el link navegue (si hay link_url)
      }
    });
  }
  if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

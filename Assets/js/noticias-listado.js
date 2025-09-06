(function(){
  const form = document.getElementById('news-form');
  if (!form) return;

  const base = form.dataset.base || (window.BASE_URL + 'Noticias/index/');
  const list = document.getElementById('news-list');
  const pag  = document.getElementById('news-pag');
  const cnt  = document.getElementById('news-counter');

  let timer = null;

  function qs(formEl){
    const params = new URLSearchParams(new FormData(formEl));
    return params.toString();
  }

  function buildUrl(page=1){
    const q = qs(form);
    return `${base}${page}?${q}&ajax=1`;
  }

  function loading(on){
    if (on) {
      list.style.opacity = '0.5';
    } else {
      list.style.opacity = '';
    }
  }

  async function update(page=1, push=true){
    loading(true);
    try {
      const res = await fetch(buildUrl(page), {
        headers: {'X-Requested-With':'fetch'}
      });
      const json = await res.json();
      if (!json.ok) throw new Error('Respuesta inválida');

      list.innerHTML = json.cards || '';
      pag.innerHTML  = json.pagination || '';

      // contador (opcional)
      if (cnt && typeof json.total === 'number') {
        const per  = json.per || 9, p = json.page || 1, total = json.total || 0;
        const ini  = total>0 ? ((p-1)*per+1) : 0;
        const fin  = Math.min(p*per, total);
        cnt.textContent = total>0 ? `Mostrando ${ini}–${fin} de ${total} noticias` : 'No hay noticias con ese filtro';
      }

      // Actualiza URL
      if (push) {
        const url = `${base}${json.page || page}?${qs(form)}`;
        history.pushState({page: json.page || page}, '', url);
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading(false);
    }
  }

  // Eventos: selects -> change; buscar -> debounce
  form.querySelectorAll('select').forEach(sel=>{
    sel.addEventListener('change', ()=>update(1, true));
  });

  const qInput = form.querySelector('input[name="q"]');
  if (qInput) {
    qInput.addEventListener('input', ()=>{
      clearTimeout(timer);
      timer = setTimeout(()=>update(1, true), 300);
    });
  }

  // Submit normal del botón -> usa AJAX también
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    update(1, true);
  });

  // Delegación para paginación
  document.addEventListener('click', (e)=>{
    const a = e.target.closest('#news-pag a.page-link');
    if (!a) return;
    const page = parseInt(a.dataset.page || '1', 10);
    if (!Number.isFinite(page)) return;
    e.preventDefault();
    update(page, true);
  });

  // Soporta back/forward
  window.addEventListener('popstate', (ev)=>{
    // Si quieres recargar del servidor en back/forward, puedes location.reload();
    update(1, false);
  });
})();

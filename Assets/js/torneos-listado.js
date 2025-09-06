(function(){
  const form = document.getElementById('torneos-form');
  if (!form) return;

  const base = form.dataset.base || (window.BASE_URL + 'Torneos/index/');
  const list = document.getElementById('torneos-list');
  const pag  = document.getElementById('torneos-pag');
  const cnt  = document.getElementById('torneos-counter');
  let timer=null;

  const qs = (f)=> new URLSearchParams(new FormData(f)).toString();
  const buildUrl=(page=1)=> `${base}${page}?${qs(form)}&ajax=1`;
  const loading=(on)=>{ if(list) list.style.opacity = on ? '0.5' : ''; };

  async function update(page=1, push=true){
    loading(true);
    try{
      const res = await fetch(buildUrl(page), { headers:{'X-Requested-With':'fetch'} });
      const json = await res.json();
      if(!json.ok) throw new Error('Respuesta inválida');
      list.innerHTML = json.cards || '';
      pag.innerHTML  = json.pagination || '';
      if (cnt && typeof json.total==='number') {
        const per=json.per||9, p=json.page||1, total=json.total||0;
        const ini = total>0?((p-1)*per+1):0, fin=Math.min(p*per,total);
        cnt.textContent = total>0 ? `Mostrando ${ini}–${fin} de ${total} torneos` : 'No hay torneos con ese filtro';
      }
      if (push) {
        const url = `${base}${json.page || page}?${qs(form)}`;
        history.pushState({page:json.page||page},'',url);
      }
    }catch(e){ console.error(e); }
    finally{ loading(false); }
  }

  // filtros
  form.querySelectorAll('select').forEach(el=> el.addEventListener('change', ()=>update(1,true)));
  const qInput=form.querySelector('input[name="q"]');
  if (qInput) qInput.addEventListener('input', ()=>{ clearTimeout(timer); timer=setTimeout(()=>update(1,true),300); });
  form.addEventListener('submit', (e)=>{ e.preventDefault(); update(1,true); });

  // paginación
  document.addEventListener('click', (e)=>{
    const a = e.target.closest('#torneos-pag a.page-link');
    if (!a) return;
    const page = parseInt(a.dataset.page || '1', 10);
    if (!Number.isFinite(page)) return;
    e.preventDefault();
    update(page, true);
  });

  window.addEventListener('popstate', ()=>update(1,false));
})();

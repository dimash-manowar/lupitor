'use strict';
document.addEventListener('DOMContentLoaded', () => {
  // Flip torneo
  const tarjeta = document.getElementById('tarjeta-torneo');
  tarjeta?.addEventListener('click', (ev) => {
    const a = ev.target.closest('[data-accion="flip"]'); if (!a) return;
    tarjeta.classList.toggle('is-flipped');
  });

  // Lightbox para fotos
  const modal = document.getElementById('lightbox');
  const img   = document.getElementById('lightbox-img');
  document.querySelectorAll('a.galeria-link[data-tipo="foto"]').forEach(a=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      const href = a.getAttribute('href');
      img.src = href;
      const m = new bootstrap.Modal(modal);
      m.show();
    });
  });
});

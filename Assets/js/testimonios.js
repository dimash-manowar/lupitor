// testimonios.js (FINAL)
document.addEventListener('DOMContentLoaded', () => {
  const el = document.querySelector('.testi-swiper');
  if (!el || typeof Swiper !== 'function') return;

  // 1) Destruye cualquier instancia previa
  if (el.swiper) el.swiper.destroy(true, true);

  // 2) Asegura los dos gap-slides (izquierda y derecha)
  const wrapper = el.querySelector('.swiper-wrapper');
  // Limpia gaps previos por si re-ejecuta
  wrapper.querySelectorAll('.gap-slide').forEach(n => n.remove());

  const mkGap = () => {
    const d = document.createElement('div');
    d.className = 'swiper-slide gap-slide';
    d.setAttribute('aria-hidden', 'true');
    return d;
  };
  wrapper.insertBefore(mkGap(), wrapper.firstElementChild); // gap izquierdo
  wrapper.appendChild(mkGap());                              // gap derecho

  // 3) Cálculo del slide inicial (real del medio + 1 por el gap izq)
  const reales = wrapper.querySelectorAll('.swiper-slide:not(.gap-slide)').length; // 7
  const initial = 1 + Math.floor(reales / 2); // 1 + 3 = 4

  // 4) Swiper limpio y arrastrable
  const swiper = new Swiper(el, {
    effect: 'coverflow',
    slidesPerView: 'auto',
    centeredSlides: true,
    centeredSlidesBounds: true,
    spaceBetween: 24,

    loop: false,                 // sin clones (evita líos)
    initialSlide: initial,

    touchEventsTarget: 'container',
    allowTouchMove: true,
    simulateTouch: true,
    grabCursor: true,
    threshold: 5,
    longSwipes: true,
    resistance: true,
    resistanceRatio: 0.85,
    watchOverflow: false,

    navigation: { nextEl: '.testi-next', prevEl: '.testi-prev' },
    keyboard: { enabled: true },
    observer: true,
    observeParents: true,
    updateOnWindowResize: true,

    coverflowEffect: { rotate: 0, stretch: 0, depth: 360, modifier: 1.05, slideShadows: false },

    on: {
      init(sw){
        // Posiciona exactamente en el centro
        sw.slideTo(initial, 0, false);
      },
      imagesReady(sw){
        // Recalcula al cargar imágenes (anchos reales)
        sw.update();
        sw.slideTo(sw.activeIndex, 0, false);
      },
      resize(sw){
        sw.update();
      }
    }
  });

  // Fallback por si el módulo Navigation no engancha
  document.querySelector('.testi-next')?.addEventListener('click', e => { e.preventDefault(); swiper.slideNext(); });
  document.querySelector('.testi-prev')?.addEventListener('click', e => { e.preventDefault(); swiper.slidePrev(); });
});

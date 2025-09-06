<?php
$cards       = is_array($cards ?? null)       ? $cards       : (is_array($data['cards'] ?? null)       ? $data['cards']       : []);
$galeria     = is_array($galeria ?? null)     ? $galeria     : (is_array($data['galeria'] ?? null)     ? $data['galeria']     : []);
$testimonios = is_array($testimonios ?? null) ? $testimonios : (is_array($data['testimonios'] ?? null) ? $data['testimonios'] : []);
$torneos     = is_array($torneos ?? null) || is_object($torneos ?? null) ? $torneos : ($data['torneos'] ?? null);

?>
<script>
  console.log('[testimonios] archivo cargado');
  document.addEventListener('DOMContentLoaded', () => {
    console.log('[testimonios] DOM listo');
    console.log('[testimonios] Swiper =', typeof Swiper);
    const el = document.querySelector('.testi-swiper');
    console.log('[testimonios] contenedor =', !!el);
    const slides = document.querySelectorAll('.testi-swiper .swiper-slide').length;
    console.log('[testimonios] slides =', slides);
  });
</script>
<?php
// Acepta $testimonios o $data['testimonios']
$testimonios = $testimonios ?? ($data['testimonios'] ?? []);
if (empty($testimonios)) return; // nada que mostrar
?>
<section class="mb-5">
  <h2 class="text-center mb-4">Testimonios</h2>

  <div class="full-bleed">
    <div class="edge-pad edge-pad--24">
      <div class="swiper testi-swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide gap-slide" aria-hidden="true"></div>
          <?php foreach ($testimonios as $t):
          
            $foto  = $t['foto'] ?? (BASE_URL . 'Assets/img/user-default.png');
            $stars = max(0, min(5, (int)($t['estrellas'] ?? 5)));
          ?>
          
            <div class="swiper-slide">
              <div class="card testi-card shadow-lg text-center p-4 rounded-4">
                <!-- Foto -->
                <img src="<?= htmlspecialchars($foto) ?>"
                  alt="Foto de <?= htmlspecialchars($t['nombre']) ?>"
                  loading="lazy"
                  style="width:90px;height:90px;object-fit:cover;border-radius:9999px;"
                  class="mx-auto mb-3">

                <!-- Estrellas -->
                <div class="mb-2">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi <?= $i <= $stars ? 'bi-star-fill text-warning' : 'bi-star text-secondary' ?>"></i>
                  <?php endfor; ?>
                </div>

                <!-- Nombre y rol -->
                <h5 class="mb-1"><?= htmlspecialchars($t['nombre']) ?></h5>
                <?php if (!empty($t['rol'])): ?>
                  <small class="text-secondary d-block mb-2"><?= htmlspecialchars($t['rol']) ?></small>
                <?php endif; ?>

                <!-- Texto -->
                <p class="small fst-italic mb-0">“<?= htmlspecialchars($t['texto']) ?>”</p>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="swiper-slide gap-slide" aria-hidden="true"></div>
        </div>

        <!-- Controles -->
        <div class="testi-nav mt-2 d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-outline-light btn-sm testi-prev">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button type="button" class="btn btn-outline-light btn-sm testi-next">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
</section>
<?php

$cards = $data['home_cards'] ?? [];

// Helper para href (URL directa, destino o ancla)
$hrefDe = function(array $c): string {
    $url  = trim((string)($c['url'] ?? ''), '/');
    $dest = strtolower(trim((string)($c['destino'] ?? ''), '/'));

    // 1) URL absoluta o relativa
    if ($url !== '') {
        if (preg_match('~^https?://~i', $url)) return $url;              // absoluta
        return rtrim(BASE_URL, '/') . '/' . $url;                        // relativa
    }

    // 2) Destinos conocidos
    switch ($dest) {
        case 'club':     return BASE_URL . 'Club';
        case 'noticias': return BASE_URL . 'Noticias';
        case 'eventos':  return BASE_URL . 'Eventos';
        // 3) Anclas de la propia home (opcional)
        case 'ancla_noticias': return BASE_URL . '#sec-noticias';
        case 'ancla_eventos':  return BASE_URL . '#sec-eventos';
        default: return BASE_URL;
    }
};

if (!$cards) {
    // Fallback por si no hay tarjetas en BD
    $cards = [
        ['titulo'=>'Sobre el club','resumen'=>'Quiénes somos, valores, escuela y equipos.','color_fondo'=>'#0d6efd','color_texto'=>'#fff','btn_texto'=>'Ver','destino'=>'club','icono'=>'bi-people'],
        ['titulo'=>'Noticias','resumen'=>'Últimas novedades del club y del ajedrez.','color_fondo'=>'#198754','color_texto'=>'#fff','btn_texto'=>'Ver','destino'=>'noticias','icono'=>'bi-newspaper'],
        ['titulo'=>'Eventos','resumen'=>'Torneos y actividades próximas.','color_fondo'=>'#6f42c1','color_texto'=>'#fff','btn_texto'=>'Ver','destino'=>'eventos','icono'=>'bi-calendar-event'],
    ];
}
?>
<section class="container my-4" id="home-cards">
  <div class="row g-3">
    <?php foreach ($cards as $c): 
      $bg = $c['color_fondo'] ?? '#222';
      $tx = $c['color_texto'] ?? '#fff';
      $href = $hrefDe($c);
      $btn  = $c['btn_texto'] ?? 'Ver';
      $ico  = $c['icono'] ?? 'bi-grid-3x3-gap';
    ?>
      <div class="col-md-4">
        <article class="card border-0 shadow-sm h-100" style="background:<?= htmlspecialchars($bg) ?>; color:<?= htmlspecialchars($tx) ?>;">
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi <?= htmlspecialchars($ico) ?>"></i>
              <h3 class="h5 m-0"><?= htmlspecialchars($c['titulo'] ?? '') ?></h3>
            </div>
            <?php if (!empty($c['resumen'])): ?>
              <p class="mb-4 opacity-75"><?= htmlspecialchars($c['resumen']) ?></p>
            <?php endif; ?>
            <div class="mt-auto">
              <a href="<?= $href ?>" class="btn btn-light btn-sm">
                <?= htmlspecialchars($btn) ?>
              </a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

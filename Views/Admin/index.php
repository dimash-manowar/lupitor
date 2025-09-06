<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>


<div class="container-fluid">
  <h1 class="h3 mb-4">Administrar Portada (Home)</h1>

  <!-- Pestañas -->
  <ul class="nav nav-tabs" id="homeTab" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#config">Ajustes generales</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cards">Tarjetas destacadas</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#torneo">Torneo destacado</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#galeria">Galería</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#testimonios">Testimonios</a></li>
  </ul>

  <div class="tab-content p-4 border border-top-0 rounded-bottom bg-white shadow-sm">

    <!-- Ajustes generales -->
    <div class="tab-pane fade show active" id="config">
      <form action="home_guardar_config.php" method="post" enctype="multipart/form-data" data-max-mb="<?= $maxMb ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
        <input type="hidden" name="to_id" value="<?= (int)($to_id ?? 0) ?>">
        <div class="mb-3">
          <label class="form-label">Título portada</label>
          <input type="text" name="titulo_portada" class="form-control" value="<?= $config['titulo_portada'] ?? '' ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Subtítulo portada</label>
          <input type="text" name="subtitulo_portada" class="form-control" value="<?= $config['subtitulo_portada'] ?? '' ?>">
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
    </div>

    <!-- Tarjetas destacadas -->
    <div class="tab-pane fade" id="cards">
      <p class="text-muted">Aquí puedes editar las 3 tarjetas visibles en la Home.</p>
      <?php foreach ($cards as $c): ?>
        <form action="home_cards_guardar.php" method="post" class="card mb-3 p-3 shadow-sm">
          <input type="hidden" name="id" value="<?= $c['id'] ?>">
          <div class="row">
            <div class="col-md-4 mb-2">
              <label>Título</label>
              <input type="text" name="titulo" value="<?= htmlspecialchars($c['titulo']) ?>" class="form-control">
            </div>
            <div class="col-md-8 mb-2">
              <label>Descripción</label>
              <input type="text" name="descripcion" value="<?= htmlspecialchars($c['descripcion']) ?>" class="form-control">
            </div>
          </div>
          <div class="row">
            <div class="col-md-3 mb-2">
              <label>Icono (Bootstrap)</label>
              <input type="text" name="icono" value="<?= $c['icono'] ?>" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
              <label>Color fondo</label>
              <input type="color" name="color_fondo" value="<?= $c['color_fondo'] ?>" class="form-control form-control-color">
            </div>
            <div class="col-md-3 mb-2">
              <label>Color texto</label>
              <input type="color" name="color_texto" value="<?= $c['color_texto'] ?>" class="form-control form-control-color">
            </div>
            <div class="col-md-3 mb-2">
              <label>Visible</label>
              <select name="visible" class="form-select">
                <option value="1" <?= $c['visible'] ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= !$c['visible'] ? 'selected' : '' ?>>No</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-success mt-2">Guardar tarjeta</button>
        </form>
      <?php endforeach; ?>
    </div>

    <!-- Torneo destacado -->
    <div class="tab-pane fade" id="torneo">
      <form action="home_torneo_guardar.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $torneo['id'] ?? 0 ?>">
        <div class="mb-3"><label>Título</label>
          <input type="text" name="titulo" value="<?= $torneo['titulo'] ?? '' ?>" class="form-control">
        </div>
        <div class="row">
          <div class="col-md-4 mb-2"><label>Fecha</label>
            <input type="date" name="fecha" value="<?= $torneo['fecha'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-md-4 mb-2"><label>Hora</label>
            <input type="time" name="hora" value="<?= $torneo['hora'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-md-4 mb-2"><label>Lugar</label>
            <input type="text" name="lugar" value="<?= $torneo['lugar'] ?? '' ?>" class="form-control">
          </div>
        </div>
        <div class="mb-3"><label>Modalidad</label>
          <input type="text" name="modalidad" value="<?= $torneo['modalidad'] ?? '' ?>" class="form-control">
        </div>
        <div class="mb-3"><label>Bases (PDF)</label>
          <input type="file" name="bases_pdf" class="form-control">
        </div>
        <div class="row">
          <div class="col-md-4"><label>Color fondo</label>
            <input type="color" name="color_fondo" value="<?= $torneo['color_fondo'] ?? '#ffffff' ?>" class="form-control form-control-color">
          </div>
          <div class="col-md-4"><label>Color texto</label>
            <input type="color" name="color_texto" value="<?= $torneo['color_texto'] ?? '#000000' ?>" class="form-control form-control-color">
          </div>
          <div class="col-md-4"><label>Color botón</label>
            <input type="color" name="color_boton" value="<?= $torneo['color_boton'] ?? '#007bff' ?>" class="form-control form-control-color">
          </div>
        </div>
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" name="form_activo" value="1" <?= !empty($torneo['form_activo']) ? 'checked' : '' ?>>
          <label class="form-check-label">Formulario de inscripción activo</label>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar torneo</button>
      </form>
    </div>

    <!-- Galería -->
    <div class="tab-pane fade" id="galeria">
      <form action="home_galeria_subir.php" method="post" enctype="multipart/form-data" class="mb-3">
        <label>Subir fotos (múltiples)</label>
        <input type="file" name="fotos[]" multiple class="form-control">
        <button type="submit" class="btn btn-success mt-2">Subir</button>
      </form>
      <div class="row">
        <?php foreach ($galeria as $m): ?>
          <div class="col-md-3 mb-3 text-center">
            <?php if ($m['tipo'] == "foto"): ?>
              <img src="<?= $m['ruta'] ?>" class="img-fluid rounded">
            <?php else: ?>
              <i class="bi bi-play-btn display-4"></i>
              <p><?= $m['url'] ?></p>
            <?php endif; ?>
            <form action="home_galeria_eliminar.php" method="post" class="mt-2">
              <input type="hidden" name="id" value="<?= $m['id'] ?>">
              <button class="btn btn-danger btn-sm">Eliminar</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Testimonios -->
    <div class="tab-pane fade" id="testimonios">
      <form action="home_testimonios_guardar.php" method="post" class="mb-4">
        <h5>Añadir nuevo testimonio</h5>
        <div class="row">
          <div class="col-md-4 mb-2"><input type="text" name="nombre" class="form-control" placeholder="Nombre"></div>
          <div class="col-md-4 mb-2"><input type="text" name="rol" class="form-control" placeholder="Rol/edad"></div>
          <div class="col-md-4 mb-2"><input type="text" name="texto" class="form-control" placeholder="Testimonio"></div>
        </div>
        <button class="btn btn-success">Añadir</button>
      </form>
      <ul class="list-group">
        <?php foreach ($testimonios as $t): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($t['nombre']) ?> – <?= htmlspecialchars($t['rol']) ?>:
            <em><?= htmlspecialchars($t['texto']) ?></em>
            <form action="home_testimonios_eliminar.php" method="post" class="ms-2">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button class="btn btn-danger btn-sm">X</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>
</div>



<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>
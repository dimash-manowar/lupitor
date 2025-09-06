<?php require_once BASE_PATH . "Views/Templates/header.php"; ?>

<body class="bg-dark text-light">
    <main class="container py-5">

        <!-- ================= EJERCICIOS ================= -->
        <section id="sec-ejercicios" class="container my-5">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <h1 class="h3 m-0">Ejercicios del profe (públicos)</h1>
                    <small class="text-secondary">Filtra por nivel o busca por título.</small>
                </div>
                <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>">Volver al inicio</a>
            </div>

            <!-- Filtros -->
            <form id="form-filtros" class="row g-2 mb-2" method="get" action=""
                data-base="<?= BASE_URL ?>Home/index/">
                <div class="col-sm-3">
                    <?php $niv = $data['nivel'] ?? ''; ?>
                    <select name="nivel" class="form-select">
                        <option value="">Todos los niveles</option>
                        <?php foreach (['Iniciación', 'Intermedio', 'Avanzado'] as $n): ?>
                            <option value="<?= $n ?>" <?= $niv === $n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-4">
                    <input type="search" name="q" class="form-control"
                        placeholder="Buscar por título…"
                        value="<?= htmlspecialchars($data['q'] ?? '') ?>">
                </div>

                <div class="col-sm-3">
                    <?php $orden = $data['orden'] ?? 'recientes'; ?>
                    <select name="orden" class="form-select">
                        <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                        <option value="antiguos" <?= $orden === 'antiguos' ? 'selected' : '' ?>>Más antiguos</option>
                        <option value="nivel_asc" <?= $orden === 'nivel_asc' ? 'selected' : '' ?>>Nivel (A→Z)</option>
                        <option value="nivel_desc" <?= $orden === 'nivel_desc' ? 'selected' : '' ?>>Nivel (Z→A)</option>
                        <option value="titulo_asc" <?= $orden === 'titulo_asc' ? 'selected' : '' ?>>Título (A→Z)</option>
                        <option value="titulo_desc" <?= $orden === 'titulo_desc' ? 'selected' : '' ?>>Título (Z→A)</option>
                    </select>
                </div>

                <div class="col-sm-1">
                    <?php $perSel = (int)($data['meta']['per'] ?? 9); ?>
                    <select name="per" class="form-select">
                        <?php foreach ([9, 12, 18, 24] as $pp): ?>
                            <option value="<?= $pp ?>" <?= $perSel === $pp ? 'selected' : '' ?>><?= $pp ?>/pág</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-1 d-grid">
                    <button class="btn btn-primary">Aplicar</button>
                </div>
            </form>

            <!-- Contador -->
            <?php
            $m = $data['meta'] ?? ['page' => 1, 'per' => 9, 'total' => 0];
            $ini = ($m['total'] > 0) ? (($m['page'] - 1) * $m['per'] + 1) : 0;
            $fin = min($m['page'] * $m['per'], $m['total']);
            ?>
            <div id="ex-contador" class="text-secondary small mb-3">
                <?= $m['total'] > 0 ? "Mostrando {$ini}–{$fin} de {$m['total']} ejercicios" : 'No hay ejercicios que coincidan con tu búsqueda' ?>
            </div>

            <!-- Listado -->
            <?php $ejercicios = $data['ejercicios'] ?? []; ?>
            <div id="ex-list" class="row">
                <?php require BASE_PATH . 'Views/Home/_cards.php'; ?>
            </div>

            <!-- Paginación -->
            <div id="ex-pag" class="mt-3">
                <?php require BASE_PATH . 'Views/Home/_paginacion.php'; ?>
            </div>
        </section>

        <!-- ================= INFO CLUB ================= -->
        <div class="masonry"></div>
        <?php require BASE_PATH . 'Views/Home/_home_cards.php'; ?>

        <main class="container py-5 text-center">
            <h1 class="display-5 mb-4"><?= $data['titulo'] ?? 'Eventos' ?></h1>
            <p class="lead">Simultáneas, campeonatos, actividades escolares… ¡Entérate de todo lo que organizamos!</p>
        </main>

        <!-- ================= TORNEO DESTACADO ================= -->
        <?php if (!empty($data['destacado'])): ?>
            <?php $t = $data['destacado'];
            $precio = (float)($t['precio'] ?? 0); ?>
            <section class="container my-4">
                <?php
                // 1) Calcular estado ANTES de abrir el contenedor
                $now = new DateTime('now');
                $ini = new DateTime($t['inicio']);
                $fin = !empty($t['fin']) ? new DateTime($t['fin']) : null;

                if ($now < $ini) {
                    $estado = 'proximo';
                    $estadoTxt = 'Próximo';
                } elseif ($fin && $now > $fin) {
                    $estado = 'finalizado';
                    $estadoTxt = 'Finalizado';
                } elseif ($now->format('Y-m-d') === $ini->format('Y-m-d')) {
                    $estado = 'hoy';
                    $estadoTxt = 'Hoy';
                } else {
                    $estado = 'en-curso';
                    $estadoTxt = 'En curso';
                }
                ?>
                <div id="home-torneo-feature"
                    class="torneo-feature-wrap torneo-state--<?= $estado ?> p-3 p-md-4 position-relative">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="torneo-status torneo-status--<?= $estado ?>"><?= $estadoTxt ?></span>
                        <i class="bi bi-trophy text-warning fs-4"></i>
                        <h2 class="h5 m-0">Torneo destacado</h2>

                        <?php if ($estado !== 'finalizado'): ?>
                            <span id="torneo-countdown"
                                class="countdown-badge countdown-badge--<?= $estado ?> ms-auto"
                                data-inicio="<?= htmlspecialchars($t['inicio']) ?>"></span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4 align-items-center">
                        <!-- Imagen derecha (siempre dentro) -->
                        <div class="col-12 col-lg-3 order-1 order-lg-2 d-flex justify-content-lg-end">
                            <div class="torneo-thumb-wrap">
                                <?php if (!empty($t['portada'])): ?>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalPosterHome" class="d-inline-block">
                                        <div class="torneo-thumb torneo-thumb--lg">
                                            <img src="<?= BASE_URL . $t['portada'] ?>" alt="Cartel del torneo: <?= htmlspecialchars($t['titulo']) ?>">
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="torneo-thumb torneo-thumb--lg torneo-thumb--placeholder">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Modal cartel -->
                            <div class="modal fade" id="modalPosterHome" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content bg-dark">
                                        <img src="<?= BASE_URL . $t['portada'] ?>" class="w-100 rounded" alt="Cartel del torneo en grande">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Texto izquierda -->
                        <div class="col-12 col-lg-9 order-2 order-lg-1">


                            <dl class="row mb-3 torneo-dl">
                                <dt class="col-sm-4 col-lg-3 text-info">Título</dt>
                                <dd class="col-sm-8 col-lg-9 text-white"><?= htmlspecialchars($t['titulo']) ?></dd>

                                <dt class="col-sm-4 col-lg-3 text-primary">Modalidad</dt>
                                <dd class="col-sm-8 col-lg-9 text-white"><?= htmlspecialchars(ucfirst($t['modalidad'])) ?></dd>

                                <dt class="col-sm-4 col-lg-3 text-success">Fecha inicio</dt>
                                <dd class="col-sm-8 col-lg-9 text-white"><?= date('d/m/Y H:i', strtotime($t['inicio'])) ?></dd>

                                <?php if (!empty($t['fin'])): ?>
                                    <dt class="col-sm-4 col-lg-3 text-success">Fecha fin</dt>
                                    <dd class="col-sm-8 col-lg-9 text-white"><?= date('d/m/Y H:i', strtotime($t['fin'])) ?></dd>
                                <?php endif; ?>

                                <?php if (!empty($t['lugar'])): ?>
                                    <dt class="col-sm-4 col-lg-3 text-warning">Lugar</dt>
                                    <dd class="col-sm-8 col-lg-9 text-white"><?= htmlspecialchars($t['lugar']) ?></dd>
                                <?php endif; ?>

                                <dt class="col-sm-4 col-lg-3 text-danger">Precio</dt>
                                <dd class="col-sm-8 col-lg-9 text-white"><?= $precio > 0 ? '€' . number_format($precio, 2, ',', '.') : 'Gratuito' ?></dd>

                                <?php if (!empty($t['resumen'])): ?>
                                    <dt class="col-sm-4 col-lg-3 text-info">Descripción</dt>
                                    <dd class="col-sm-8 col-lg-9 text-white-50"><?= htmlspecialchars($t['resumen']) ?></dd>
                                <?php endif; ?>
                            </dl>

                            <div class="d-flex flex-wrap gap-2">
                                <?php if (!empty($t['form_activo'])): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInsHome">Inscribirse</button>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inscripciones cerradas</span>
                                <?php endif; ?>

                                <?php if (!empty($t['bases_pdf'])): ?>
                                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalBasesHome">Bases</button>
                                <?php endif; ?>

                                <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL . 'Torneos/ver/' . urlencode($t['slug']) ?>">Más detalles</a>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Modal: Inscripción -->
                <div class="modal fade" id="modalInsHome" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header">
                                <h5 class="modal-title">Inscripción — <?= htmlspecialchars($t['titulo']) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <form id="formInsHome" method="post" action="<?= BASE_URL ?>Torneos/inscribirsePost" novalidate>
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
                                <input type="hidden" name="torneo_id" value="<?= (int)$t['id'] ?>">
                                <input type="hidden" name="pago_ok" id="h_pago_ok" value="<?= ((float)($t['precio'] ?? 0) > 0) ? '0' : '1' ?>">
                                <input type="hidden" name="pago_modo" id="h_pago_modo" value="<?= ((float)($t['precio'] ?? 0) > 0) ? 'ninguno' : 'gratis' ?>">
                                <input type="hidden" name="pago_ref" id="h_pago_ref" value="">

                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <label class="form-label">Nombre*</label>
                                            <input name="nombre" class="form-control" required maxlength="100">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label">Apellidos</label>
                                            <input name="apellidos" class="form-control" maxlength="150">
                                        </div>
                                        <div class="col-sm-8">
                                            <label class="form-label">Dirección</label>
                                            <input name="direccion" class="form-control" maxlength="200">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Fecha nacimiento</label>
                                            <input type="date" name="fecha_nac" class="form-control">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">ELO</label>
                                            <input name="elo" class="form-control" inputmode="numeric" pattern="\d*" maxlength="5">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">¿Federado?</label>
                                            <select name="federado" class="form-select">
                                                <option value="0">No</option>
                                                <option value="1">Sí</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Email*</label>
                                            <input type="email" name="email" class="form-control" required maxlength="150">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label">Teléfono</label>
                                            <input name="telefono" class="form-control" maxlength="50">
                                        </div>
                                    </div>

                                    <?php $precio = (float)($t['precio'] ?? 0); ?>
                                    <?php if ($precio > 0): ?>
                                        <hr class="my-4">
                                        <div class="mb-2"><strong>Cuota:</strong> €<?= number_format($precio, 2, ',', '.') ?></div>
                                        <div class="row g-3">
                                            <!-- PayPal -->
                                            <div class="col-md-4">
                                                <div class="border rounded p-3 h-100">
                                                    <div id="paypal-home"></div>
                                                    <small class="text-secondary d-block mt-2">Pago con PayPal</small>
                                                </div>
                                            </div>
                                            <!-- Bizum (manual) -->
                                            <div class="col-md-4">
                                                <div class="border rounded p-3 h-100">
                                                    <label class="form-label">Bizum (código)</label>
                                                    <input id="bizum_home_code" class="form-control" placeholder="Ej: BZ-123456">
                                                    <button type="button" id="btnBizumHomeOk" class="btn btn-outline-light btn-sm mt-2">He pagado</button>
                                                    <small class="text-secondary d-block mt-2">Verificaremos el código</small>
                                                </div>
                                            </div>
                                            <!-- Tarjeta (placeholder) -->
                                            <div class="col-md-4">
                                                <div class="border rounded p-3 h-100 d-flex flex-column">
                                                    <button type="button" class="btn btn-outline-secondary w-100" disabled>Tarjeta (próximamente)</button>
                                                    <small class="text-secondary d-block mt-2">TPV en futura iteración</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success mt-3 py-2">Este torneo es <strong>gratuito</strong>. Puedes guardar la inscripción directamente.</div>
                                    <?php endif; ?>
                                    <div id="payStatus" class="alert alert-secondary d-flex align-items-center gap-2 mt-3 py-2 px-3" role="alert">
                                        <i class="bi bi-hourglass-split"></i>
                                        <div><strong>Pago:</strong> pendiente</div>
                                    </div>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" value="1" id="chk_rgpd" required>
                                        <label class="form-check-label" for="chk_rgpd">
                                            Acepto el tratamiento de mis datos conforme a la <a class="link-light" href="<?= BASE_URL ?>legal/privacidad" target="_blank">Política de Privacidad</a>.
                                        </label>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" id="btnInsHome" class="btn btn-primary"
                                        <?= $precio > 0 ? 'disabled' : '' ?>>Guardar inscripción</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Modal: Bases -->
                <?php if (!empty($t['bases_pdf'])): ?>
                    <div class="modal fade" id="modalBasesHome" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content bg-dark">
                                <div class="modal-header">
                                    <h5 class="modal-title">Bases — <?= htmlspecialchars($t['titulo']) ?></h5>
                                    <a class="btn btn-sm btn-outline-light" target="_blank" rel="noopener" href="<?= BASE_URL . $t['bases_pdf'] ?>">Abrir en nueva pestaña</a>
                                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <iframe src="<?= BASE_URL . $t['bases_pdf'] ?>" style="width:100%; height:70vh;" frameborder="0"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </section>
        <?php endif; ?>
        <!-- ================= GALERÍA ================= -->
        <?php if (!empty($galeria)): ?>
            <div class="bg-dark py-3">
                <h1>Galerías</h1>
            </div>
            <section class="container my-4">
                <div class="row g-3">
                    <!-- FOTOS -->
                    <div class="col-12 col-md-4">
                        <?php
                        $fotosCount = (int)($galStats['fotos'] ?? 0);
                        $bgFoto = !empty($galeria[0]['archivo_path']) ? (BASE_URL . $galeria[0]['archivo_path']) : BASE_URL . 'Assets/img/galeria/placeholder-foto.jpg';
                        ?>
                        <a class="feature-card d-block text-decoration-none" href="<?= BASE_URL ?>Galeria/index"
                            onmousemove="this.style.setProperty('--x',`${event.offsetX}px`);this.style.setProperty('--y',`${event.offsetY}px`)">
                            <div class="bg-thumb" style="background-image:url('<?= $bgFoto ?>')"></div>
                            <div class="shine"></div>
                            <div class="content p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="chip"><i class="bi bi-images"></i> Fotos</span>
                                    <span class="badge text-bg-warning text-dark"><?= $fotosCount ?> nuevas</span>
                                </div>
                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-images icon-xl text-info"></i>
                                    <div>
                                        <div class="h6 m-0 text-light">Álbum de fotos</div>
                                        <div class="text-secondary small">Entrenamientos, torneos y vida del club</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- VÍDEOS -->
                    <div class="col-12 col-md-4">
                        <?php
                        $videosCount = (int)($galStats['videos'] ?? 0);
                        $bgVideo = BASE_URL . 'Assets/img/galeria/placeholder-video.jpg';
                        ?>
                        <a class="feature-card d-block text-decoration-none" href="<?= BASE_URL ?>Galeria/index#videos"
                            onmousemove="this.style.setProperty('--x',`${event.offsetX}px`);this.style.setProperty('--y',`${event.offsetY}px`)">
                            <div class="bg-thumb" style="background-image:url('<?= $bgVideo ?>')"></div>
                            <div class="shine"></div>
                            <div class="content p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="chip"><i class="bi bi-camera-video"></i> Vídeos</span>
                                    <span class="badge text-bg-info text-dark"><?= $videosCount ?> clips</span>
                                </div>
                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-play-circle icon-xl text-danger"></i>
                                    <div>
                                        <div class="h6 m-0 text-light">Vídeos del club</div>
                                        <div class="text-secondary small">Resúmenes, finales y miniaturas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- MOMENTOS DEL CLUB (idea #3) -->

                    <div class="col-12 col-md-4">
                        <a class="feature-card d-block text-decoration-none" href="<?= BASE_URL ?>Galeria/album/alumnos-del-club"
                            onmousemove="this.style.setProperty('--x',`${event.offsetX}px`);this.style.setProperty('--y',`${event.offsetY}px`)">
                            <div class="bg-thumb" style="background-image:linear-gradient(135deg,rgba(29,78,216,.25),rgba(0,0,0,.25)),url('<?= BASE_URL ?>Assets/img/galeria/placeholder-alumnos.jpg')"></div>
                            <div class="shine"></div>
                            <div class="content p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="chip"><i class="bi bi-people"></i> Alumnos</span>
                                    <span class="badge text-bg-light text-dark">Destacado</span>
                                </div>
                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-stars icon-xl text-warning"></i>
                                    <div>
                                        <div class="h6 m-0 text-light">Alumnos del club</div>
                                        <div class="text-secondary small">Fotos y vídeos de partidas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </section>
            <?php if (!empty($carouselAlumnos)): ?>
                <section class="container my-4">
                    <div class="d-flex align-items-center mb-2">
                        <h2 class="h5 text-light m-0"><i class="bi bi-people me-2"></i> Alumnos — últimas fotos</h2>
                        <a class="btn btn-sm btn-soft ms-auto" href="<?= BASE_URL ?>Galeria/album/alumnos-del-club">Ver álbum</a>
                    </div>

                    <div id="homeAlumnosCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-4 border border-secondary" style="background:#0b1220;">
                            <?php foreach ($carouselAlumnos as $i => $c): ?>
                                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                    <div class="ratio ratio-16x9 position-relative">
                                        <img src="<?= BASE_URL . $c['archivo_path'] ?>" class="d-block w-100" alt="<?= htmlspecialchars($c['titulo'] ?? '') ?>" style="object-fit:cover;">
                                        <?php if (!empty($c['titulo']) || !empty($c['alumno_nombre'])): ?>
                                            <div class="carousel-caption d-none d-md-block text-start">
                                                <div class="badge text-bg-dark border border-light-subtle mb-2">Alumno</div>
                                                <h5 class="mb-0"><?= htmlspecialchars($c['alumno_nombre'] ?? $c['titulo'] ?? '') ?></h5>
                                                <?php if (!empty($c['alumno_nombre']) && !empty($c['titulo'])): ?>
                                                    <div class="small text-light-50"><?= htmlspecialchars($c['titulo']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#homeAlumnosCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#homeAlumnosCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span>
                        </button>

                        <div class="carousel-indicators">
                            <?php foreach ($carouselAlumnos as $i => $c): ?>
                                <button type="button" data-bs-target="#homeAlumnosCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>


            <!-- Modal (lightbox) -->
            <div class="modal fade" id="homeGalModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content bg-black">
                        <div class="modal-header border-0">
                            <h5 class="modal-title text-light" id="homeGalTitle"></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-0" id="homeGalBody"></div>
                    </div>
                </div>
            </div>
<?php require_once 'Views/Galeria/album.php'; ?>
            <script>
                (function() {
                    const modal = document.getElementById('homeGalModal');
                    const body = document.getElementById('homeGalBody');
                    const title = document.getElementById('homeGalTitle');

                    modal.addEventListener('show.bs.modal', e => {
                        const t = e.relatedTarget;
                        const type = t?.dataset.type;
                        const src = t?.dataset.src || '';
                        title.textContent = t?.dataset.title || '';
                        if (type === 'img') {
                            body.innerHTML = `<img src="${src}" alt="" style="width:100%;height:auto;">`;
                        } else if (type === 'video') {
                            if (src.includes('youtube.com')) {
                                body.innerHTML = `<div class="ratio ratio-16x9"><iframe src="${src}" title="YouTube" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>`;
                            } else if (src) {
                                body.innerHTML = `<video src="${src}" controls autoplay style="width:100%;height:auto;"></video>`;
                            } else {
                                body.innerHTML = '<div class="text-center text-secondary py-5">Vídeo no disponible</div>';
                            }
                        }
                    });
                    modal.addEventListener('hidden.bs.modal', () => {
                        body.innerHTML = '';
                    });
                })();
            </script>
        <?php endif; ?>

    </main>

    <?php require BASE_PATH . 'Views/Home/_testimonios.php'; ?>
    <?php if ($precio > 0): ?>
        <script src="https://www.paypal.com/sdk/js?client-id=Ae5V5zumc_75UBf1BNHE9Z2uUKl7ttuudOhYSeg0yOTMowmTuDQq8isnxDhspEWT6CrF_9pp7fLnb55X&currency=EUR&intent=capture&components=buttons&disable-funding=card,credit,sepa,bancontact,blik,eps,giropay,ideal,mybank,p24"></script>
    <?php endif; ?>


    <script>
        (function() {
            const form = document.getElementById('formInsHome');
            const btn = document.getElementById('btnInsHome');
            const ok = document.getElementById('h_pago_ok');
            const modo = document.getElementById('h_pago_modo');
            const ref = document.getElementById('h_pago_ref');
            const rgpd = document.getElementById('chk_rgpd');
            const payBox = document.getElementById('payStatus');
            const precio = <?= json_encode($precio) ?>; // número

            const isPaid = () => (ok?.value === '1');
            const canSubmit = () => ((precio === 0 || isPaid()) && rgpd?.checked);

            function setPayStatus(type, html, icon) {
                if (!payBox) return;
                payBox.className = 'alert mt-3 py-2 px-3 d-flex align-items-center gap-2 alert-' + type;
                payBox.innerHTML = `<i class="bi ${icon||'bi-info-circle'}"></i><div>${html}</div>`;
            }

            function toggleSubmit() {
                if (!btn) return;
                if (canSubmit()) btn.removeAttribute('disabled');
                else btn.setAttribute('disabled', 'disabled');
            }
            rgpd?.addEventListener('change', toggleSubmit);

            // Torneo gratuito
            if (precio === 0) {
                if (ok) ok.value = '1';
                if (modo) modo.value = 'gratis';
                if (ref) ref.value = 'FREE';
                setPayStatus('success', '<strong>Pago:</strong> no requerido (gratuito).', 'bi-check2-circle');
            }

            // ========== PayPal: init robusto ==========
            let paypalRendered = false;

            function initPayPalButtons() {
                if (paypalRendered) return; // evita doble render
                const container = document.getElementById('paypal-home');
                if (!container) return;

                try {
                    paypal.Buttons({
                        fundingSource: paypal.FUNDING.PAYPAL,
                        style: {
                            layout: 'horizontal',
                            height: 40
                        },
                        createOrder: (data, actions) => actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: (precio).toFixed(2)
                                }
                            }]
                        }),
                        onApprove: async (data, actions) => {
                            try {
                                const details = await actions.order.capture();
                                ok.value = '1';
                                modo.value = 'paypal';
                                ref.value = details?.id || data?.orderID || 'PAYPAL_OK';
                                setPayStatus('success', '<strong>Pago:</strong> confirmado por PayPal. Ref: ' + (ref.value), 'bi-check2-circle');
                                toggleSubmit();
                                if (window.Swal) Swal.fire({
                                    icon: 'success',
                                    title: 'Pago confirmado',
                                    text: 'Ya puedes guardar tu inscripción.',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                                else alert('Pago confirmado. Ya puedes guardar la inscripción.');
                            } catch (e) {
                                console.error(e);
                                setPayStatus('danger', '<strong>Pago:</strong> fallo al capturar en PayPal. Intenta de nuevo.', 'bi-x-circle');
                                if (window.Swal) Swal.fire({
                                    icon: 'error',
                                    title: 'Error con PayPal',
                                    text: 'Intenta de nuevo.'
                                });
                            }
                        },
                        onError: (err) => {
                            console.error('[PayPal error]', err);
                            setPayStatus('warning', 'No se pudo cargar o usar PayPal. Prueba de nuevo o usa Bizum.', 'bi-exclamation-triangle');
                        }
                    }).render('#paypal-home');

                    paypalRendered = true;
                } catch (e) {
                    console.error('initPayPalButtons error', e);
                }
            }

            // Espera a que el SDK esté listo (por si llega tarde)
            if (precio > 0) {
                const tryInit = setInterval(() => {
                    if (window.paypal && document.getElementById('paypal-home')) {
                        clearInterval(tryInit);
                        initPayPalButtons();
                    }
                }, 200);
                // Además, si se abre el modal, intentamos de nuevo (útil cuando el div está oculto)
                document.getElementById('modalInsHome')?.addEventListener('shown.bs.modal', () => {
                    if (window.paypal) initPayPalButtons();
                });
            }

            // ========== Bizum (simulado/manual) ==========
            const btnBizum = document.getElementById('btnBizumHomeOk');
            btnBizum?.addEventListener('click', () => {
                const code = (document.getElementById('bizum_home_code')?.value || '').trim();
                if (!code) {
                    if (window.Swal) Swal.fire({
                        icon: 'info',
                        title: 'Bizum',
                        text: 'Introduce el código Bizum.'
                    });
                    else alert('Introduce el código Bizum.');
                    return;
                }
                ok.value = '1';
                modo.value = 'bizum';
                ref.value = code.toUpperCase();
                setPayStatus('success', '<strong>Pago:</strong> marcado como abonado por Bizum. Ref: ' + ref.value, 'bi-check2-circle');
                toggleSubmit();
                if (window.Swal) Swal.fire({
                    icon: 'success',
                    title: 'Bizum registrado',
                    text: 'Ya puedes guardar tu inscripción.',
                    timer: 1700,
                    showConfirmButton: false
                });
                else alert('Bizum marcado OK. Puedes guardar la inscripción.');
            });

            // Validación básica en submit
            form?.addEventListener('submit', (e) => {
                if (!canSubmit()) {
                    e.preventDefault();
                    if (!rgpd?.checked) {
                        if (window.Swal) Swal.fire({
                            icon: 'info',
                            title: 'Falta aceptar RGPD'
                        });
                        else alert('Debes aceptar la Política de Privacidad.');
                        return;
                    }
                    if (precio > 0 && !isPaid()) {
                        if (window.Swal) Swal.fire({
                            icon: 'info',
                            title: 'Pago pendiente',
                            text: 'Completa el pago antes de guardar.'
                        });
                        else alert('Completa el pago antes de guardar.');
                    }
                } else {
                    btn.setAttribute('disabled', 'disabled');
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
                }
            });

            // ========== Countdown: proteger para no romper ==========
            try {
                const badge = document.getElementById('torneo-countdown');
                if (badge && badge.dataset.inicio) {
                    const target = new Date(badge.dataset.inicio.replace(' ', 'T'));
                    const fmt = (n) => String(n).padStart(2, '0');

                    function tick() {
                        const now = new Date();
                        let s = (target - now) / 1000;
                        if (s <= 0) {
                            badge.textContent = '¡Hoy!';
                            return;
                        }
                        const d = Math.floor(s / 86400);
                        s %= 86400;
                        const h = Math.floor(s / 3600);
                        s %= 3600;
                        const m = Math.floor(s / 60);
                        badge.textContent = (d > 0 ? d + 'd ' : '') + fmt(h) + ':' + fmt(m);
                    }
                    tick();
                    setInterval(tick, 60000);
                }
            } catch (e) {
                console.warn('Countdown desactivado:', e);
            }

            // Asegurar apertura de modales con fallback
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(el => {
                el.addEventListener('click', () => {
                    const sel = el.getAttribute('data-bs-target');
                    const modalEl = document.querySelector(sel);
                    if (modalEl && window.bootstrap) {
                        const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
                        inst.show();
                    }
                });
            });
        })();
    </script>


    <?php require_once BASE_PATH . "Views/Templates/footer.php"; ?>
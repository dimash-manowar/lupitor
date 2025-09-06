<?php
// Views/Usuario/Templates/headerUsuario.php

$user   = $_SESSION['user'] ?? null;
$nombre = $user['nombre'] ?? 'Invitado';
$rol    = $user['rol'] ?? 'usuario';

// Ruta actual (según tu router ?url=...)
$uriPath = strtolower('/' . trim($_GET['url'] ?? '', '/'));
if ($uriPath === '//' || $uriPath === '/') {
    $uriPath = strtolower(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
}

// Funciones utilitarias para marcar activo
$hrefFromItem = function (array $it): string {
    $url  = trim((string)($it['url'] ?? ''), '/');
    $dest = trim((string)($it['destino'] ?? ''), '/');
    $path = $url !== '' ? $url : ($dest !== '' ? $dest : 'Usuario/index');
    return rtrim(BASE_URL, '/') . '/' . $path;
};
$matchItem = function (array $it) use ($uriPath): bool {
    $cand = [];
    if (!empty($it['url']))     $cand[] = '/' . trim(strtolower($it['url']), '/');
    if (!empty($it['destino'])) $cand[] = '/' . trim(strtolower($it['destino']), '/');
    foreach ($cand as $s) {
        if ($s !== '/' && (str_starts_with($uriPath, $s) || stripos($uriPath, $s) !== false)) return true;
    }
    return false;
};

// ---- Sidebar dinámico: espera $userItems desde el controlador ----
$userItems = $userItems ?? []; // NO cargar modelos aquí (mejor IDE / linter)

// árbol padre/hijos
$children = [];
$roots = [];
foreach ($userItems as $it) {
    $pid = (int)($it['parent_id'] ?? 0);
    if ($pid > 0) $children[$pid][] = $it;
    else $roots[] = $it;
}
$hasChildren = fn(int $id) => !empty($children[$id]);
$anyChildActive = function (int $id) use ($children, $matchItem): bool {
    foreach ($children[$id] ?? [] as $ch) if ($matchItem($ch)) return true;
    return false;
};

// CSRF
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($data['titulo'] ?? 'Panel') ?> — Usuario</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">
    <script>
        window.CSRF_TOKEN = "<?= htmlspecialchars($csrf) ?>";
    </script>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/usuario.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/mensajes.css?v=<?= time() ?>">
</head>

<body class="user-body">
    <div class="user-layout"><!-- wrapper principal: sidebar + área derecha -->

        <!-- Sidebar -->
        <aside id="userSidebar" class="user-sidebar">
            <div class="userbox">
                <div class="avatar" aria-hidden="true"><span><?= strtoupper(substr($nombre, 0, 1)) ?></span></div>
                <div class="info">
                    <div class="hello">Bienvenido</div>
                    <div class="name" title="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></div>
                    <div class="role">Rol: <strong><?= htmlspecialchars($rol) ?></strong></div>
                </div>
            </div>

            <nav class="user-nav">
                <?php $csrfNotif = $csrf; ?>
                <?php if (!empty($roots)): ?>
                    <?php foreach ($roots as $it): ?>
                        <?php
                        $id = (int)$it['id'];
                        $icon = $it['icono'] ?: 'bi-dot';
                        $title = htmlspecialchars($it['titulo']);
                        $isParent = $hasChildren($id);
                        $isActive = $matchItem($it);
                        $open = $isParent && ($isActive || $anyChildActive($id));
                        ?>

                        <?php if ($isParent): ?>
                            <div class="dropdown w-100">
                                <a href="#"
                                    class="item dropdown-toggle w-100 d-flex align-items-center <?= $open ? 'active show' : '' ?>"
                                    data-bs-toggle="dropdown" aria-expanded="<?= $open ? 'true' : 'false' ?>">
                                    <i class="bi <?= htmlspecialchars($icon) ?> me-2"></i> <?= $title ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark w-100 <?= $open ? 'show' : '' ?>">
                                    <?php foreach ($children[$id] as $ch): ?>
                                        <?php $childActive = $matchItem($ch);
                                        $href = $hrefFromItem($ch); ?>
                                        <li><a class="dropdown-item <?= $childActive ? 'active' : '' ?>" href="<?= $href ?>"><?= htmlspecialchars($ch['titulo']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <?php $href = $hrefFromItem($it); ?>
                            <a class="item <?= $isActive ? 'active' : '' ?>" href="<?= $href ?>" aria-current="<?= $isActive ? 'page' : 'false' ?>">
                                <i class="bi <?= htmlspecialchars($icon) ?> me-2"></i> <?= $title ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- fallback si BD vacío: padres con dropdown y sus tres secciones -->
                    <a class="item" href="<?= BASE_URL ?>usuario"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <div class="dropdown w-100">
                        <a class="item dropdown-toggle w-100 d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar-event me-2"></i> Ejercicios
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark w-100">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>UsuarioEjercicios/index">Listado</a></li>
                            
                        </ul>
                    </div>
                    <div class="dropdown w-100">
                        <a class="item dropdown-toggle w-100 d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar-event me-2"></i> Mensajes
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark w-100">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>UsuarioMensajes/index">Listado</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>UsuarioMensajes/crear">Crear</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="sep"></div>
                <a class="item" href="<?= BASE_URL ?>" target="_blank" rel="noopener"><i class="bi bi-globe2 me-2"></i> Ver web</a>
                <a class="item js-logout" href="<?= BASE_URL ?>auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Salir</a>
            </nav>
        </aside>
        <div class="user-overlay" hidden></div>
        <!-- Área derecha -->
        <div class="user-right">
            <!-- Topbar -->
            <div class="user-topbar">
                <div class="topbar-inner">
                    <div class="brand">
                        <button class="menu-btn d-lg-none" type="button"
                            aria-label="Abrir menú" aria-controls="userSidebar" aria-expanded="false">
                            <i class="bi bi-list"></i>
                        </button>
                        <img src="<?= BASE_URL ?>Assets/img/logo.png" alt="Logo" onerror="this.style.display='none'">
                        <span>Club de Ajedrez de Berriozar</span>
                    </div>

                    <div class="topbar-actions d-flex align-items-center gap-2">
                        <!-- Botón hamburguesa (si aún no lo pusiste) -->
                        <button class="menu-btn d-lg-none" type="button" aria-label="Abrir menú" aria-controls="userSidebar" aria-expanded="false">
                            <i class="bi bi-list"></i>
                        </button>

                        <!-- Campanita de notificaciones -->
                        <div class="btn-group me-2">
                            <a class="icon-btn position-relative" href="#" id="notifDrop" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Notificaciones">
                                <i class="bi bi-bell"></i>
                                <span id="notifBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end p-0 shadow" aria-labelledby="notifDrop" style="min-width:380px;">
                                <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
                                    <strong>Notificaciones</strong>
                                    <button class="btn btn-sm btn-outline-secondary" id="notifMarkAll"
                                        data-csrf="<?= htmlspecialchars($csrfNotif) ?>">
                                        <i class="bi bi-check2-all"></i> Marcar todas
                                    </button>
                                </div>
                                <div id="notifList" style="max-height:360px; overflow:auto;">
                                    <div class="p-3 text-secondary small">Cargando…</div>
                                </div>
                            </div>
                        </div>

                        <div class="welcome ms-1">Hola, <strong><?= htmlspecialchars($nombre) ?></strong></div>
                    </div>


                </div>
            </div>

            <!-- 👇 El main de contenido empieza aquí -->
            <main class="user-content">
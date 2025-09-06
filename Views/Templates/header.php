<?php
// Flags controladas desde el controlador (con valores por defecto)
$hideNavbar = (bool)($data['hideNavbar'] ?? false); // true = NO mostrar navbar
$showHero   = (bool)($data['showHero']   ?? false); // true = mostrar hero (banner) opcional
$pageTitle  = $data['titulo'] ?? 'Club de Ajedrez de Berriozar';

// Usuario (para el menú)
$user   = $_SESSION['user'] ?? null;
$nombre = $user['nombre'] ?? 'Invitado';
$rol    = $user['rol'] ?? 'usuario';
?>
<?php $hideNavbar = $data['hideNavbar'] ?? false; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club de Ajedrez de Berriozar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/vendor/chessboard/chessboard-1.0.0.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="<?= BASE_URL ?>Assets/css/estilos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/estilos.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/testimonios.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/noticias.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/eventos.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/club.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/torneos.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/inscripciones.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/galeria.css?v=<?= time() ?>">
    
   


</head>

<body class="bg-dark text-light">
    <!-- Navbar -->
    <?php if (!$hideNavbar): ?>

        <nav class="navbar navbar-expand-lg navbar-dark bg-black" style="height: 70px;">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= BASE_URL ?>">Ajedrez Berriozar</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>">Inicio</a></li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        <?php if (!empty($_SESSION['user'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <?= htmlspecialchars($nombre) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>perfil">Mi perfil</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>perfil/password">Cambiar contraseña</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <?php if (($rol ?? '') === 'admin'): ?>
                                        <li><a class="dropdown-item" href="<?= BASE_URL ?>admin">Admin</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item js-logout" href="<?= BASE_URL ?>auth/logout">Salir</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/login">Iniciar Sesión</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/registro">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Header -->
    <?php if ($showHero): ?>

        <header class="header text-center py-5">
            <div class="fondo bg-black bg-opacity-75 p-4 rounded">
                <h1 class="mt-3">Bienvenidos al Club de Ajedrez de Berriozar</h1>
                <p>Pasión por el ajedrez, formación y competición para todas las edades</p>
                <img src="<?= BASE_URL ?>Assets/img/ajedrez.png" alt="Logo Ajedrez" width="100">
            </div>
        </header>
    <?php endif; ?>
<?php
require_once __DIR__ . '/funciones.php';
$base = $base ?? '../';
?>
<!DOCTYPE html>
<html lang="es">
<script>
    const temaGuardado = localStorage.getItem('tema') || 'oscuro';
    document.documentElement.dataset.tema = temaGuardado;
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? e($titulo) : 'RentAr' ?></title>
    <link rel="stylesheet" href="<?= $base ?>css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <header class="header">
        <figure class="logo">
            <a href="../07 - CRUD CATALOGO (alvarez)/index.php">
                <img src="<?= $base ?>img/logo-blanco.png" alt="Logo de RentAr">
            </a>
        </figure>
        <nav class="navbar">
            <a href="../07 - CRUD CATALOGO (alvarez)/index.php">Inicio</a>
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Catálogo</a>
            <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php">Reservas</a>
            <?php if (esArrendador() || esAdmin()): ?>
                <a href="../02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php">Publicaciones</a>
            <?php endif; ?>
            <a href="../08 - HISTORIAL (castri)/empresas.php">Empresas</a>
            <a href="../09 - REGISTRO Y LOGIN (saul)/recogida.php">Recogida</a>
            <?php if (esAdmin()):
                $totalPendientesNav = vehiculosPendientes($pdo) + usuariosSinVerificar($pdo);
            ?>
                <a href="../05 - ADMINISTRACION (samuel)/panel.php" style="color:var(--secondary); font-weight:700;">
                    Admin<?php if ($totalPendientesNav > 0): ?><span class="admin-badge-count"><?= $totalPendientesNav ?></span><?php endif; ?>
                </a>
            <?php endif; ?>
            <button type="button" class="btn-tema" onclick="cambiarTema()" title="Cambiar tema">
                <span class="tema-oscuro"><?= icono('sol') ?></span>
                <span class="tema-claro"><?= icono('luna') ?></span>
            </button>
            <?php if (haySesion()):
                $sinLeer = notificacionesSinLeer($pdo); ?>
                <a href="../03 - PAGOS Y PUNTOS (samuel)/puntos.php" class="nav-icono" title="Mis puntos"><?= icono('estrella') ?></a>
                <a href="../04 - PERFIL Y CUENTA (samuel)/favoritos.php" class="nav-icono" title="Mis favoritos"><?= icono('corazon') ?></a>
                <a href="../04 - PERFIL Y CUENTA (samuel)/notificaciones.php" class="nav-icono nav-campana" title="Notificaciones">
                    <?= icono('campana') ?><?php if ($sinLeer > 0): ?><span class="admin-badge-count"><?= $sinLeer ?></span><?php endif; ?>
                </a>
            <?php endif; ?>
            <a href="<?= haySesion() ? '../04 - PERFIL Y CUENTA (samuel)/editar-perfil.php' : '../09 - REGISTRO Y LOGIN (saul)/login.php' ?>" title="<?= haySesion() ? 'Mi perfil' : 'Iniciar sesión' ?>">
                <?php if (haySesion() && !empty($_SESSION['foto'])): ?>
                    <img src="<?= $base . e($_SESSION['foto']) ?>" alt="cuenta" class="nav-avatar-foto">
                <?php else: ?>
                    <img src="<?= $base ?>img/avatar.png" alt="cuenta">
                <?php endif; ?>
            </a>
            <?php if (haySesion()): ?>
                <a href="../09 - REGISTRO Y LOGIN (saul)/logout.php" class="nav-icono" title="Cerrar sesión"><?= icono('salir') ?></a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="sidebar">
        <h3>Información</h3>
        <div class="sidebar-links">
            <a href="../10 - PAGINAS PUBLICAS (samuel)/quienes.php">¿Quiénes somos?</a>
            <a href="../10 - PAGINAS PUBLICAS (samuel)/trabajo.php">Trabaja con nosotros</a>
            <a href="../10 - PAGINAS PUBLICAS (samuel)/terminos.php">Términos y Condiciones</a>
            <a href="../10 - PAGINAS PUBLICAS (samuel)/privacidad.php">Política de Privacidad</a>
            <a href="../10 - PAGINAS PUBLICAS (samuel)/creadores.php">Creadores</a>
        </div>
    </div>

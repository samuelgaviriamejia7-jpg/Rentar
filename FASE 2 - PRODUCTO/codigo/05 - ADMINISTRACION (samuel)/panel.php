<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereAdmin();

$totalVehiculos = $pdo->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
$totalReservas  = $pdo->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
$totalUsuarios  = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol <> 'admin'")->fetchColumn();
$totalPendientes = $pdo->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'pendiente'")->fetchColumn();
$totalSinVerificar = usuariosSinVerificar($pdo);

$st = $pdo->prepare(
    "SELECT v.*, c.nombre AS categoria
     FROM vehiculos v
     LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
     WHERE v.id_admin = ?
     ORDER BY v.fecha_creacion DESC"
);
$st->execute([usuarioId()]);
$misVehiculos = $st->fetchAll();

$titulo = 'Panel de administrador';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1>Panel de administrador</h1>
            <p>Hola, <?= e($_SESSION['nombre']) ?> — gestiona el catálogo de RentAr</p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="admin-stats">
            <div class="admin-stat-card">
                <h2><?= (int)$totalVehiculos ?></h2>
                <span>Vehículos en el sistema</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= (int)$totalReservas ?></h2>
                <span>Reservas totales</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= (int)$totalUsuarios ?></h2>
                <span>Usuarios registrados</span>
            </div>
            <div class="admin-stat-card" style="<?= $totalPendientes > 0 ? 'border:2px solid #d68910;' : '' ?>">
                <h2 style="<?= $totalPendientes > 0 ? 'color:#d68910;' : '' ?>"><?= (int)$totalPendientes ?></h2>
                <span>Publicaciones por revisar</span>
            </div>
            <div class="admin-stat-card" style="<?= $totalSinVerificar > 0 ? 'border:2px solid #d68910;' : '' ?>">
                <h2 style="<?= $totalSinVerificar > 0 ? 'color:#d68910;' : '' ?>"><?= (int)$totalSinVerificar ?></h2>
                <span>Usuarios sin verificar</span>
            </div>
        </div>

        <nav class="extra-links" style="margin: 20px 0;">
            <a href="agregar-vehiculo.php" class="primario">+ Agregar vehículo</a>
            <a href="aprobaciones.php" style="background: <?= $totalPendientes > 0 ? '#d68910' : 'var(--secondary)' ?>;">
                Revisar publicaciones<?= $totalPendientes > 0 ? " ($totalPendientes)" : '' ?>
            </a>
            <a href="usuarios.php" style="background: <?= $totalSinVerificar > 0 ? '#d68910' : 'var(--secondary)' ?>;">
                Verificar usuarios<?= $totalSinVerificar > 0 ? " ($totalSinVerificar)" : '' ?>
            </a>
            <a href="admin-vehiculos.php">Ver todos los vehículos</a>
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Ir al catálogo público</a>
        </nav>

        <article class="formulario">
            <h2>Últimos vehículos que publicaste</h2>

            <?php if (!$misVehiculos): ?>
                <p>Todavía no has publicado ningún vehículo. <a href="agregar-vehiculo.php">Agrega el primero</a>.</p>
            <?php endif; ?>

            <?php foreach (array_slice($misVehiculos, 0, 5) as $v): ?>
            <div class="admin-vehiculo-row">
                <img src="../<?= e($v['imagen']) ?>" alt="" class="admin-vehiculo-mini">
                <div class="admin-vehiculo-info">
                    <strong><?= e($v['marca'] . ' ' . $v['modelo']) ?></strong>
                    <span><?= pesos($v['precio_dia']) ?>/día</span>
                </div>
                <span class="badge-estado badge-<?= e($v['estado']) ?>"><?= e(ucfirst($v['estado'])) ?></span>
            </div>
            <?php endforeach; ?>
        </article>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $idVeh  = (int)$_POST['id_vehiculo'];
    $nuevo  = $_POST['nuevo_estado'];
    $validos = ['disponible', 'mantenimiento', 'inactivo'];

    if (in_array($nuevo, $validos, true)) {
        $pdo->prepare("UPDATE vehiculos SET estado = ? WHERE id_vehiculo = ?")
            ->execute([$nuevo, $idVeh]);
        mensaje('Estado del vehículo actualizado.');
    }
    ir('admin-vehiculos.php');
}

$st = $pdo->query(
    "SELECT v.*, c.nombre AS categoria, CONCAT(u.nombre,' ',u.apellido) AS publicado_por
     FROM vehiculos v
     LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
     LEFT JOIN usuarios u ON u.id_usuario = v.id_admin
     ORDER BY v.fecha_creacion DESC"
);
$vehiculos = $st->fetchAll();

$titulo = 'Vehículos registrados';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Vehículos registrados</h1>
            <p><?= count($vehiculos) ?> vehículos en el sistema</p>
        </article>

        <?php mostrarMensaje(); ?>

        <nav class="extra-links espacio-abajo">
            <a href="agregar-vehiculo.php" class="primario">+ Agregar vehículo</a>
            <a href="aprobaciones.php">Revisar pendientes</a>
            <a href="panel.php">Volver al panel</a>
        </nav>

        <?php foreach ($vehiculos as $v): ?>
        <article class="formulario admin-vehiculo-card" style="margin-bottom:16px;">

            <div class="fila-flexible-arriba">
                <img src="../<?= e($v['imagen']) ?>" alt="" class="foto-vehiculo-lista">

                <div class="columna-crece">
                    <h2><?= e($v['marca'] . ' ' . $v['modelo']) ?> (<?= (int)$v['anio'] ?>)</h2>
                    <p><strong>Placa:</strong> <?= e($v['placa']) ?></p>
                    <p><strong>Categoría:</strong> <?= e($v['categoria'] ?? 'Sin categoría') ?></p>
                    <p><strong>Precio:</strong> <?= pesos($v['precio_dia']) ?>/día</p>
                    <p><strong>Publicado por:</strong> <?= e($v['publicado_por'] ?? 'N/D') ?></p>
                    <p><strong>Estado actual:</strong>
                        <span class="badge-estado badge-<?= e($v['estado']) ?>"><?= e(ucfirst($v['estado'])) ?></span>
                    </p>
                </div>

                <?php if (in_array($v['estado'], ['pendiente', 'rechazado'], true)): ?>
                    <div style="min-width:200px;">
                        <p class="texto-suave">
                            Este vehículo <?= $v['estado'] === 'pendiente' ? 'está pendiente de revisión' : 'fue rechazado' ?>.
                        </p>
                        <a href="aprobaciones.php" class="btn-form" style="display:block; text-align:center; text-decoration:none;">
                            Ir a revisarlo
                        </a>
                    </div>
                <?php else: ?>
                    <form method="post" style="min-width:200px;">
                        <input type="hidden" name="cambiar_estado" value="1">
                        <input type="hidden" name="id_vehiculo" value="<?= $v['id_vehiculo'] ?>">
                        <select name="nuevo_estado" required>
                            <option value="disponible"    <?= $v['estado']=='disponible'    ? 'selected' : '' ?>>Disponible</option>
                            <option value="mantenimiento" <?= $v['estado']=='mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                            <option value="inactivo"      <?= $v['estado']=='inactivo'      ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                        <button type="submit" class="btn-form" style="margin-top:8px;">Actualizar estado</button>
                    </form>
                <?php endif; ?>
            </div>

        </article>
        <?php endforeach; ?>

        <?php if (!$vehiculos): ?>
            <p>No hay vehículos registrados todavía.</p>
        <?php endif; ?>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

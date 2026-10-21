<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVeh  = (int)($_POST['id_vehiculo'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'aprobar') {
        $pdo->prepare("UPDATE vehiculos SET estado = 'disponible', motivo_rechazo = NULL WHERE id_vehiculo = ?")
            ->execute([$idVeh]);
        $stD = $pdo->prepare("SELECT id_admin, marca, modelo FROM vehiculos WHERE id_vehiculo = ?");
        $stD->execute([$idVeh]);
        $d = $stD->fetch();
        crearNotificacion($pdo, $d['id_admin'], 'Tu ' . $d['marca'] . ' ' . $d['modelo'] . ' fue aprobado y ya está en el catálogo.', '02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php');
        mensaje('Vehículo aprobado. Ya aparece en el catálogo.');

    } elseif ($accion === 'rechazar') {
        $motivo = trim($_POST['motivo'] ?? 'No cumple con los requisitos de publicación.');
        $pdo->prepare("UPDATE vehiculos SET estado = 'rechazado', motivo_rechazo = ? WHERE id_vehiculo = ?")
            ->execute([$motivo, $idVeh]);
        $stD = $pdo->prepare("SELECT id_admin, marca, modelo FROM vehiculos WHERE id_vehiculo = ?");
        $stD->execute([$idVeh]);
        $d = $stD->fetch();
        crearNotificacion($pdo, $d['id_admin'], 'Tu ' . $d['marca'] . ' ' . $d['modelo'] . ' fue rechazado. Revisa el motivo.', '02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php');
        mensaje('Vehículo rechazado.');
    }
    ir('aprobaciones.php');
}

$st = $pdo->query(
    "SELECT v.*, CONCAT(u.nombre,' ',u.apellido) AS publicado_por, u.correo, u.telefono
     FROM vehiculos v
     LEFT JOIN usuarios u ON u.id_usuario = v.id_admin
     WHERE v.estado = 'pendiente'
     ORDER BY v.fecha_creacion ASC"
);
$pendientes = $st->fetchAll();

$titulo = 'Aprobaciones pendientes';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Aprobaciones pendientes</h1>
            <p><?= count($pendientes) ?> vehículo<?= count($pendientes) === 1 ? '' : 's' ?> esperando revisión</p>
        </article>

        <?php mostrarMensaje(); ?>

        <nav class="extra-links espacio-abajo">
            <a href="panel.php">Volver al panel</a>
        </nav>

        <?php if (!$pendientes): ?>
            <div class="estado-vacio">
                <img src="../img/carro.png" alt="">
                <h3>No hay publicaciones pendientes</h3>
                <p>Cuando un cliente publique un vehículo, aparecerá aquí para tu revisión.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($pendientes as $v): ?>
        <article class="formulario espacio-abajo">

            <div class="fila-flexible-arriba">
                <img src="../<?= e($v['imagen']) ?>" alt="" class="foto-vehiculo-grande">

                <div class="columna-crece">
                    <h2><?= e($v['marca'] . ' ' . $v['modelo']) ?> (<?= (int)$v['anio'] ?>)</h2>
                    <p><strong>Placa:</strong> <?= e($v['placa']) ?></p>
                    <p><strong>Precio propuesto:</strong> <?= pesos($v['precio_dia']) ?>/día</p>
                    <p><strong>Transmisión:</strong> <?= e($v['transmision']) ?> · <strong>Combustible:</strong> <?= e($v['combustible']) ?></p>
                    <p><strong>Pasajeros:</strong> <?= (int)$v['pasajeros'] ?> · <strong>Puertas:</strong> <?= (int)$v['puertas'] ?></p>
                    <?php if ($v['descripcion']): ?>
                        <p><strong>Descripción:</strong> <?= e($v['descripcion']) ?></p>
                    <?php endif; ?>
                    <hr style="margin:10px 0;">
                    <p><strong>Publicado por:</strong> <?= e($v['publicado_por'] ?? 'Usuario') ?></p>
                    <p><strong>Contacto:</strong> <?= e($v['correo']) ?> <?= $v['telefono'] ? '· ' . e($v['telefono']) : '' ?></p>
                </div>
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:16px;">

                <form method="post" onsubmit="return confirm('¿Aprobar este vehículo? Aparecerá de inmediato en el catálogo.');" style="flex:1; min-width:160px;">
                    <input type="hidden" name="id_vehiculo" value="<?= $v['id_vehiculo'] ?>">
                    <input type="hidden" name="accion" value="aprobar">
                    <button type="submit" class="btn-form btn-verde">Aprobar</button>
                </form>

                <form method="post" style="flex:2; min-width:240px;" onsubmit="return confirm('¿Rechazar esta publicación?');">
                    <input type="hidden" name="id_vehiculo" value="<?= $v['id_vehiculo'] ?>">
                    <input type="hidden" name="accion" value="rechazar">
                    <input type="text" name="motivo" placeholder="Motivo del rechazo (se le muestra al usuario)" style="margin-bottom:8px;">
                    <button type="submit" class="btn-form btn-rojo">Rechazar</button>
                </form>

            </div>

        </article>
        <?php endforeach; ?>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

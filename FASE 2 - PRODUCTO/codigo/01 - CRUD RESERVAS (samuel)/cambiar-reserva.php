<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$id = (int)($_GET['id'] ?? $_POST['id_reserva'] ?? 0);

$st = $pdo->prepare("SELECT * FROM reservas WHERE id_reserva = ? AND id_usuario = ?");
$st->execute([$id, usuarioId()]);
$r = $st->fetch();

if (!$r) {
    mensaje('Reserva no encontrada.', 'error');
    ir('mis-reservas.php');
}
if ($r['estado'] !== 'confirmada') {
    mensaje('Esta reserva ya no se puede modificar.', 'error');
    ir('mis-reservas.php');
}

$vehiculos = $pdo->query("SELECT id_vehiculo, marca, modelo, precio_dia
                          FROM vehiculos WHERE estado = 'disponible'")->fetchAll();
$sedes = $pdo->query("SELECT id_sede, nombre FROM sedes WHERE activa = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nVehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $nRecogida = (int)($_POST['id_sede_recogida'] ?? 0);
    $nInicio   = trim($_POST['fecha_inicio'] ?? '');
    $nFin      = trim($_POST['fecha_fin'] ?? '');

    if (!$nVehiculo || !$nRecogida || !$nInicio || !$nFin) {
        mensaje('Completa todos los campos.', 'error');
    } elseif ($nInicio < date('Y-m-d')) {
        mensaje('No puedes mover la reserva a una fecha pasada.', 'error');
    } elseif ($nFin < $nInicio) {
        mensaje('La fecha de entrega no puede ser anterior a la de renta.', 'error');
    } elseif (!vehiculoDisponible($pdo, $nVehiculo, $nInicio, $nFin, $id)) {
        mensaje('Ese vehículo no está libre en esas fechas.', 'error');
    } else {
        try {
            $pdo->beginTransaction();

            $stP = $pdo->prepare("SELECT precio_dia FROM vehiculos WHERE id_vehiculo = ? AND estado = 'disponible'");
            $stP->execute([$nVehiculo]);
            $precioDia = $stP->fetchColumn();

            if ($precioDia === false) {
                $pdo->rollBack();
                mensaje('El vehículo seleccionado no está disponible.', 'error');
                ir("cambiar-reserva.php?id=$id");
            }

            $dias  = max((new DateTime($nInicio))->diff(new DateTime($nFin))->days, 1);
            $total = $precioDia * $dias;

            registrarCambio($pdo, $id, 'id_vehiculo',      $r['id_vehiculo'],      $nVehiculo, usuarioId());
            registrarCambio($pdo, $id, 'id_sede_recogida', $r['id_sede_recogida'], $nRecogida, usuarioId());
            registrarCambio($pdo, $id, 'fecha_inicio',     $r['fecha_inicio'],     $nInicio,   usuarioId());
            registrarCambio($pdo, $id, 'fecha_fin',        $r['fecha_fin'],        $nFin,      usuarioId());
            registrarCambio($pdo, $id, 'total',            $r['total'],            $total,     usuarioId());

            $pdo->prepare(
                "UPDATE reservas
                 SET id_vehiculo = ?, id_sede_recogida = ?, fecha_inicio = ?,
                     fecha_fin = ?, precio_dia = ?, dias = ?, total = ?
                 WHERE id_reserva = ? AND id_usuario = ?"
            )->execute([$nVehiculo, $nRecogida, $nInicio, $nFin,
                        $precioDia, $dias, $total, $id, usuarioId()]);

            $pdo->commit();
            mensaje('Reserva actualizada.');
            ir("mis-reservas.php?descargar=$id");

        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            mensaje('No se pudo actualizar la reserva.', 'error');
        }
    }
}

$titulo = 'Cambiar reserva';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

    <main class="hero hero-utility">
        <section class="hero-info">
            <div class="hero-title">
                <h1>Cambiar reserva</h1>
                <p>Modifica los datos de tu reserva</p>
            </div>
            <?php mostrarMensaje(); ?>
            <div class="resumen-reserva">
                <h2>Reserva actual</h2>
                <p><strong>Código:</strong> <?= e($r['codigo']) ?></p>
                <p><strong>Fecha de renta:</strong> <?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?></p>
                <p><strong>Fecha de entrega:</strong> <?= date('d/m/Y', strtotime($r['fecha_fin'])) ?></p>
                <p><strong>Total:</strong> <?= pesos($r['total']) ?></p>
            </div>

    <section class="formularios">
        <form method="post">
            <input type="hidden" name="id_reserva" value="<?= $r['id_reserva'] ?>">
            <div class="formulario">
                <h2>Vehículo</h2>
                <select name="id_vehiculo" required>
                    <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= $v['id_vehiculo'] ?>"
                            <?= $v['id_vehiculo'] == $r['id_vehiculo'] ? 'selected' : '' ?>>
                            <?= e($v['marca'].' '.$v['modelo']) ?> — <?= pesos($v['precio_dia']) ?>/día
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formulario">
                <h2>Sede de recogida</h2>
                <select name="id_sede_recogida" required>
                    <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['id_sede'] ?>"
                            <?= $s['id_sede'] == $r['id_sede_recogida'] ? 'selected' : '' ?>>
                            <?= e($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formulario">
                <h2>Nueva fecha de renta</h2>
                <input type="date" name="fecha_inicio" value="<?= e($r['fecha_inicio']) ?>" required>
            </div>
            <div class="formulario">
                <h2>Nueva fecha de entrega</h2>
                <input type="date" name="fecha_fin" value="<?= e($r['fecha_fin']) ?>" required>
            </div>
            <button type="submit" class="btn-form">GUARDAR CAMBIOS</button>
        </form>
    </section>

        <nav class="extra-links espacio-arriba">
            <a href="mis-reservas.php">Volver a mis reservas</a>
        </nav>
        </section>
    </main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

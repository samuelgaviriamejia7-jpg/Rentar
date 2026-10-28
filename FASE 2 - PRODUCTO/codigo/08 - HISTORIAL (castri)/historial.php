<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$idReserva = (int)($_GET['id'] ?? 0);

$sql = "SELECT h.*, r.codigo FROM historial_reservas h
        JOIN reservas r ON r.id_reserva = h.id_reserva
        WHERE r.id_usuario = ?";
$params = [usuarioId()];

if ($idReserva) { $sql .= " AND h.id_reserva = ?"; $params[] = $idReserva; }
$sql .= " ORDER BY h.fecha_cambio DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$cambios = $st->fetchAll();

$etiquetas = [
    'id_vehiculo'      => 'Vehículo',
    'id_sede_recogida' => 'Sede de recogida',
    'id_sede_devolucion' => 'Sede de devolución',
    'fecha_inicio'     => 'Fecha de renta',
    'fecha_fin'        => 'Fecha de entrega',
    'total'            => 'Total',
    'estado'           => 'Estado',
];

$titulo = 'Historial de reservas';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

    <main class="hero hero-utility">
        <section class="hero-info">
            <div class="hero-title"><h1>Historial de cambios</h1></div>

            <?php if (!$cambios): ?>
                <div class="estado-vacio">
                    <img src="../img/carro.png" alt="">
                    <h3>No hay modificaciones registradas</h3>
                    <p>Cuando cambies o canceles una reserva, el movimiento va a aparecer acá.</p>
                </div>
            <?php else: ?>
            <table class="tabla-historial">
                <tr>
                    <th>Reserva</th><th>Campo</th><th>Antes</th><th>Después</th><th>Fecha</th>
                </tr>
                <?php foreach ($cambios as $c): ?>
                <tr>
                    <td><?= e($c['codigo']) ?></td>
                    <td><?= e($etiquetas[$c['campo_modificado']] ?? $c['campo_modificado']) ?></td>
                    <td><?= e($c['valor_anterior']) ?></td>
                    <td><?= e($c['valor_nuevo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($c['fecha_cambio'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <nav class="extra-links">
                <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php">Volver a mis reservas</a>
            </nav>
        </section>
    </main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

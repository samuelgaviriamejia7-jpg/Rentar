<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereArrendador();

$st = $pdo->prepare(
    "SELECT DATE_FORMAT(r.fecha_inicio, '%Y-%m') AS mes, SUM(r.total) AS bruto, COUNT(*) AS reservas
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     JOIN pagos p ON p.id_reserva = r.id_reserva AND p.estado = 'aprobado'
     WHERE v.id_admin = ? AND r.estado <> 'cancelada'
       AND r.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
     GROUP BY mes
     ORDER BY mes"
);
$st->execute([usuarioId()]);
$filas = [];
foreach ($st->fetchAll() as $f) {
    $filas[$f['mes']] = $f;
}

$meses = [];
for ($i = 5; $i >= 0; $i--) {
    $clave = date('Y-m', strtotime("-$i month"));
    $meses[] = [
        'clave'    => $clave,
        'nombre'   => ucfirst(strftime_es($clave)),
        'bruto'    => (float)($filas[$clave]['bruto'] ?? 0),
        'reservas' => (int)($filas[$clave]['reservas'] ?? 0),
    ];
}

$maximo = max(array_column($meses, 'bruto')) ?: 1;
$brutoTotal = array_sum(array_column($meses, 'bruto'));
$comisionTotal = $brutoTotal * COMISION_PLATAFORMA;
$netoTotal = $brutoTotal - $comisionTotal;

$st = $pdo->prepare(
    "SELECT CONCAT(v.marca,' ',v.modelo) AS vehiculo, COUNT(r.id_reserva) AS reservas,
            COALESCE(SUM(r.total), 0) AS bruto
     FROM vehiculos v
     LEFT JOIN reservas r ON r.id_vehiculo = v.id_vehiculo AND r.estado <> 'cancelada'
          AND EXISTS (SELECT 1 FROM pagos p
                       WHERE p.id_reserva = r.id_reserva AND p.estado = 'aprobado')
     WHERE v.id_admin = ?
     GROUP BY v.id_vehiculo
     ORDER BY bruto DESC"
);
$st->execute([usuarioId()]);
$porVehiculo = $st->fetchAll();

$titulo = 'Mis ingresos';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1>Mis ingresos</h1>
            <p>Lo que han generado tus vehículos en los últimos 6 meses</p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="admin-stats">
            <div class="admin-stat-card">
                <h2><?= pesos($brutoTotal) ?></h2>
                <span>Facturado</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= pesos($comisionTotal) ?></h2>
                <span>Comisión RentAr (25%)</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= pesos($netoTotal) ?></h2>
                <span>Tu ganancia</span>
            </div>
        </div>

        <article class="formulario grafica-caja">
            <h2>Mes a mes</h2>
            <div class="grafica">
                <?php foreach ($meses as $m): ?>
                    <div class="grafica-columna">
                        <span class="grafica-valor"><?= $m['bruto'] > 0 ? pesos($m['bruto'] * (1 - COMISION_PLATAFORMA)) : '' ?></span>
                        <div class="grafica-barra" style="height: <?= max(4, round($m['bruto'] / $maximo * 100)) ?>%"></div>
                        <span class="grafica-mes"><?= e($m['nombre']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="grafica-nota">Las barras muestran tu ganancia después de la comisión.</p>
        </article>

        <article class="formulario">
            <h2>Por vehículo</h2>
            <?php if (!$porVehiculo): ?>
                <p>Todavía no tienes vehículos publicados.</p>
            <?php else: ?>
                <table class="tabla-historial">
                    <tr><th>Vehículo</th><th>Reservas</th><th>Facturado</th><th>Tu ganancia</th></tr>
                    <?php foreach ($porVehiculo as $v): ?>
                        <tr>
                            <td><?= e($v['vehiculo']) ?></td>
                            <td><?= (int)$v['reservas'] ?></td>
                            <td><?= pesos($v['bruto']) ?></td>
                            <td><?= pesos($v['bruto'] * (1 - COMISION_PLATAFORMA)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </article>

        <nav class="extra-links">
            <a href="../02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php">Mis publicaciones</a>
            <a href="../02 - CRUD VEHICULOS (samuel)/publicar-vehiculo.php" class="primario">Publicar otro vehículo</a>
        </nav>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

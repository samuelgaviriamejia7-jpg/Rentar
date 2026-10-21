<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereArrendador();

$idVehiculo = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare(
    "SELECT v.*, c.nombre AS categoria
     FROM vehiculos v
     LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
     WHERE v.id_vehiculo = ? AND (v.id_admin = ? OR ?)"
);
$st->execute([$idVehiculo, usuarioId(), (int)esAdmin()]);
$v = $st->fetch();

if (!$v) {
    mensaje('Esa publicación no existe o no te pertenece.', 'error');
    ir('mis-publicaciones.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enVenta     = isset($_POST['en_venta']) ? 1 : 0;
    $precioVenta = (float)str_replace(['.', ','], ['', '.'], $_POST['precio_venta'] ?? '0');
    $porcentaje  = (int)($_POST['porcentaje_abono'] ?? 10);

    if ($enVenta && $precioVenta <= 0) {
        mensaje('Escribe el precio de venta del vehículo.', 'error');
    } elseif ($porcentaje < 0 || $porcentaje > 50) {
        mensaje('El porcentaje de abono debe estar entre 0 y 50.', 'error');
    } else {
        $pdo->prepare("UPDATE vehiculos SET en_venta = ?, precio_venta = ?, porcentaje_abono = ?
                       WHERE id_vehiculo = ? AND id_admin = ?")
            ->execute([$enVenta, $enVenta ? $precioVenta : null, $porcentaje, $idVehiculo, usuarioId()]);
        mensaje($enVenta ? 'Tu vehículo quedó publicado también en venta.' : 'Se quitó de la venta.');
    }
    ir('ver-publicacion.php?id=' . $idVehiculo);
}

$stats = estadisticasVehiculo($pdo, $idVehiculo);
$ubicacion = estadoVehiculo($pdo, $idVehiculo);

$stProxima = $pdo->prepare(
    "SELECT r.fecha_inicio, r.fecha_fin, CONCAT(u.nombre,' ',u.apellido) AS cliente
     FROM reservas r
     JOIN usuarios u ON u.id_usuario = r.id_usuario
     WHERE r.id_vehiculo = ? AND r.estado IN ('confirmada','en_curso') AND r.fecha_fin >= CURDATE()
     ORDER BY r.fecha_inicio ASC
     LIMIT 1"
);
$stProxima->execute([$idVehiculo]);
$proxima = $stProxima->fetch();

$etiquetas = [
    'pendiente'     => 'En revisión',
    'disponible'    => 'Publicado y disponible',
    'rechazado'     => 'Rechazado',
    'mantenimiento' => 'En mantenimiento',
    'inactivo'      => 'Inactivo',
];

$titulo = $v['marca'] . ' ' . $v['modelo'];
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1><?= e($v['marca'] . ' ' . $v['modelo']) ?></h1>
            <p><?= (int)$v['anio'] ?> · Publicado el <?= date('d/m/Y', strtotime($v['fecha_creacion'])) ?></p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="detalle-publicacion">

            <figure class="detalle-publicacion-foto">
                <img src="../<?= e($v['imagen']) ?>" alt="<?= e($v['modelo']) ?>">
                <span class="badge-estado badge-<?= e($v['estado']) ?>">
                    <?= e($etiquetas[$v['estado']] ?? ucfirst($v['estado'])) ?>
                </span>
            </figure>

            <div class="detalle-publicacion-info">

                <?php if ($v['estado'] === 'rechazado' && $v['motivo_rechazo']): ?>
                    <div class="aviso aviso-error">
                        <strong>Motivo del rechazo:</strong> <?= e($v['motivo_rechazo']) ?>
                    </div>
                <?php elseif ($v['estado'] === 'pendiente'): ?>
                    <div class="aviso aviso-error">
                        Tu vehículo está en revisión. Te avisaremos cuando el equipo lo apruebe.
                    </div>
                <?php endif; ?>

                <p class="detalle-publicacion-precio"><?= pesos($v['precio_dia']) ?> <span>/ día</span></p>

                <dl class="ficha-tecnica">
                    <div><dt>Placa</dt><dd><?= e($v['placa']) ?></dd></div>
                    <div><dt>Categoría</dt><dd><?= e($v['categoria'] ?: 'Sin categoría') ?></dd></div>
                    <div><dt>Tipo</dt><dd><?= $v['tipo'] === 'moto' ? 'Moto' : 'Carro' ?></dd></div>
                    <div><dt>Transmisión</dt><dd><?= e($v['transmision']) ?></dd></div>
                    <div><dt>Combustible</dt><dd><?= e($v['combustible']) ?></dd></div>
                    <div><dt>Pasajeros</dt><dd><?= (int)$v['pasajeros'] ?></dd></div>
                    <div><dt>Puertas</dt><dd><?= (int)$v['puertas'] ?></dd></div>
                    <div><dt>Kilometraje</dt><dd><?= $v['kilometraje_ilimitado'] ? 'Ilimitado' : 'Limitado' ?></dd></div>
                </dl>

                <?php if ($v['descripcion']): ?>
                    <div class="detalle-publicacion-descripcion">
                        <h3>Descripción</h3>
                        <p><?= e($v['descripcion']) ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div class="estado-vehiculo estado-<?= e($ubicacion['clave']) ?>">
            <div class="estado-vehiculo-cabecera">
                <span class="estado-punto"></span>
                <strong><?= e($ubicacion['titulo']) ?></strong>
            </div>
            <p class="estado-vehiculo-lugar"><?= e($ubicacion['ubicacion']) ?></p>
            <p class="estado-vehiculo-detalle"><?= e($ubicacion['detalle']) ?></p>
        </div>

        <div class="admin-stats" style="margin-top:6px;">
            <div class="admin-stat-card">
                <h2><?= (int)$stats['total_reservas'] ?></h2>
                <span>Reservas realizadas</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= pesos($stats['ingresos']) ?></h2>
                <span>Ingresos generados</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= $proxima ? date('d/m', strtotime($proxima['fecha_inicio'])) : '—' ?></h2>
                <span><?= $proxima ? 'Próxima reserva' : 'Sin reservas próximas' ?></span>
            </div>
        </div>

        <?php if ($proxima): ?>
            <p class="detalle-publicacion-nota">
                Reservado por <?= e($proxima['cliente']) ?>, del
                <?= date('d/m/Y', strtotime($proxima['fecha_inicio'])) ?> al
                <?= date('d/m/Y', strtotime($proxima['fecha_fin'])) ?>.
            </p>
        <?php endif; ?>

        <article class="formulario venta-caja">
            <h2>Alquiler con opción de compra</h2>
            <p class="texto-suave">
                Si activas esta opción, una parte de lo que te paguen por cada alquiler
                queda abonada a la compra del vehículo. El cliente ve cuánto lleva abonado
                y puede completar la diferencia cuando quiera.
            </p>

            <form method="post">
                <label class="venta-check">
                    <input type="checkbox" name="en_venta" value="1" <?= $v['en_venta'] ? 'checked' : '' ?>>
                    Poner este vehículo en venta
                </label>

                <div class="venta-campos">
                    <input type="text" name="precio_venta" placeholder="Precio de venta (ej: 38000000)"
                           value="<?= $v['precio_venta'] ? (int)$v['precio_venta'] : '' ?>">
                    <input type="number" name="porcentaje_abono" min="0" max="50"
                           placeholder="% que se abona" value="<?= (int)$v['porcentaje_abono'] ?>">
                </div>

                <button type="submit" class="btn-form">GUARDAR</button>
            </form>
        </article>

        <nav class="extra-links espacio-arriba">
            <a href="mis-publicaciones.php">Volver a mis publicaciones</a>
            <?php if ($v['estado'] === 'disponible'): ?>
                <a href="../01 - CRUD RESERVAS (samuel)/reservas.php?id=<?= (int)$v['id_vehiculo'] ?>">Ver en el catálogo</a>
            <?php endif; ?>
        </nav>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

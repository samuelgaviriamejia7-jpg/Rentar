<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$st = $pdo->prepare(
    "SELECT r.*, v.marca, v.modelo, v.imagen,
            s1.nombre AS recogida, s2.nombre AS devolucion
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     JOIN sedes s1 ON s1.id_sede = r.id_sede_recogida
     JOIN sedes s2 ON s2.id_sede = r.id_sede_devolucion
     WHERE r.id_reserva = ? AND r.id_usuario = ?"
);
$st->execute([(int)($_GET['id'] ?? 0), usuarioId()]);
$r = $st->fetch();

if (!$r) {
    mensaje('Reserva no encontrada.', 'error');
    ir('mis-reservas.php');
}

$etiquetasEstado = [
    'confirmada' => 'Confirmada',
    'en_curso'   => 'En curso',
    'finalizada' => 'Finalizada',
    'cancelada'  => 'Cancelada',
];

$titulo = 'Comprobante ' . $r['codigo'];
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1><?= $r['estado'] === 'cancelada' ? 'Reserva cancelada' : 'Renta confirmada' ?></h1>
            <p><?= $r['estado'] === 'cancelada' ? 'Este comprobante ya no está vigente' : 'Guarda este comprobante para recoger tu vehículo' ?></p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="recibo" id="recibo">

            <div class="recibo-cabecera">
                <span class="recibo-marca">RentAr</span>
                <span class="badge-estado badge-<?= e($r['estado']) ?>">
                    <?= e($etiquetasEstado[$r['estado']] ?? ucfirst($r['estado'])) ?>
                </span>
            </div>

            <div class="recibo-vehiculo">
                <img src="../<?= e($r['imagen']) ?>" alt="">
                <div>
                    <h2><?= e($r['marca'] . ' ' . $r['modelo']) ?></h2>
                    <span class="recibo-codigo"><?= e($r['codigo']) ?></span>
                </div>
            </div>

            <div class="recibo-ruta">
                <div>
                    <span class="recibo-etiqueta">Recogida</span>
                    <strong><?= e($r['recogida']) ?></strong>
                    <span><?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?></span>
                </div>
                <span class="recibo-flecha">→</span>
                <div>
                    <span class="recibo-etiqueta">Devolución</span>
                    <strong><?= e($r['devolucion']) ?></strong>
                    <span><?= date('d/m/Y', strtotime($r['fecha_fin'])) ?></span>
                </div>
            </div>

            <div class="recibo-corte" aria-hidden="true"></div>

            <div class="recibo-total">
                <div class="recibo-linea">
                    <span><?= pesos($r['precio_dia']) ?> / día × <?= (int)$r['dias'] ?> día<?= $r['dias'] == 1 ? '' : 's' ?></span>
                    <span><?= pesos($r['precio_dia'] * $r['dias']) ?></span>
                </div>
                <div class="recibo-linea recibo-linea-total">
                    <span>Total</span>
                    <span><?= pesos($r['total']) ?></span>
                </div>
            </div>

            <p class="recibo-pie">Reservado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($r['fecha_creacion'])) ?></p>

        </div>

        <div class="recibo-acciones">
            <button type="button" class="btn-form" onclick="window.print()">Imprimir</button>
            <a href="comprobante-jpg.php?id=<?= $r['id_reserva'] ?>" class="btn-form btn-form-claro">Descargar JPG</a>
            <a href="comprobante-txt.php?id=<?= $r['id_reserva'] ?>" class="btn-form btn-form-claro">Descargar TXT</a>
        </div>

        <div class="extra-links" style="margin-top: 16px;">
            <a href="../07 - CRUD CATALOGO (alvarez)/index.php">Volver al inicio</a>
            <a href="mis-reservas.php" class="primario">Mis reservas</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

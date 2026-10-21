<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereLogin();

$st = $pdo->prepare(
    "SELECT DISTINCT v.id_vehiculo, v.marca, v.modelo, v.anio, v.imagen
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     WHERE r.id_usuario = ? AND r.estado <> 'cancelada' AND v.en_venta = 1
     ORDER BY v.marca"
);
$st->execute([usuarioId()]);
$vehiculos = $st->fetchAll();

$titulo = 'Mis abonos';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1>Mis abonos</h1>
            <p>Cada vez que alquilas uno de estos vehículos, una parte queda abonada a su compra</p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (!$vehiculos): ?>
            <div class="estado-vacio">
                <img src="../img/carro.png" alt="">
                <h3>Todavía no tienes abonos</h3>
                <p>Alquila un vehículo que esté en venta y empieza a abonar a su compra.</p>
                <div class="extra-links">
                    <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php" class="primario">Ver catálogo</a>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($vehiculos as $v):
            $abono = abonoDelUsuario($pdo, $v['id_vehiculo']);
            if (!$abono) continue; ?>

            <article class="tarjeta-abono">
                <div class="fila-flexible">
                    <img src="../<?= e($v['imagen']) ?>" alt="" class="foto-vehiculo-lista">
                    <div class="columna-crece">
                        <h2><?= e($v['marca'] . ' ' . $v['modelo']) ?> (<?= (int)$v['anio'] ?>)</h2>
                        <p class="texto-suave">Abonas el <?= $abono['porcentaje'] ?>% de cada alquiler</p>
                    </div>
                    <span class="abono-precio"><?= pesos($abono['precio']) ?></span>
                </div>

                <div class="barra-abono">
                    <div class="barra-abono-relleno" style="width: <?= $abono['avance'] ?>%"></div>
                </div>

                <div class="abono-cifras">
                    <span><strong><?= pesos($abono['abonado']) ?></strong> abonados</span>
                    <span><?= $abono['avance'] ?>%</span>
                    <span>Faltan <strong><?= pesos($abono['falta']) ?></strong></span>
                </div>

                <nav class="extra-links">
                    <a href="../01 - CRUD RESERVAS (samuel)/reservas.php?id=<?= $v['id_vehiculo'] ?>">Alquilar de nuevo</a>
                    <a href="../03 - PAGOS Y PUNTOS (samuel)/pago.php?tipo=compra&id=<?= $v['id_vehiculo'] ?>" class="primario">
                        Completar compra
                    </a>
                </nav>
            </article>

        <?php endforeach; ?>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

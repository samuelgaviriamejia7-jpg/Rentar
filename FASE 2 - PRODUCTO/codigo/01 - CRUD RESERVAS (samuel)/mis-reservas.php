<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$st = $pdo->prepare(
    "SELECT r.*, v.marca, v.modelo, v.imagen,
            CONCAT(v.marca,' ',v.modelo) AS vehiculo,
            s1.nombre AS recogida, s2.nombre AS devolucion,
            (SELECT p.referencia FROM pagos p
              WHERE p.id_reserva = r.id_reserva AND p.estado = 'aprobado'
              ORDER BY p.fecha_pago DESC LIMIT 1) AS pago_referencia
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     JOIN sedes s1    ON s1.id_sede    = r.id_sede_recogida
     JOIN sedes s2    ON s2.id_sede    = r.id_sede_devolucion
     WHERE r.id_usuario = ?
     ORDER BY r.fecha_creacion DESC"
);
$st->execute([usuarioId()]);
$reservas = $st->fetchAll();

$descargarAhora = (int)($_GET['descargar'] ?? 0);

$titulo = 'Mis reservas';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Mis reservas</h1>
            <p>Hola, <?= e($_SESSION['nombre']) ?></p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (!$reservas): ?>
            <div class="estado-vacio">
                <img src="../img/carro.png" alt="">
                <h3>Aún no tienes reservas</h3>
                <p>Cuando reserves un vehículo, va a aparecer acá con todos sus detalles.</p>
                <nav class="extra-links">
                    <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php" class="primario">Ver catálogo</a>
                </nav>
            </div>
        <?php endif; ?>

        <?php foreach ($reservas as $r): ?>
        <article class="tarjeta-reserva">
            <div class="tarjeta-reserva-cabecera">
                <div>
                    <h2><?= e($r['vehiculo']) ?></h2>
                    <span class="reserva-codigo"><?= e($r['codigo']) ?></span>
                </div>
                <span class="badge-estado badge-<?= e($r['estado']) ?>"><?= e(ucfirst($r['estado'])) ?></span>
            </div>

            <div class="tarjeta-reserva-detalle">
                <div>
                    <span class="detalle-etiqueta">Recogida</span>
                    <strong><?= e($r['recogida']) ?></strong>
                </div>
                <div>
                    <span class="detalle-etiqueta">Fechas</span>
                    <strong><?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?> – <?= date('d/m/Y', strtotime($r['fecha_fin'])) ?></strong>
                </div>
                <div>
                    <span class="detalle-etiqueta">Total</span>
                    <strong><?= pesos($r['total']) ?></strong>
                </div>
                <div>
                    <span class="detalle-etiqueta">Pago</span>
                    <?php if ($r['pago_referencia']): ?>
                        <strong class="pago-listo">Pagada · <?= e($r['pago_referencia']) ?></strong>
                    <?php elseif ($r['estado'] === 'cancelada'): ?>
                        <strong class="pago-pendiente">Sin pago</strong>
                    <?php else: ?>
                        <strong class="pago-pendiente">Pendiente</strong>
                        <a href="../03 - PAGOS Y PUNTOS (samuel)/pago.php?tipo=reserva&id=<?= $r['id_reserva'] ?>"
                           class="btn-pagar-ahora">Pagar <?= pesos($r['total']) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="tarjeta-reserva-acciones">
                <a href="comprobante-jpg.php?id=<?= $r['id_reserva'] ?>">Comprobante</a>
                <a href="comprobante.php?id=<?= $r['id_reserva'] ?>">Ver</a>
                <?php if ($r['estado'] === 'confirmada'): ?>
                    <a href="cambiar-reserva.php?id=<?= $r['id_reserva'] ?>">Cambiar</a>
                    <a href="cancelar-reserva.php?id=<?= $r['id_reserva'] ?>"
                       onclick="return confirm('¿Seguro que quieres cancelar esta reserva?');">Cancelar</a>
                <?php endif; ?>
                <?php if ($r['estado'] === 'finalizada'): ?>
                    <a href="../04 - PERFIL Y CUENTA (samuel)/calificar.php?id=<?= $r['id_reserva'] ?>" class="accion-pagar">Calificar</a>
                <?php endif; ?>
                <a href="../08 - HISTORIAL (castri)/historial.php?id=<?= $r['id_reserva'] ?>">Historial</a>
            </nav>
        </article>
        <?php endforeach; ?>

        <nav class="extra-links">
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Nueva reserva</a>
            <a href="../02 - CRUD VEHICULOS (samuel)/abonos.php">Mis abonos</a>
            <a href="../02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php">Mis publicaciones</a>
            <a href="../04 - PERFIL Y CUENTA (samuel)/editar-perfil.php">Editar perfil</a>
            <a href="../09 - REGISTRO Y LOGIN (saul)/logout.php">Cerrar sesión</a>
        </nav>
    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<?php if ($descargarAhora): ?>
    <iframe src="comprobante-jpg.php?id=<?= $descargarAhora ?>" hidden></iframe>
<?php endif; ?>

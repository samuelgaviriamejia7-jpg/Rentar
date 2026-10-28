<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereArrendador();

$st = $pdo->prepare(
    "SELECT id_vehiculo, marca, modelo, anio, precio_dia, imagen, estado, motivo_rechazo, fecha_creacion
     FROM vehiculos
     WHERE id_admin = ?
     ORDER BY fecha_creacion DESC"
);
$st->execute([usuarioId()]);
$publicaciones = $st->fetchAll();

$etiquetas = [
    'pendiente'     => 'En revisión',
    'disponible'    => 'Publicado',
    'rechazado'     => 'Rechazado',
    'mantenimiento' => 'En mantenimiento',
    'inactivo'      => 'Inactivo',
];

$titulo = 'Mis publicaciones';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Mis publicaciones</h1>
            <p>Los vehículos que has subido a RentAr y su estado de aprobación</p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (haySesion() && !usuarioVerificado()): ?>
            <div class="aviso aviso-error">
                Tu cuenta está pendiente de verificación. Puedes publicar, pero el vehículo no saldrá al catálogo hasta que un administrador te verifique.
            </div>
        <?php endif; ?>

        <?php if (!$publicaciones): ?>
            <div class="estado-vacio">
                <img src="../img/carro.png" alt="">
                <h3>Todavía no has publicado ningún vehículo</h3>
                <p>Si tienes carro y no lo usas siempre, publícalo y empieza a generar ingresos.</p>
                <div class="extra-links">
                    <a href="publicar-vehiculo.php" class="primario">Publicar mi vehículo</a>
                </div>
            </div>
        <?php else: ?>

        <div class="grid-publicaciones">
            <?php foreach ($publicaciones as $v): ?>
            <a href="ver-publicacion.php?id=<?= (int)$v['id_vehiculo'] ?>" class="tarjeta-publicacion">
                <img src="../<?= e($v['imagen']) ?>" alt="" class="tarjeta-publicacion-img">
                <div class="tarjeta-publicacion-info">
                    <span class="badge-estado badge-<?= e($v['estado']) ?>">
                        <?= e($etiquetas[$v['estado']] ?? ucfirst($v['estado'])) ?>
                    </span>
                    <h3><?= e($v['marca'] . ' ' . $v['modelo']) ?></h3>
                    <p><?= (int)$v['anio'] ?> · <?= pesos($v['precio_dia']) ?>/día</p>
                    <?php if ($v['estado'] === 'disponible'):
                        $u = estadoVehiculo($pdo, $v['id_vehiculo']); ?>
                        <p class="tarjeta-publicacion-lugar estado-<?= e($u['clave']) ?>">
                            <span class="estado-punto"></span><?= e($u['titulo']) ?> · <?= e($u['ubicacion']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($v['estado'] === 'rechazado' && $v['motivo_rechazo']): ?>
                        <p class="tarjeta-publicacion-motivo">Motivo: <?= e($v['motivo_rechazo']) ?></p>
                    <?php endif; ?>
                </div>
                <span class="tarjeta-publicacion-ver">Ver información →</span>
            </a>
            <?php endforeach; ?>
        </div>

        <nav class="extra-links espacio-arriba">
            <a href="publicar-vehiculo.php" class="primario">Publicar otro vehículo</a>
            <a href="../03 - PAGOS Y PUNTOS (samuel)/ingresos.php">Ver mis ingresos</a>
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Volver al catálogo</a>
        </nav>

        <?php endif; ?>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

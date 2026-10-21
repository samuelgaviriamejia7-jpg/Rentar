<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $volver     = $_POST['volver'] ?? 'favoritos.php';

    if (esFavorito($pdo, $idVehiculo)) {
        $pdo->prepare("DELETE FROM favoritos WHERE id_usuario = ? AND id_vehiculo = ?")
            ->execute([usuarioId(), $idVehiculo]);
    } else {
        $pdo->prepare("INSERT IGNORE INTO favoritos (id_usuario, id_vehiculo) VALUES (?, ?)")
            ->execute([usuarioId(), $idVehiculo]);
    }
    ir($volver);
}

$st = $pdo->prepare(
    "SELECT v.*, c.nombre AS categoria
     FROM favoritos f
     JOIN vehiculos v  ON v.id_vehiculo  = f.id_vehiculo
     LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
     WHERE f.id_usuario = ?
     ORDER BY f.fecha DESC"
);
$st->execute([usuarioId()]);
$vehiculos = $st->fetchAll();

$titulo = 'Mis favoritos';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="catalogo">
    <div class="catalogo-text">
        <h2>Mis favoritos</h2>
    </div>

    <?php mostrarMensaje(); ?>

    <?php if (!$vehiculos): ?>
        <div class="estado-vacio">
            <img src="../img/carro.png" alt="">
            <h3>Todavía no has guardado vehículos</h3>
            <p>Toca el corazón de cualquier vehículo del catálogo para tenerlo a mano.</p>
            <div class="extra-links">
                <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php" class="primario">Ir al catálogo</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="opciones">
        <?php foreach ($vehiculos as $v): $cal = calificacionVehiculo($pdo, $v['id_vehiculo']); ?>
            <div class="card-envoltura">
            <form method="post" action="favoritos.php" class="form-favorito">
                <input type="hidden" name="id_vehiculo" value="<?= $v['id_vehiculo'] ?>">
                <input type="hidden" name="volver" value="favoritos.php">
                <button type="submit" class="btn-favorito activo"
                        title="Quitar de favoritos"><?= icono('corazon', true) ?></button>
            </form>
            <a href="../01 - CRUD RESERVAS (samuel)/reservas.php?id=<?= $v['id_vehiculo'] ?>" class="card">
                <h3><?= e($v['marca'] . ' ' . $v['modelo']) ?></h3>
                <div class="card-info">
                    <span><?= pesos($v['precio_dia']) ?>/día</span>
                </div>
                <?php if ($cal['total']): ?>
                    <span class="card-estrellas"><?= estrellas($cal['promedio']) ?> <?= $cal['promedio'] ?></span>
                <?php endif; ?>
                <img src="../<?= e($v['imagen']) ?>" alt="" class="card-car">
            </a>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

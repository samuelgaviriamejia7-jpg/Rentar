<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$idReserva = (int)($_GET['id'] ?? $_POST['id_reserva'] ?? 0);

$st = $pdo->prepare(
    "SELECT r.id_reserva, r.id_vehiculo, r.codigo, r.estado,
            CONCAT(v.marca,' ',v.modelo) AS vehiculo, v.imagen
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     WHERE r.id_reserva = ? AND r.id_usuario = ?"
);
$st->execute([$idReserva, usuarioId()]);
$r = $st->fetch();

if (!$r) {
    mensaje('Reserva no encontrada.', 'error');
    ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php');
}
if ($r['estado'] !== 'finalizada') {
    mensaje('Solo puedes calificar una reserva que ya terminó.', 'error');
    ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php');
}

$st = $pdo->prepare("SELECT estrellas, comentario FROM calificaciones WHERE id_reserva = ?");
$st->execute([$idReserva]);
$calificacion = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estrellas   = (int)($_POST['estrellas'] ?? 0);
    $comentario  = trim($_POST['comentario'] ?? '');

    if ($estrellas < 1 || $estrellas > 5) {
        mensaje('Elige de 1 a 5 estrellas.', 'error');
    } else {
        $pdo->prepare(
            "INSERT INTO calificaciones (id_reserva, id_vehiculo, id_usuario, estrellas, comentario)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE estrellas = VALUES(estrellas), comentario = VALUES(comentario)"
        )->execute([$idReserva, $r['id_vehiculo'], usuarioId(), $estrellas, $comentario ?: null]);

        $stDueno = $pdo->prepare("SELECT id_admin FROM vehiculos WHERE id_vehiculo = ?");
        $stDueno->execute([$r['id_vehiculo']]);
        crearNotificacion($pdo, $stDueno->fetchColumn(),
            'Tu ' . $r['vehiculo'] . ' recibió una calificación de ' . $estrellas . ' estrellas.',
            '02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php');

        mensaje('Gracias por calificar.');
        ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php');
    }
}

$titulo = 'Calificar';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Calificar</h1>
            <p><?= e($r['vehiculo']) ?> · reserva <?= e($r['codigo']) ?></p>
        </article>

        <?php mostrarMensaje(); ?>

        <form method="post" class="formulario tarjeta-calificar">
            <input type="hidden" name="id_reserva" value="<?= (int)$idReserva ?>">

            <h2>¿Cómo te fue con el vehículo?</h2>

            <div class="selector-estrellas">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="estrellas" id="e<?= $i ?>" value="<?= $i ?>"
                           <?= (int)($calificacion['estrellas'] ?? 0) === $i ? 'checked' : '' ?>>
                    <label for="e<?= $i ?>" title="<?= $i ?> estrellas"><?= icono('estrella', true) ?></label>
                <?php endfor; ?>
            </div>

            <textarea name="comentario" rows="3" maxlength="300"
                      placeholder="Cuéntanos cómo estaba el vehículo (opcional)"><?= e($calificacion['comentario'] ?? '') ?></textarea>

            <button type="submit" class="btn-form">GUARDAR CALIFICACIÓN</button>
        </form>

        <nav class="extra-links">
            <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php">Volver a mis reservas</a>
        </nav>
    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

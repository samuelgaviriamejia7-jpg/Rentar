<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = ?")->execute([usuarioId()]);
    ir('notificaciones.php');
}

$st = $pdo->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 40");
$st->execute([usuarioId()]);
$notificaciones = $st->fetchAll();

$titulo = 'Notificaciones';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Notificaciones</h1>
            <p>Lo que ha pasado con tus reservas y publicaciones</p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (!$notificaciones): ?>
            <div class="estado-vacio">
                <img src="../img/carro.png" alt="">
                <h3>No tienes notificaciones</h3>
                <p>Acá te avisamos cuando te reserven un vehículo o cambie el estado de tu cuenta.</p>
            </div>
        <?php else: ?>

            <form method="post" style="margin-bottom:14px;">
                <button type="submit" class="btn-form btn-compacto">
                    Marcar todas como leídas
                </button>
            </form>

            <?php foreach ($notificaciones as $n): ?>
                <article class="notificacion <?= $n['leida'] ? '' : 'sin-leer' ?>">
                    <span class="estado-punto"></span>
                    <div>
                        <p><?= e($n['mensaje']) ?></p>
                        <span class="notificacion-fecha"><?= date('d/m/Y H:i', strtotime($n['fecha'])) ?></span>
                    </div>
                    <?php if ($n['enlace']): ?>
                        <a href="../<?= e($n['enlace']) ?>">Ver</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$disponibles = puntosDisponibles($pdo);

$st = $pdo->prepare("SELECT detalle, puntos, fecha FROM movimientos_puntos
                     WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 30");
$st->execute([usuarioId()]);
$movimientos = $st->fetchAll();

$st = $pdo->prepare("SELECT COALESCE(SUM(puntos), 0) FROM movimientos_puntos WHERE id_usuario = ? AND puntos > 0");
$st->execute([usuarioId()]);
$ganados = (int)$st->fetchColumn();

$titulo = 'Mis puntos';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1>RentAr Rewards</h1>
            <p>Ganas <?= PUNTOS_POR_DIA ?> puntos por cada día que alquilas</p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="tarjeta-puntos">
            <span class="detalle-etiqueta">Tienes</span>
            <h2><?= number_format($disponibles, 0, ',', '.') ?> <span>puntos</span></h2>
            <p>Equivalen a <?= pesos($disponibles * PESOS_POR_PUNTO) ?> de descuento</p>
            <p class="puntos-nota">
                Puedes usarlos al pagar una reserva. Cada punto vale <?= pesos(PESOS_POR_PUNTO) ?>
                y puedes cubrir hasta la mitad del total.
            </p>
        </div>

        <div class="admin-stats espacio-arriba">
            <div class="admin-stat-card">
                <h2><?= number_format($ganados, 0, ',', '.') ?></h2>
                <span>Puntos ganados en total</span>
            </div>
            <div class="admin-stat-card">
                <h2><?= number_format(max($ganados - $disponibles, 0), 0, ',', '.') ?></h2>
                <span>Puntos ya usados</span>
            </div>
        </div>

        <article class="formulario">
            <h2>Movimientos</h2>
            <?php if (!$movimientos): ?>
                <p class="texto-suave">Todavía no tienes movimientos. Haz tu primera reserva y empieza a acumular.</p>
            <?php else: ?>
                <table class="tabla-historial">
                    <tr><th>Detalle</th><th>Puntos</th><th>Fecha</th></tr>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= e($m['detalle']) ?></td>
                            <td class="<?= $m['puntos'] > 0 ? 'puntos-suma' : 'puntos-resta' ?>">
                                <?= $m['puntos'] > 0 ? '+' : '' ?><?= (int)$m['puntos'] ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </article>

        <nav class="extra-links">
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php" class="primario">Reservar y ganar puntos</a>
            <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php">Mis reservas</a>
        </nav>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

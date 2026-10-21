<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereAdmin();

$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (guardarVehiculoDesdeFormulario($pdo, 'disponible', $errores)) {
        mensaje('Vehículo publicado correctamente en el catálogo.');
        ir('panel.php');
    }
    mensaje(implode(' ', $errores), 'error');
}

$titulo = 'Agregar vehículo';
$textoBoton = 'PUBLICAR VEHÍCULO';
$enlaces = ['admin-vehiculos.php' => 'Ver todos los vehículos', 'panel.php' => 'Volver al panel'];
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Agregar vehículo</h1>
            <p>Como administrador, el vehículo queda publicado de inmediato</p>
        </article>

        <?php mostrarMensaje(); ?>
    </section>
</main>

<?php require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/form-vehiculo.php'; ?>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

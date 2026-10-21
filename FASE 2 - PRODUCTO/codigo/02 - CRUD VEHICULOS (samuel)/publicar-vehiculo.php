<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereArrendador();

$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (guardarVehiculoDesdeFormulario($pdo, 'pendiente', $errores)) {
        mensaje('Tu vehículo fue enviado. Un administrador lo revisará antes de publicarlo en el catálogo.');
        ir('mis-publicaciones.php');
    }
    mensaje(implode(' ', $errores), 'error');
}

$titulo = 'Publica tu vehículo';
$textoBoton = 'ENVIAR PARA REVISIÓN';
$enlaces = ['mis-publicaciones.php' => 'Ver mis publicaciones', '../07 - CRUD CATALOGO (alvarez)/catalogo.php' => 'Volver al catálogo'];
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Publica tu vehículo</h1>
            <p>Gana dinero con tu carro mientras no lo usas</p>
        </article>

        <?php mostrarMensaje(); ?>

        <article class="formulario">
            <h2>Cómo funciona</h2>
            <p class="texto-suave">
                Completa los datos de tu vehículo y súbelo con una foto clara.
                Nuestro equipo revisa la información y, una vez aprobado, tu carro
                aparece en el catálogo de RentAr disponible para que otros lo reserven.
            </p>
        </article>
    </section>
</main>

<?php require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/form-vehiculo.php'; ?>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

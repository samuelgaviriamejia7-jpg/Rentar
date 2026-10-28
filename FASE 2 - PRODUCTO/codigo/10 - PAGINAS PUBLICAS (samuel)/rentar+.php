<?php
$base = '../';
$titulo = 'RentAr+ | RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';

$beneficios = [
    ['Prioridad en reservas', 'Tu solicitud se procesa antes que la de los demás usuarios.'],
    ['Descuentos exclusivos', 'Tarifas más bajas en toda la flota, todos los días del año.'],
    ['Atención VIP 24/7', 'Una línea de soporte aparte, disponible a cualquier hora.'],
    ['Acceso anticipado', 'Reserva los vehículos nuevos antes de que salgan al catálogo.'],
    ['Cancelaciones flexibles', 'Cancela sin costo hasta 24 horas antes de la recogida.'],
    ['RentAr Rewards Premium', 'Acumulas el doble de puntos por cada día de alquiler.'],
];
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1>RentAr<span class="plus-mas">+</span></h1>
            <p>La experiencia premium para quienes buscan más comodidad, mejores vehículos y beneficios únicos</p>
        </article>

        <?php mostrarMensaje(); ?>

        <div class="plus-layout">

            <aside class="plus-plan">
                <span class="plus-tag">Exclusivo</span>
                <p class="plus-precio"><?= pesos(39900) ?><span>/ mes</span></p>
                <p class="plus-detalle">Sin cláusula de permanencia. Puedes cancelar cuando quieras.</p>
                <a href="../03 - PAGOS Y PUNTOS (samuel)/pago.php?tipo=plus" class="btn-form plus-btn">Suscribirme ahora</a>
                <p class="plus-nota">El cobro se hace una vez al mes, siempre el mismo día.</p>
            </aside>

            <div class="plus-beneficios">
                <h2>Qué incluye</h2>
                <?php foreach ($beneficios as $b): ?>
                    <div class="plus-item">
                        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        <div>
                            <strong><?= e($b[0]) ?></strong>
                            <span><?= e($b[1]) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <nav class="extra-links espacio-arriba">
            <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Ver catálogo</a>
            <a href="../08 - HISTORIAL (castri)/empresas.php">Planes para empresas</a>
        </nav>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

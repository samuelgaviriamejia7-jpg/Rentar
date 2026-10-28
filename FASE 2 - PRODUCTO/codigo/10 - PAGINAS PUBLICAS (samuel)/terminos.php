<?php
$base = '../';
$titulo = 'Términos y Condiciones | RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero">

        <section class="terminos">

            <article class="hero-title">
                <h1>Términos de uso</h1>

                <p>
                    Consulta nuestras políticas y condiciones de alquiler.
                </p>
            </article>

            <article class="terminos-box">

                <h2>Condiciones generales</h2>

                <p>
                    El cliente deberá presentar un documento de identidad válido
                    y licencia de conducción vigente al momento de recoger el vehículo.
                </p>

                <p>
                    El vehículo debe devolverse en la fecha y hora acordadas
                    durante la reserva.
                </p>

                <p>
                    Cualquier daño ocasionado por uso indebido será responsabilidad
                    del conductor registrado.
                </p>

                <p>
                    No está permitido fumar dentro de los vehículos de la empresa.
                </p>

                <p>
                    RentAr se reserva el derecho de cancelar reservas
                    en caso de incumplimiento de políticas internas.
                </p>
            </article>

            <article class="terminos-box">

                <h2>Cuentas y publicaciones</h2>

                <p>
                    Toda cuenta debe ser verificada por un administrador antes de poder
                    reservar o publicar. La información entregada debe ser veraz.
                </p>

                <p>
                    Los vehículos publicados por arrendadores pasan por aprobación antes
                    de aparecer en el catálogo. RentAr puede rechazar una publicación
                    indicando el motivo.
                </p>

                <p>
                    RentAr no es propietario de los vehículos publicados por sus usuarios.
                    Actúa como intermediario entre el arrendador y el arrendatario, y
                    retiene una comisión del 25% sobre el valor de cada alquiler.
                </p>

                <p>
                    El usuario puede eliminar su cuenta desde su perfil. Las reservas y
                    pagos ya registrados se conservan porque hacen parte del historial de
                    la otra parte.
                </p>

            </article>

            <article class="terminos-box">

                <h2>Pagos y puntos</h2>

                <p>
                    La pasarela de pago de esta plataforma es de prueba: no realiza cobros
                    reales y no almacena números de tarjeta. De cada tarjeta se guardan
                    únicamente los últimos cuatro dígitos, para que el usuario reconozca
                    su pago.
                </p>

                <p>
                    Cada pago aprobado genera un número de referencia único que queda
                    asociado a la reserva.
                </p>

                <p>
                    Los puntos de RentAr Rewards se otorgan a razón de 20 puntos por día
                    alquilado, equivalen a $100 cada uno y pueden cubrir como máximo la
                    mitad del valor de una reserva. No son canjeables por dinero.
                </p>

            </article>
            <a href="../07 - CRUD CATALOGO (alvarez)/index.php" class="back-btn">
                Volver al inicio
            </a>
        </section>
    </main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

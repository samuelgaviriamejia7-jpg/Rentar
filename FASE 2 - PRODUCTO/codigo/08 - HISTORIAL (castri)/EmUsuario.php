<?php
$base = '../';
$titulo = 'Manual para el usuario';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<article id="RentAr">
    <header>
        <h2>Manual de uso para el usuario</h2>
        <p><a href="empresas.php">&larr; Volver a Empresas</a></p>
    </header>

    <section class="manual-seccion">
        <h3>1. Antes de empezar</h3>
        <p>Bienvenido a RentAr.</p>
        <ul>
            <li>Debes ser mayor de edad y tener tu documento de identidad a la mano.</li>
            <li>Para conducir necesitas licencia vigente. Te la pueden pedir al momento de recoger el vehículo.</li>
            <li>Lee estas indicaciones antes de reservar: te evitan la mayoría de los inconvenientes.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>2. Crear tu cuenta</h3>
        <ul>
            <li>Entra a Crear cuenta y elige el tipo <strong>Arrendatario</strong> si vas a alquilar vehículos. Si además quieres publicar el tuyo, puedes cambiar a arrendador después desde tu perfil.</li>
            <li>Completa nombre, documento, teléfono y correo. La foto de perfil es opcional.</li>
            <li>Tu cuenta queda <strong>pendiente de verificación</strong>. Un administrador revisa tus datos y la habilita.</li>
            <li>Mientras no estés verificado puedes navegar el catálogo y guardar favoritos, pero no reservar. Si intentas reservar guardamos tu solicitud y la completas apenas te verifiquen.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>3. Buscar un vehículo</h3>
        <ul>
            <li>En el Catálogo puedes filtrar por tipo (carro o moto), precio máximo, transmisión automática, híbridos o precio más bajo.</li>
            <li>El filtro <strong>Disponible entre</strong> te muestra solo los vehículos libres en las fechas que necesitas.</li>
            <li>Cada tarjeta muestra la calificación promedio que le han dado otros usuarios.</li>
            <li>El corazón guarda el vehículo en Favoritos para decidir después.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>4. Reservar</h3>
        <ul>
            <li>Al entrar a un vehículo debes completar cuatro datos: sede de recogida, sede de devolución, fecha de renta y fecha de entrega. Los cuatro son obligatorios.</li>
            <li>El total se calcula solo: precio por día por la cantidad de días.</li>
            <li>Al confirmar, tu comprobante se descarga automáticamente en formato JPG. Guárdalo, te lo piden al recoger el vehículo.</li>
            <li>También puedes volver a descargarlo cuando quieras desde Mis reservas.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>5. Pagar</h3>
        <ul>
            <li>Desde Mis reservas entra a <strong>Pagar</strong>. Puedes elegir tarjeta, PSE o efectivo al recoger.</li>
            <li>Si pagas con tarjeta, RentAr solo guarda los últimos cuatro dígitos. Nunca almacenamos el número completo.</li>
            <li>Cuando el pago queda aprobado recibes un número de referencia y la reserva aparece marcada como <strong>Pagada</strong>.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>6. Cambiar o cancelar</h3>
        <ul>
            <li>Mientras la reserva esté confirmada puedes cambiar fechas o sede desde el botón Cambiar.</li>
            <li>Cada modificación queda registrada en el Historial de la reserva, con el valor anterior y el nuevo.</li>
            <li>Cancelar libera el vehículo para otros usuarios.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>7. Puntos RentAr Rewards</h3>
        <ul>
            <li>Ganas 20 puntos por cada día que alquilas. Cada punto vale $100 de descuento.</li>
            <li>Los usas al pagar marcando la casilla <strong>Usar mis puntos</strong>.</li>
            <li>Los puntos cubren hasta la mitad del total, nunca la reserva completa.</li>
            <li>En la sección Puntos ves de dónde salió cada uno y en qué los gastaste.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>8. Al recoger y al devolver</h3>
        <ul>
            <li>Revisa el vehículo antes de salir: carrocería, llantas, luces y nivel de combustible.</li>
            <li>Confirma que los papeles del vehículo estén al día y a la mano.</li>
            <li>Devuélvelo en la fecha acordada y en las mismas condiciones.</li>
            <li>Cuando la reserva termina puedes <strong>calificar</strong> el vehículo de 1 a 5 estrellas y dejar un comentario.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>9. Preguntas frecuentes</h3>
        <ul>
            <li><strong>¿Puedo eliminar mi cuenta?</strong> Sí, desde Editar perfil. Si nunca reservaste se borra por completo; si ya tienes historial la cuenta se cierra y esos registros se conservan.</li>
            <li><strong>¿Dónde veo los avisos?</strong> En la campana del menú: ahí llegan la verificación de tu cuenta y las novedades de tus reservas.</li>
            <li>Cualquier otra duda la atendemos en nuestro correo.</li>
        </ul>
    </section>
</article>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

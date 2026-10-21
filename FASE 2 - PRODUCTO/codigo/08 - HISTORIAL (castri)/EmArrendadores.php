<?php
$base = '../';
$titulo = 'Manual para arrendadores';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<article id="RentAr">
    <header>
        <h2>Manual de uso para arrendadores</h2>
        <p><a href="empresas.php">&larr; Volver a Empresas</a></p>
    </header>

    <section class="manual-seccion">
        <h3>1. Qué significa ser arrendador</h3>
        <p>Bienvenido a RentAr.</p>
        <ul>
            <li>Como arrendador publicas tu vehículo y generas ingresos con el tiempo en que no lo usas.</li>
            <li>Tú sigues siendo el dueño. RentAr gestiona la publicación, la reserva, el cobro y el respaldo.</li>
            <li>Puedes ser arrendador y arrendatario con la misma cuenta: el tipo se cambia desde Editar perfil cuando quieras.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>2. Requisitos para publicar</h3>
        <ul>
            <li>Tener la cuenta verificada por un administrador.</li>
            <li>El vehículo debe estar a tu nombre, con documentación al día y sin comparendos pendientes.</li>
            <li>La información que publiques debe ser real y la debes poder sustentar: placa, marca, modelo, año y características.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>3. Publicar tu vehículo</h3>
        <ul>
            <li>Entra a Publicaciones y luego a <strong>Publicar mi vehículo</strong>.</li>
            <li>Completa los datos, elige si es carro o moto, define el precio por día y sube una foto (JPG, PNG o WEBP de máximo 2 MB).</li>
            <li>La publicación queda en estado <strong>Pendiente</strong> hasta que un administrador la revise.</li>
            <li>Si se aprueba, entra al catálogo y te llega una notificación. Si se rechaza, también te avisamos y puedes ver el motivo.</li>
            <li>La placa es única: no puedes publicar dos veces el mismo vehículo.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>4. Seguirle el rastro a tu vehículo</h3>
        <ul>
            <li>En Mis publicaciones ves el estado de cada vehículo: disponible en sede, en alquiler, en mantenimiento o inactivo.</li>
            <li>Cuando está alquilado ves hasta cuándo y en qué sede se devuelve.</li>
            <li>Cada reserva sobre tu vehículo te llega como notificación en la campana del menú.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>5. Ingresos y comisión</h3>
        <ul>
            <li>RentAr retiene el <strong>25%</strong> de cada alquiler. Ese porcentaje cubre la plataforma, la verificación de usuarios y el soporte.</li>
            <li>En la sección Ingresos ves el bruto, la comisión y el neto de los últimos seis meses, además del desempeño de cada vehículo.</li>
            <li>Entrega el vehículo solo cuando el arrendatario te muestre su comprobante, en papel o desde el celular.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>6. Vender con opción de compra</h3>
        <ul>
            <li>Puedes marcar un vehículo como disponible para compra y definir qué porcentaje de cada alquiler se abona a su precio.</li>
            <li>El arrendatario ve cuánto lleva abonado y cuánto le falta.</li>
            <li>Cuando completa el valor, el vehículo sale del catálogo y ustedes coordinan el traspaso directamente.</li>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>7. Cómo comprobamos tu información</h3>
        <ul>
            <li>Antes de aprobar tu publicación verificamos que el vehículo y tus documentos estén en regla.
                Puedes adelantar esa consulta en el sitio oficial del Registro Único Nacional de Tránsito (RUNT)
                y enviarnos el soporte a nuestro correo oficial.
            </li>
            <p><a href="https://mintransporte.gov.co/publicaciones/222/servicios-y-consultas-en-linea/" class="btn-small" target="_blank" rel="noopener noreferrer">Ir a Registro Único Nacional de Tránsito (RUNT)</a></p>
        </ul>
    </section>

    <section class="manual-seccion">
        <h3>8. Preguntas frecuentes</h3>
        <ul>
            <li><strong>¿Puedo retirar un vehículo?</strong> Sí, pasándolo a inactivo desde Mis publicaciones. No puedes retirarlo si tiene una reserva en curso.</li>
            <li><strong>¿Qué pasa si elimino mi cuenta?</strong> Tus vehículos salen del catálogo. Si alguien tiene una reserva activa sobre uno de ellos, debes esperar a que termine.</li>
            <li>Cualquier otra duda la atendemos en nuestro correo.</li>
        </ul>
    </section>
</article>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

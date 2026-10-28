<?php
$base = '../';
$titulo = 'Recogida | RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="page-content">
    <h1 class="page-title">Selecciona el lugar de recogida</h1>

    <div class="layout">
        <div class="locations-list" role="radiogroup" aria-label="Puntos de recogida disponibles">
            <div class="location-card selected" role="radio" aria-checked="true" tabindex="0" onclick="selectCard(this)" onkeydown="handleCardKey(event, this)" data-location="robledo">
                <div class="card-top">
                    <div class="card-icon">
                        <img src="../img/garaje.png" alt="Icono de garaje">
                    </div>
                    <div class="card-name">Robledo, parque principal</div>
                </div>

                <div class="badges">
                    <span class="badge">24 horas</span>
                    <span class="badge">Lunes a domingo</span>
                </div>

                <div class="horarios-title">Horarios</div>
                <div class="horario-row">
                    <span class="horario-label">Lunes a domingo</span>
                    <span class="horario-value">24 horas</span>
                </div>
                <div class="horario-row">
                    <span class="horario-label">Festivos</span>
                    <span class="horario-value">7:00 a.m. – 4:00 p.m.</span>
                </div>
            </div>

            <div class="location-card" role="radio" aria-checked="false" tabindex="0" onclick="selectCard(this)" onkeydown="handleCardKey(event, this)" data-location="aeropuerto">
                <div class="card-top">
                    <div class="card-icon">
                        <img src="../img/avion.png" alt="Icono de avión">
                    </div>
                    <div class="card-name">Aeropuerto Olaya Herrera</div>
                </div>

                <div class="badges">
                    <span class="badge">24 horas</span>
                    <span class="badge">Lunes a domingo</span>
                </div>

                <div class="horarios-title">Horarios</div>
                <div class="horario-row">
                    <span class="horario-label">Lunes a domingo</span>
                    <span class="horario-value">24 horas</span>
                </div>
                <div class="horario-row">
                    <span class="horario-label">Festivos</span>
                    <span class="horario-value">7:00 a.m. – 4:00 p.m.</span>
                </div>
            </div>
        </div>

        <div class="image-box">
            <iframe
                id="mapaSede"
                src="https://www.google.com/maps?q=Parque+Robledo,+Medell%C3%ADn&hl=es&z=16&output=embed"
                width="100%"
                height="450"
                style="border:0;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Ubicación del punto de recogida en el mapa">
            </iframe>
        </div>
    </div>

    <div class="confirm-bar">
        <button type="button" class="btn-confirmar" onclick="confirmarSeleccion()">Confirmar punto de recogida</button>
    </div>

    <div class="qr-registro-card">
        <div class="qr-registro-texto">
            <h3>¿Aún no tienes cuenta?</h3>
            <p>
                Escanea este código QR con la cámara de tu celular para registrarte
                en RentAr y poder reclamar tu vehículo en cualquiera de nuestras sedes.
            </p>
        </div>
        <div class="qr-registro-caja">
            <div id="qr-registro"></div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const mapasSedes = {
        robledo:    'https://www.google.com/maps?q=Parque+Robledo,+Medell%C3%ADn&hl=es&z=16&output=embed',
        aeropuerto: 'https://www.google.com/maps?q=Aeropuerto+Olaya+Herrera,+Medell%C3%ADn&hl=es&z=15&output=embed'
    };

    function selectCard(card) {
        document.querySelectorAll('.location-card').forEach(c => {
            c.classList.remove('selected');
            c.setAttribute('aria-checked', 'false');
        });
        card.classList.add('selected');
        card.setAttribute('aria-checked', 'true');

        const mapa = document.getElementById('mapaSede');
        const url = mapasSedes[card.dataset.location];
        if (mapa && url && mapa.getAttribute('src') !== url) {
            mapa.setAttribute('src', url);
        }
    }

    function handleCardKey(event, card) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            selectCard(card);
        }
    }

    function confirmarSeleccion() {
        const seleccionada = document.querySelector('.location-card.selected');
        const nombre = seleccionada.querySelector('.card-name').textContent;
        alert('Punto de recogida confirmado: ' + nombre);
    }

    const urlRegistro = window.location.origin
        + window.location.pathname.replace(/recogida\.php$/, '')
        + 'registro.php';

    new QRCode(document.getElementById('qr-registro'), {
        text: urlRegistro,
        width: 140,
        height: 140,
        colorDark: '#101C2E',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
</script>

<?php
$base = '../';
$titulo = 'RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero">

    <section class="hero-text">

        <h1>
            No solo alquilas<br>
            <span class="highlight">DESBLOQUEAS</span><br>
            El camino
        </h1>

        <p>
            Vehículos premium para cada momento.
            Conduce con comodidad, seguridad y estilo.
        </p>

        <div class="hero-cta">
            <a href="catalogo.php" class="btn-main">
                AGENDA TU VEHÍCULO
            </a>
        </div>

    </section>

    <section class="hero-visual">

        <div class="hero-carrusel" id="heroCarrusel">

            <div class="hero-glow"></div>

            <div class="hero-slide activa">
                <img src="../img/hero-carro1.png" alt="Toyota Corolla Cross">
                <span class="hero-slide-nombre">Toyota Corolla Cross</span>
            </div>
            <div class="hero-slide">
                <img src="../img/hero-audi.png" alt="Chevrolet Camaro ZL1">
                <span class="hero-slide-nombre">Chevrolet Camaro ZL1</span>
            </div>
            <div class="hero-slide">
                <img src="../img/hero-cupra.png" alt="Yamaha NMAX 155">
                <span class="hero-slide-nombre">Yamaha NMAX 155</span>
            </div>
            <div class="hero-slide">
                <img src="../img/hero-cross.png" alt="Renault Kwid Outsider">
                <span class="hero-slide-nombre">Renault Kwid Outsider</span>
            </div>

            <div class="hero-puntos" id="heroPuntos"></div>

        </div>

    </section>

</main>

<script>
(function () {
    const slides = document.querySelectorAll('.hero-slide');
    const puntos = document.getElementById('heroPuntos');
    if (!slides.length) return;

    let actual = 0;
    let temporizador;

    slides.forEach((_, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'hero-punto' + (i === 0 ? ' activo' : '');
        b.setAttribute('aria-label', 'Ver vehículo ' + (i + 1));
        b.onclick = () => { mostrar(i); reiniciar(); };
        puntos.appendChild(b);
    });

    function mostrar(i) {
        slides[actual].classList.remove('activa');
        puntos.children[actual].classList.remove('activo');
        actual = i;
        slides[actual].classList.add('activa');
        puntos.children[actual].classList.add('activo');
    }

    function siguiente() {
        mostrar((actual + 1) % slides.length);
    }

    function reiniciar() {
        clearInterval(temporizador);
        temporizador = setInterval(siguiente, 4000);
    }

    reiniciar();

    const cont = document.getElementById('heroCarrusel');
    cont.addEventListener('mouseenter', () => clearInterval(temporizador));
    cont.addEventListener('mouseleave', reiniciar);
})();
</script>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

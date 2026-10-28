<?php
$base = '../';
$titulo = 'Empresas';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<article id="RentAr">
    <header>
        <h2>Manuales de uso para todos nuestros usuarios.</h2>
    </header>

    <section class="articulos">
        <ul>
            <li><h3>Mayor seguridad y reducción de accidentes.</h3></li>
            <li><h3>Cumple cada una de nuestras condiciones.</h3></li>
            <li><h3>Ofrecemos atención personalizada.</h3></li>
        </ul>
    </section>

    <div class="botons">
        <a href="EmUsuario.php" class="btn-Ren">
            <img src="../img/usuario.1-removebg-preview.png" alt="Usuario">
            <span>Para el usuario</span>
        </a>
        <a href="EmArrendadores.php" class="btn-T">
            <img src="../img/viaje.carro-removebg-preview.png" alt="Viaje">
            <span>Para arrendadores</span>
        </a>
        <a href="EmAgencias.php" class="btn-Ar">
            <img src="../img/seguro-removebg-preview.png" alt="Seguro">
            <span>Seguros para nuestros usuarios</span>
        </a>
    </div>
</article>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

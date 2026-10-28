    <footer class="footer">
        <div class="footer-container">
            <div class="footer-row">
                <div class="footer-col">
                    <h4>RentAr</h4>
                    <p>
                        Conectamos a quien tiene un vehículo parado con quien necesita uno.
                        Usuarios verificados, vehículos aprobados y cada reserva con su comprobante,
                        para que alquilar entre particulares sea seguro.
                    </p>
                </div>
                <div class="footer-col">
                    <h4>Navegación</h4>
                    <ul>
                        <li><a href="../10 - PAGINAS PUBLICAS (samuel)/quienes.php">¿Quienes somos?</a></li>
                        <li><a href="../10 - PAGINAS PUBLICAS (samuel)/trabajo.php">Trabaja con nosotros</a></li>
                        <li><a href="../10 - PAGINAS PUBLICAS (samuel)/terminos.php">Términos y Condiciones</a></li>
                        <li><a href="../10 - PAGINAS PUBLICAS (samuel)/privacidad.php">Política de Privacidad</a></li>
                        <li><a href="../10 - PAGINAS PUBLICAS (samuel)/creadores.php">Creadores</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contacto</h4>
                    <ul>
                        <li>Medellín, Colombia</li>
                        <li>+57 333 2521594</li>
                        <li>rentarcolombia1@gmail.com</li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Redes</h4>
                    <div class="links">
                        <a href="https://wa.me/573332521594?text=Buen%20d%C3%ADa,%20me%20interesa%20saber%20m%C3%A1s%20sobre%20RentAr.">
                            <img src="<?= $base ?>img/whatsapp.png" alt="WhatsApp">
                        </a>
                        <a href="https://www.instagram.com/rentar_colombia/">
                            <img src="<?= $base ?>img/instagram.png" alt="Instagram">
                        </a>
                        <a href="https://www.tiktok.com/@rent_ar">
                            <img src="<?= $base ?>img/tiktok.png" alt="TikTok">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="pyp">
            <p>Todos los derechos reservados</p>
        </div>
        <div class="footer-bottom">
            <p>© 2026 RentAr</p>
        </div>
    </footer>

</body>
</html>

<script>
function cambiarTema() {
    const raiz = document.documentElement;
    const nuevo = raiz.dataset.tema === 'claro' ? 'oscuro' : 'claro';
    raiz.dataset.tema = nuevo;
    localStorage.setItem('tema', nuevo);
}
</script>

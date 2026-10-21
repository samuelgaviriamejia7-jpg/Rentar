<?php
$base = '../';
$titulo = 'Creadores | RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main>

        <section class="creadores">

            <div class="titulo-creadores">

                <h1>Creadores de RentAr</h1>

                <p>
                    Conoce el equipo encargado del diseño,
                    desarrollo y crecimiento de la plataforma.
                </p>

            </div>

            <div class="contenedor-creadores">

                <div class="card-creador">

                    <div class="foto-creador">
                        <img src="../img/creadores/juan-alvarez.jpg" alt="Juan José Alvarez Cardona">
                    </div>

                    <h2>Juan José Alvarez Cardona</h2>

                    <span>Estructuras y coordinación</span>

                    <p>
                        Encargado de la estructura del proyecto y de coordinar
                        al equipo de RentAr Colombia.
                    </p>

                </div>

                <div class="card-creador">
                    <div class="foto-creador">
                        <img src="../img/creadores/juan-castrillon.jpg" alt="Juan Jose Castrillon Valencia">
                    </div>

                    <h2>Juan Jose Castrillon Valencia</h2>

                    <span>Manuales e información legal</span>

                    <p>
                        Encargado de los manuales y de la información legal
                        de la aplicación.
                    </p>
                </div>

                <div class="card-creador">

                    <div class="foto-creador">
                        <img src="../img/creadores/samuel-gaviria.jpg" alt="Samuel Gaviria Mejía">
                    </div>

                    <h2>Samuel Gaviria Mejía</h2>

                    <span>Diseñador de bases de datos</span>

                    <p>
                        Encargado del diseño de la base de datos de RentAr
                        Colombia.
                    </p>

                </div>
                <div class="card-creador">

                    <div class="foto-creador">
                        <img src="../img/creadores/saul-benitez.jpg" alt="Saul Benitez">
                    </div>

                    <h2>Saul Benitez</h2>

                    <span>Gestor de sedes</span>

                    <p>
                        Encargado de las sedes de recogida y devolución: define los puntos
                        de entrega y verifica el estado de los vehículos al entregarlos y al recibirlos.
                    </p>

                </div>

            </div>

        </section>

    </main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

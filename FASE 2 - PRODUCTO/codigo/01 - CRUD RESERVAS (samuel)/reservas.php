<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';

$idVehiculo = (int)($_GET['id'] ?? 0);

$vehiculo = null;
if ($idVehiculo) {
    $st = $pdo->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = ? AND estado = 'disponible'");
    $st->execute([$idVehiculo]);
    $vehiculo = $st->fetch();
}

$sedes = $pdo->query("SELECT id_sede, nombre FROM sedes WHERE activa = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVehiculo = (int)($_POST['id_vehiculo'] ?? 0);
    $recogida   = (int)($_POST['id_sede_recogida'] ?? 0);
    $devolucion = (int)($_POST['id_sede_devolucion'] ?? 0);
    $inicio     = $_POST['fecha_inicio'] ?? '';
    $fin        = $_POST['fecha_fin'] ?? '';

    if (!haySesion()) {
        guardarReservaPendiente($idVehiculo, $recogida, $devolucion, $inicio, $fin);
        mensaje('Inicia sesión o crea una cuenta para completar tu reserva.', 'error');
        ir('../09 - REGISTRO Y LOGIN (saul)/login.php');
    }

    $volver = "reservas.php?id=$idVehiculo";

    if (!usuarioVerificado()) {
        guardarReservaPendiente($idVehiculo, $recogida, $devolucion, $inicio, $fin);
        mensaje('Tu cuenta está pendiente de verificación por un administrador. Guardamos tu solicitud y podrás completarla cuando te verifiquen.', 'error');
        ir($volver);
    }

    if (!$idVehiculo || !$recogida || !$devolucion || !$inicio || !$fin) {
        mensaje('Completa todos los campos.', 'error'); ir($volver);
    }
    if ($fin < $inicio) {
        mensaje('La fecha de entrega no puede ser anterior a la de renta.', 'error'); ir($volver);
    }
    if ($inicio < date('Y-m-d')) {
        mensaje('No puedes reservar en una fecha pasada.', 'error'); ir($volver);
    }
    if (!vehiculoDisponible($pdo, $idVehiculo, $inicio, $fin)) {
        mensaje('Ese vehículo ya está reservado en esas fechas.', 'error'); ir($volver);
    }

    $stP = $pdo->prepare("SELECT precio_dia FROM vehiculos WHERE id_vehiculo = ? AND estado = 'disponible'");
    $stP->execute([$idVehiculo]);
    $precioDia = $stP->fetchColumn();

    if (!$precioDia) { mensaje('Vehículo no disponible.', 'error'); ir($volver); }

    $dias  = max((new DateTime($inicio))->diff(new DateTime($fin))->days, 1);
    $total = $precioDia * $dias;

    $pdo->prepare(
        "INSERT INTO reservas
         (codigo, id_usuario, id_vehiculo, id_sede_recogida, id_sede_devolucion,
          fecha_inicio, fecha_fin, precio_dia, dias, total, estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmada')"
    )->execute([generarCodigoReserva(), usuarioId(), $idVehiculo, $recogida,
                $devolucion, $inicio, $fin, $precioDia, $dias, $total]);

    mensaje('Reserva confirmada. Tu comprobante se está descargando.');
    ir('mis-reservas.php?descargar=' . $pdo->lastInsertId());
}

$titulo = 'Reservas';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">

    <section class="hero-info">

        <article class="hero-title">
            <h1>Renta con libertad</h1>
            <p>Reserva fácil, rápido y sin complicaciones</p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (haySesion() && !usuarioVerificado()): ?>
            <div class="aviso aviso-error">
                Tu cuenta todavía no ha sido verificada por un administrador, así que aún no puedes confirmar reservas.
            </div>
        <?php endif; ?>

        <?php if ($vehiculo && $vehiculo['en_venta'] && $vehiculo['precio_venta']): ?>
            <div class="aviso-venta">
                <strong>Este vehículo está en venta por <?= pesos($vehiculo['precio_venta']) ?>.</strong>
                El <?= (int)$vehiculo['porcentaje_abono'] ?>% de lo que pagues por cada alquiler
                queda abonado a su compra.
            </div>
        <?php endif; ?>

        <?php if ($vehiculo): ?>
        <article class="formulario vehiculo-resumen" style="margin-bottom:10px;">
            <h2><?= e($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h2>

            <p class="vehiculo-precio"><?= pesos($vehiculo['precio_dia']) ?> <span>/ día</span></p>

            <ul class="vehiculo-specs">
                <?php if (!empty($vehiculo['anio'])): ?>
                    <li><?= e($vehiculo['anio']) ?></li>
                <?php endif; ?>
                <?php if (!empty($vehiculo['transmision'])): ?>
                    <li><?= e($vehiculo['transmision']) ?></li>
                <?php endif; ?>
                <?php if (!empty($vehiculo['combustible'])): ?>
                    <li><?= e($vehiculo['combustible']) ?></li>
                <?php endif; ?>
                <?php if (!empty($vehiculo['pasajeros'])): ?>
                    <li><?= e($vehiculo['pasajeros']) ?> pasajeros</li>
                <?php endif; ?>
                <?php if (!empty($vehiculo['puertas'])): ?>
                    <li><?= e($vehiculo['puertas']) ?> puertas</li>
                <?php endif; ?>
                <?php if (!empty($vehiculo['kilometraje_ilimitado'])): ?>
                    <li>Kilometraje ilimitado</li>
                <?php endif; ?>
            </ul>

            <?php if (!empty($vehiculo['descripcion'])): ?>
                <p class="vehiculo-descripcion"><?= e($vehiculo['descripcion']) ?></p>
            <?php endif; ?>

            <?php
            $cal = calificacionVehiculo($pdo, $idVehiculo);
            $resenas = resenasVehiculo($pdo, $idVehiculo);
            ?>

            <?php if ($cal['total']): ?>
                <details class="resenas">
                    <summary>
                        <span class="resenas-estrellas"><?= estrellas($cal['promedio']) ?></span>
                        <span class="resenas-promedio"><?= $cal['promedio'] ?></span>
                        <span class="resenas-abrir">Ver reseñas (<?= (int)$cal['total'] ?>)</span>
                    </summary>

                    <ul class="resenas-lista">
                        <?php foreach ($resenas as $re): ?>
                            <li>
                                <div class="resena-cabecera">
                                    <strong><?= e($re['nombre']) ?></strong>
                                    <span class="resena-estrellas"><?= estrellas($re['estrellas']) ?></span>
                                    <span class="resena-fecha"><?= date('d/m/Y', strtotime($re['fecha'])) ?></span>
                                </div>
                                <?php if ($re['comentario']): ?>
                                    <p><?= e($re['comentario']) ?></p>
                                <?php else: ?>
                                    <p class="resena-sin-texto">Calificó sin dejar comentario.</p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php else: ?>
                <p class="resenas-vacio">Este vehículo todavía no tiene reseñas.</p>
            <?php endif; ?>
        </article>
        <?php endif; ?>

        <form method="post">

        <input type="hidden" name="id_vehiculo" value="<?= $idVehiculo ?>">

        <section class="booking-panel">
             <div class="booking-family">

    <a href="#" class="booking-card" data-paso="form-renta" onclick="mostrarFormulario('form-renta'); return false;">
        <img src="../img/ubicacion.png" alt="ubicacion">
        <section><span>Renta vehículo en</span><h3>Seleccionar sede</h3></section>
        <span class="booking-card-check">✓</span>
    </a>

    <a href="#" class="booking-card" data-paso="form-devolucion" onclick="mostrarFormulario('form-devolucion'); return false;">
        <img src="../img/ubicacion.png" alt="ubicacion">
        <section><span>Devolver vehículo en</span><h3>Seleccionar sede</h3></section>
        <span class="booking-card-check">✓</span>
    </a>

    <a href="#" class="booking-card" data-paso="form-fecha-renta" onclick="mostrarFormulario('form-fecha-renta'); return false;">
        <img src="../img/OIP.png" alt="calendario">
        <section><span>Fecha de renta</span><h3>Seleccionar fecha</h3></section>
        <span class="booking-card-check">✓</span>
    </a>

    <a href="#" class="booking-card" data-paso="form-fecha-entrega" onclick="mostrarFormulario('form-fecha-entrega'); return false;">
        <img src="../img/OIP.png" alt="calendario">
        <section><span>Fecha de entrega</span><h3>Seleccionar fecha</h3></section>
        <span class="booking-card-check">✓</span>
    </a>

</div>
</section>

        <section class="formularios">

    <article id="form-renta" class="paso">
        <h2>seleccionar sede de renta</h2>
        <select name="id_sede_recogida" id="campo-recogida" required>
            <option value="" selected disabled>seleciona una sede</option>
            <?php foreach ($sedes as $s): ?>
                <option value="<?= $s['id_sede'] ?>"><?= e($s['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </article>

    <article id="form-devolucion" class="paso">
        <h2>seleccionar sede de devolución</h2>
        <select name="id_sede_devolucion" id="campo-devolucion" required>
            <option value="" selected disabled>seleciona una sede</option>
            <?php foreach ($sedes as $s): ?>
                <option value="<?= $s['id_sede'] ?>"><?= e($s['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </article>

    <article id="form-fecha-renta" class="paso">
        <h2>selecciona fecha de renta</h2>
        <input type="date" name="fecha_inicio" id="campo-fecha-inicio" min="<?= date('Y-m-d') ?>" required>
    </article>

    <article id="form-fecha-entrega" class="paso">
        <h2>selecciona fecha de entrega</h2>
        <input type="date" name="fecha_fin" id="campo-fecha-fin" min="<?= date('Y-m-d') ?>" required>
    </article>

</section>

        <section class="car-banner">
            <figure class="car-image">
                <img src="../<?= e($vehiculo['imagen'] ?? 'img/carro.png') ?>" alt="Vehículo">
            </figure>
            <button type="submit" class="reserve-btn">RESERVAR</button>
        </section>

        </form>

        <nav class="extra-links">
            <a href="../10 - PAGINAS PUBLICAS (samuel)/terminos.php">Términos de uso</a>
            <a href="../10 - PAGINAS PUBLICAS (samuel)/rentar+.php">Suscripción RentAr+</a>
        </nav>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<script>
const ordenPasos = ['form-renta', 'form-devolucion', 'form-fecha-renta', 'form-fecha-entrega'];

function mostrarFormulario(id){
    document.querySelectorAll('.formularios .paso').forEach(paso => {
        paso.classList.remove('activo');
    });
    document.getElementById(id).classList.add('activo');
}

function marcarCompleta(idPaso, texto) {
    const tarjeta = document.querySelector(`.booking-card[data-paso="${idPaso}"]`);
    if (!tarjeta) return;
    tarjeta.querySelector('h3').textContent = texto;
    tarjeta.classList.add('completo');
}

function avanzar(idPasoActual) {
    const i = ordenPasos.indexOf(idPasoActual);
    if (i > -1 && i < ordenPasos.length - 1) {
        mostrarFormulario(ordenPasos[i + 1]);
    }
}

function formatearFecha(valorISO) {
    const [anio, mes, dia] = valorISO.split('-');
    return `${dia}/${mes}/${anio}`;
}

const campoRecogida   = document.getElementById('campo-recogida');
const campoDevolucion = document.getElementById('campo-devolucion');
const campoInicio     = document.getElementById('campo-fecha-inicio');
const campoFin        = document.getElementById('campo-fecha-fin');

campoRecogida.addEventListener('change', function () {
    if (!this.value) return;
    marcarCompleta('form-renta', this.options[this.selectedIndex].text);
    avanzar('form-renta');
});

campoDevolucion.addEventListener('change', function () {
    if (!this.value) return;
    marcarCompleta('form-devolucion', this.options[this.selectedIndex].text);
    avanzar('form-devolucion');
});

campoInicio.addEventListener('change', function () {
    if (!this.value) return;
    marcarCompleta('form-fecha-renta', formatearFecha(this.value));
    campoFin.min = this.value;
    if (campoFin.value && campoFin.value < this.value) campoFin.value = '';
    avanzar('form-fecha-renta');
});

campoFin.addEventListener('change', function () {
    if (!this.value) return;
    marcarCompleta('form-fecha-entrega', formatearFecha(this.value));
    document.querySelectorAll('.formularios .paso').forEach(p => p.classList.remove('activo'));
});

document.querySelector('form').addEventListener('submit', function (evento) {
    const campos = [
        ['form-renta', campoRecogida],
        ['form-devolucion', campoDevolucion],
        ['form-fecha-renta', campoInicio],
        ['form-fecha-fin', campoFin],
    ];
    for (const [idPaso, campo] of campos) {
        if (!campo.value) {
            evento.preventDefault();
            mostrarFormulario(idPaso === 'form-fecha-fin' ? 'form-fecha-entrega' : idPaso);
            campo.reportValidity();
            return;
        }
    }
});
</script>

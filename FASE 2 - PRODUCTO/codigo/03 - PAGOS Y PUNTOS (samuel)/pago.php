<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
require_once __DIR__ . '/../02 - CRUD VEHICULOS (samuel)/vehiculos.php';
requiereLogin();

$tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? '';
$tipo = in_array($tipo, ['reserva', 'compra'], true) ? $tipo : 'plus';
$idReserva = (int)($_GET['id'] ?? $_POST['id_reserva'] ?? 0);
$reserva = null;

if ($tipo === 'reserva') {
    $st = $pdo->prepare(
        "SELECT r.id_reserva, r.codigo, r.total, r.estado, r.dias, v.id_admin AS dueno,
                CONCAT(v.marca,' ',v.modelo) AS vehiculo
         FROM reservas r
         JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
         WHERE r.id_reserva = ? AND r.id_usuario = ?"
    );
    $st->execute([$idReserva, usuarioId()]);
    $reserva = $st->fetch();

    if (!$reserva) {
        mensaje('Reserva no encontrada.', 'error');
        ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php');
    }
    if ($reserva['estado'] === 'cancelada') {
        mensaje('Esa reserva está cancelada, no se puede pagar.', 'error');
        ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php');
    }

    $concepto = 'Reserva ' . $reserva['codigo'] . ' — ' . $reserva['vehiculo'];
    $monto    = (float)$reserva['total'];
} elseif ($tipo === 'compra') {
    $idVehiculo = (int)($_GET['id'] ?? $_POST['id_reserva'] ?? 0);
    $abono = abonoDelUsuario($pdo, $idVehiculo);

    $st = $pdo->prepare("SELECT marca, modelo FROM vehiculos WHERE id_vehiculo = ?");
    $st->execute([$idVehiculo]);
    $veh = $st->fetch();

    if (!$abono || !$veh) {
        mensaje('Ese vehículo no está en venta.', 'error');
        ir('../02 - CRUD VEHICULOS (samuel)/abonos.php');
    }

    $idReserva = $idVehiculo;
    $concepto  = 'Compra de ' . $veh['marca'] . ' ' . $veh['modelo'];
    $monto     = $abono['falta'];

    if ($monto <= 0) {
        mensaje('Ya completaste el valor de este vehículo. Comunícate con el dueño para el traspaso.');
        ir('../02 - CRUD VEHICULOS (samuel)/abonos.php');
    }
} else {
    $concepto = 'Suscripción RentAr+ (1 mes)';
    $monto    = 39900.00;
}

$stPagado = $pdo->prepare(
    "SELECT referencia, fecha_pago, ultimos_digitos
     FROM pagos
     WHERE id_usuario = ? AND estado = 'aprobado' AND concepto = ?
     ORDER BY fecha_pago DESC LIMIT 1"
);
$stPagado->execute([usuarioId(), $concepto]);
$pagoPrevio = $stPagado->fetch();

$puntos = puntosDisponibles($pdo);
$descuentoMaximo = min($puntos * PESOS_POR_PUNTO, $monto / 2);

$metodosValidos = ['tarjeta', 'pse', 'efectivo'];
$errores = [];
$pagoHecho = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo = in_array($_POST['metodo'] ?? '', $metodosValidos, true) ? $_POST['metodo'] : 'tarjeta';
    $ultimos = null;

    if ($metodo === 'tarjeta') {
        $numero = preg_replace('/\D/', '', $_POST['numero'] ?? '');
        $nombre = trim($_POST['nombre_tarjeta'] ?? '');
        $mes    = (int)($_POST['mes'] ?? 0);
        $anio   = (int)($_POST['anio'] ?? 0);
        $cvv    = preg_replace('/\D/', '', $_POST['cvv'] ?? '');

        if (strlen($numero) < 13 || strlen($numero) > 19) $errores[] = 'El número de la tarjeta no es válido.';
        elseif (!numeroTarjetaValido($numero))            $errores[] = 'El número de la tarjeta no pasa la validación.';
        if ($nombre === '')                               $errores[] = 'Escribe el nombre que aparece en la tarjeta.';
        if ($mes < 1 || $mes > 12)                        $errores[] = 'El mes de vencimiento no es válido.';
        if ($anio < (int)date('Y') || $anio > (int)date('Y') + 15) $errores[] = 'El año de vencimiento no es válido.';
        if (!$errores && $anio === (int)date('Y') && $mes < (int)date('n')) $errores[] = 'La tarjeta está vencida.';
        if (strlen($cvv) < 3 || strlen($cvv) > 4)         $errores[] = 'El código de seguridad no es válido.';

        $ultimos = substr($numero, -4);
    }

    $usarPuntos = isset($_POST['usar_puntos']) && $descuentoMaximo > 0;
    $descuento  = $usarPuntos ? floor($descuentoMaximo) : 0;
    $aPagar     = $monto - $descuento;

    if (!$errores) {
        try {
            $referencia = 'PG-' . random_int(1000000, 9999999);
            $pdo->prepare(
                "INSERT INTO pagos (id_usuario, id_reserva, concepto, monto, metodo, ultimos_digitos, referencia, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'aprobado')"
            )->execute([usuarioId(), $reserva['id_reserva'] ?? null, $concepto, $aPagar, $metodo, $ultimos, $referencia]);

            if ($tipo === 'reserva') {
                $dias = max((int)$reserva['dias'], 1);
                registrarPuntos($pdo, usuarioId(), 'Pago de ' . $concepto, $dias * PUNTOS_POR_DIA);

                $comision = $aPagar * COMISION_PLATAFORMA;
                $neto     = $aPagar - $comision;

                crearNotificacion($pdo, $reserva['dueno'],
                    'Te pagaron ' . pesos($neto) . ' por ' . $reserva['vehiculo']
                    . ' (reserva ' . $reserva['codigo'] . '). Ya se descontó la comisión de RentAr.',
                    '03 - PAGOS Y PUNTOS (samuel)/ingresos.php');
            }

            if ($tipo === 'compra') {
                $pdo->prepare("UPDATE vehiculos SET estado = 'inactivo', en_venta = 0 WHERE id_vehiculo = ?")
                    ->execute([$idReserva]);
                $stD = $pdo->prepare("SELECT id_admin FROM vehiculos WHERE id_vehiculo = ?");
                $stD->execute([$idReserva]);
                crearNotificacion($pdo, $stD->fetchColumn(), 'Compraron tu vehículo: ' . $concepto . '.', '02 - CRUD VEHICULOS (samuel)/mis-publicaciones.php');
            }

            if ($descuento > 0) {
                registrarPuntos($pdo, usuarioId(), 'Descuento en ' . $concepto, -(int)round($descuento / PESOS_POR_PUNTO));
            }

            $pagoHecho = [
                'referencia' => $referencia,
                'metodo'     => $metodo,
                'ultimos'    => $ultimos,
                'descuento'  => $descuento,
                'pagado'     => $aPagar,
                'puntos'     => $tipo === 'reserva' ? max((int)$reserva['dias'], 1) * PUNTOS_POR_DIA : 0,
            ];
        } catch (PDOException $ex) {
            $errores[] = 'No se pudo registrar el pago. Intenta de nuevo.';
        }
    }

    if ($errores) mensaje(implode(' ', $errores), 'error');
}

$titulo = 'Pago | RentAr';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">

        <article class="hero-title">
            <h1><?= $pagoHecho ? 'Pago aprobado' : 'Pagar' ?></h1>
            <p><?= $pagoHecho ? 'Guarda tu número de referencia' : 'Revisa el detalle y elige cómo quieres pagar' ?></p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if ($pagoHecho): ?>

            <div class="pago-exito">
                <span class="pago-check"><?= icono('check') ?></span>
                <h2><?= pesos($pagoHecho['pagado']) ?></h2>
                <p><?= e($concepto) ?></p>
                <dl class="pago-datos">
                    <?php if ($pagoHecho['descuento'] > 0): ?>
                        <div><dt>Descuento con puntos</dt><dd>- <?= pesos($pagoHecho['descuento']) ?></dd></div>
                    <?php endif; ?>
                    <div><dt>Referencia</dt><dd><?= e($pagoHecho['referencia']) ?></dd></div>
                    <div><dt>Método</dt><dd><?= e(ucfirst($pagoHecho['metodo'])) ?><?= $pagoHecho['ultimos'] ? ' ····' . e($pagoHecho['ultimos']) : '' ?></dd></div>
                    <div><dt>Fecha</dt><dd><?= date('d/m/Y H:i') ?></dd></div>
                    <?php if ($pagoHecho['puntos'] > 0): ?>
                        <div><dt>Puntos ganados</dt><dd>+ <?= (int)$pagoHecho['puntos'] ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <nav class="extra-links espacio-arriba">
                <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php" class="primario">Mis reservas</a>
                <a href="../07 - CRUD CATALOGO (alvarez)/catalogo.php">Volver al catálogo</a>
            </nav>

        <?php else: ?>

            <?php if ($pagoPrevio): ?>
                <div class="aviso aviso-ok">
                    Ya tienes un pago aprobado para esto el <?= date('d/m/Y', strtotime($pagoPrevio['fecha_pago'])) ?>,
                    referencia <?= e($pagoPrevio['referencia']) ?>.
                </div>
            <?php endif; ?>

            <div class="pago-layout">

                <aside class="pago-resumen">
                    <span class="detalle-etiqueta">Vas a pagar</span>
                    <h2><?= pesos($monto) ?></h2>
                    <p><?= e($concepto) ?></p>
                    <p class="pago-nota">Esta es una pasarela de prueba para el proyecto: no se cobra dinero real ni se guardan números de tarjeta.</p>
                </aside>

                <form method="post" class="pago-form">
                    <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                    <input type="hidden" name="id_reserva" value="<?= (int)$idReserva ?>">

                    <h2>Método de pago</h2>
                    <div class="metodos">
                        <label class="metodo seleccionado">
                            <input type="radio" name="metodo" value="tarjeta" checked>
                            <strong>Tarjeta</strong>
                            <span>Crédito o débito</span>
                        </label>
                        <label class="metodo">
                            <input type="radio" name="metodo" value="pse">
                            <strong>PSE</strong>
                            <span>Débito desde tu banco</span>
                        </label>
                        <label class="metodo">
                            <input type="radio" name="metodo" value="efectivo">
                            <strong>Efectivo</strong>
                            <span>Pagas al recoger</span>
                        </label>
                    </div>

                    <?php if ($descuentoMaximo > 0): ?>
                        <label class="usar-puntos">
                            <input type="checkbox" name="usar_puntos" value="1">
                            <span>
                                <strong>Usar mis puntos</strong>
                                Tienes <?= number_format($puntos, 0, ',', '.') ?> puntos.
                                Aplicaríamos <?= pesos(floor($descuentoMaximo)) ?> de descuento.
                            </span>
                        </label>
                    <?php endif; ?>

                    <div id="nota-pse" class="pago-metodo-nota" style="display:none;">
                        <strong>Débito desde tu banco</strong>
                        Al confirmar te llevamos al portal de tu banco para autorizar el débito.
                        Cuando el banco responde, la reserva queda pagada.
                    </div>

                    <div id="nota-efectivo" class="pago-metodo-nota" style="display:none;">
                        <strong>Pagas al recoger</strong>
                        Tu reserva queda registrada y el valor lo entregas en la sede el día de la recogida.
                        Lleva tu comprobante y tu documento.
                    </div>

                    <div id="datos-tarjeta">
                        <h2>Datos de la tarjeta</h2>
                        <input type="text" name="numero" id="numero" placeholder="Número de la tarjeta"
                               inputmode="numeric" maxlength="23" autocomplete="off">
                        <input type="text" name="nombre_tarjeta" placeholder="Nombre como aparece en la tarjeta" autocomplete="off">
                        <div class="pago-fila">
                            <input type="number" name="mes" placeholder="Mes (MM)" min="1" max="12">
                            <input type="number" name="anio" placeholder="Año (AAAA)" min="<?= date('Y') ?>" max="<?= date('Y') + 15 ?>">
                            <input type="text" name="cvv" placeholder="CVV" inputmode="numeric" maxlength="4" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn-form">PAGAR <?= pesos($monto) ?></button>
                </form>

            </div>

        <?php endif; ?>

    </section>
</main>

<script>
const camposTarjeta = document.getElementById('datos-tarjeta');
const notaPse = document.getElementById('nota-pse');
const notaEfectivo = document.getElementById('nota-efectivo');

document.querySelectorAll('.metodo input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.metodo').forEach(m => m.classList.remove('seleccionado'));
        radio.closest('.metodo').classList.add('seleccionado');
        camposTarjeta.style.display = radio.value === 'tarjeta' ? 'block' : 'none';
        notaPse.style.display = radio.value === 'pse' ? 'block' : 'none';
        notaEfectivo.style.display = radio.value === 'efectivo' ? 'block' : 'none';
    });
});

const campoNumero = document.getElementById('numero');
if (campoNumero) {
    campoNumero.addEventListener('input', () => {
        const digitos = campoNumero.value.replace(/\D/g, '').slice(0, 19);
        campoNumero.value = digitos.replace(/(.{4})/g, '$1 ').trim();
    });
}
</script>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

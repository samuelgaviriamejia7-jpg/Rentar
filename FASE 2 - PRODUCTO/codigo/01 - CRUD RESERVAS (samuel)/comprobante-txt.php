<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$st = $pdo->prepare(
    "SELECT r.*, v.marca, v.modelo, v.imagen,
            s1.nombre AS recogida, s2.nombre AS devolucion
     FROM reservas r
     JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
     JOIN sedes s1 ON s1.id_sede = r.id_sede_recogida
     JOIN sedes s2 ON s2.id_sede = r.id_sede_devolucion
     WHERE r.id_reserva = ? AND r.id_usuario = ?"
);
$st->execute([(int)($_GET['id'] ?? 0), usuarioId()]);
$r = $st->fetch();

if (!$r) ir('mis-reservas.php');

$d = datosComprobante($r, __DIR__ . '/../');

$linea = str_repeat('=', 54);
$guion = str_repeat('-', 54);

function campo($etiqueta, $valor) {
    return str_pad($etiqueta, 18) . $valor . "\r\n";
}

$texto  = $linea . "\r\n";
$texto .= "                    R E N T A R\r\n";
$texto .= "          Comprobante de reserva\r\n";
$texto .= $linea . "\r\n\r\n";
$texto .= campo('Codigo', $d['codigo']);
$texto .= campo('Estado', $d['estado']);
$texto .= "\r\n" . $guion . "\r\n\r\n";
$texto .= campo('Vehiculo', $d['vehiculo']);
$texto .= campo('Recogida', $d['recogida']);
$texto .= campo('Devolucion', $d['devolucion']);
$texto .= campo('Fecha de renta', $d['fechaInicio']);
$texto .= campo('Fecha de entrega', $d['fechaFin']);
$texto .= "\r\n" . $guion . "\r\n\r\n";
$texto .= campo('Precio', $d['lineaPrecio']);
$texto .= campo('Subtotal', $d['subtotal']);
$texto .= campo('TOTAL', $d['total']);
$texto .= "\r\n" . $guion . "\r\n\r\n";
$texto .= $d['reservado'] . "\r\n";
$texto .= "Presenta este comprobante al recoger el vehiculo.\r\n";
$texto .= "RentAr Colombia - Medellin\r\n";
$texto .= "rentarcolombia1@gmail.com\r\n";
$texto .= "\r\n" . $linea . "\r\n";

$texto = str_replace('$', 'COP ', $texto);
$texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

header('Content-Type: text/plain; charset=ISO-8859-1');
header('Content-Disposition: attachment; filename="comprobante-' . $d['codigo'] . '.txt"');
header('Content-Length: ' . strlen($texto));
echo $texto;

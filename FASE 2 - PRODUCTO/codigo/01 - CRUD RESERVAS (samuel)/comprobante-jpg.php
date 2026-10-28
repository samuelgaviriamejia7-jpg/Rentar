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

$regular = __DIR__ . '/../fuentes/Poppins-Regular.ttf';
$negrita = __DIR__ . '/../fuentes/Poppins-Bold.ttf';

if (!function_exists('imagecreatetruecolor')) {
    mensaje('El comprobante en imagen necesita la extensión GD de PHP. Actívala en php.ini y reinicia Apache.', 'error');
    ir('comprobante.php?id=' . $r['id_reserva']);
}

if (!is_readable($regular) || !is_readable($negrita)) {
    mensaje('No se encontraron las tipografías en la carpeta fuentes.', 'error');
    ir('comprobante.php?id=' . $r['id_reserva']);
}

$ancho = 880;
$alto  = 620;
$lienzo = imagecreatetruecolor($ancho, $alto);
imageantialias($lienzo, true);

function color($img, $hex) {
    return imagecolorallocate($img,
        hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
}

function texto($img, $fuente, $tam, $x, $y, $hex, $txt, $alinear = 'izq') {
    if ($alinear !== 'izq') {
        $caja = imagettfbbox($tam, 0, $fuente, $txt);
        $x -= $caja[2] - $caja[0];
    }
    imagettftext($img, $tam, 0, $x, $y, color($img, $hex), $fuente, $txt);
}

function recorta($fuente, $tam, $txt, $limite) {
    if (imagettfbbox($tam, 0, $fuente, $txt)[2] <= $limite) return $txt;
    while (strlen($txt) > 4 && imagettfbbox($tam, 0, $fuente, $txt . '...')[2] > $limite) {
        $txt = mb_substr($txt, 0, mb_strlen($txt) - 1);
    }
    return $txt . '...';
}

$blanco = color($lienzo, '#FFFFFF');
$oscuro = color($lienzo, '#15181C');

imagefilledrectangle($lienzo, 0, 0, $ancho, $alto, $blanco);
imagefilledrectangle($lienzo, 0, 0, $ancho, 108, $oscuro);

texto($lienzo, $negrita, 30, 56, 68, '#FFFFFF', 'RentAr');

$anchoBadge = 200;
imagefilledrectangle($lienzo, $ancho - 56 - $anchoBadge, 38, $ancho - 56, 82, color($lienzo, $d['estadoColor']));
$cajaEstado = imagettfbbox(15, 0, $negrita, $d['estado']);
texto($lienzo, $negrita, 15, $ancho - 56 - ($anchoBadge + $cajaEstado[2] - $cajaEstado[0]) / 2, 66, '#FFFFFF', $d['estado']);

if ($d['imagen'] && is_file($d['imagen'])) {
    $foto = @imagecreatefromstring(file_get_contents($d['imagen']));
    if ($foto) {
        imagecopyresampled($lienzo, $foto, 56, 152, 0, 0, 200, 130,
            imagesx($foto), imagesy($foto));
        imagedestroy($foto);
    }
}

texto($lienzo, $negrita, 26, 292, 196, '#15181C', recorta($negrita, 26, $d['vehiculo'], 480));
texto($lienzo, $regular, 17, 292, 234, '#7A8087', $d['codigo']);

texto($lienzo, $regular, 13, 56, 330, '#7A8087', 'RECOGIDA');
texto($lienzo, $negrita, 18, 56, 364, '#15181C', recorta($negrita, 18, $d['recogida'], 330));
texto($lienzo, $regular, 15, 56, 396, '#7A8087', $d['fechaInicio']);

texto($lienzo, $regular, 13, $ancho - 56, 330, '#7A8087', 'DEVOLUCIÓN', 'der');
texto($lienzo, $negrita, 18, $ancho - 56, 364, '#15181C', recorta($negrita, 18, $d['devolucion'], 330), 'der');
texto($lienzo, $regular, 15, $ancho - 56, 396, '#7A8087', $d['fechaFin'], 'der');

$linea = color($lienzo, '#D8DCE1');
for ($x = 56; $x < $ancho - 56; $x += 16) {
    imagefilledrectangle($lienzo, $x, 440, $x + 8, 441, $linea);
}

texto($lienzo, $regular, 16, 56, 492, '#15181C', $d['lineaPrecio']);
texto($lienzo, $regular, 16, $ancho - 56, 492, '#15181C', $d['subtotal'], 'der');

texto($lienzo, $negrita, 24, 56, 550, '#15181C', 'Total');
texto($lienzo, $negrita, 24, $ancho - 56, 550, '#D92D2D', $d['total'], 'der');

texto($lienzo, $regular, 13, 56, 596, '#9AA0A6', $d['reservado']);

header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="comprobante_' . $d['codigo'] . '.jpg"');
imagejpeg($lienzo, null, 92);
imagedestroy($lienzo);

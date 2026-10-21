<?php
if (session_status() === PHP_SESSION_NONE) session_start();

const TRANSMISIONES = ['Automático', 'Manual'];
const COMBUSTIBLES  = ['Gasolina', 'Diésel', 'Híbrido', 'Eléctrico'];
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/iconos.php';

actualizarEstadosReservas($pdo);

function e($t) { return htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8'); }
function pesos($v) { return '$' . number_format((float)$v, 0, ',', '.'); }
function ir($url) { header("Location: $url"); exit; }

function haySesion() { return isset($_SESSION['id_usuario']); }
function requiereLogin() { if (!haySesion()) ir('../09 - REGISTRO Y LOGIN (saul)/login.php'); }
function usuarioId() { return $_SESSION['id_usuario'] ?? null; }

function esAdmin() { return haySesion() && ($_SESSION['rol'] ?? '') === 'admin'; }
function esArrendador() { return haySesion() && ($_SESSION['rol'] ?? '') === 'arrendador'; }
function esArrendatario() { return haySesion() && ($_SESSION['rol'] ?? '') === 'arrendatario'; }

function usuarioVerificado() {
    return haySesion() && (esAdmin() || (int)($_SESSION['verificado'] ?? 0) === 1);
}

function guardarReservaPendiente($idVehiculo, $recogida, $devolucion, $inicio, $fin) {
    $_SESSION['reserva_pendiente'] = [
        'id_vehiculo'        => $idVehiculo,
        'id_sede_recogida'   => $recogida,
        'id_sede_devolucion' => $devolucion,
        'fecha_inicio'       => $inicio,
        'fecha_fin'          => $fin,
    ];
}

function requiereAdmin() {
    if (!haySesion()) ir('../09 - REGISTRO Y LOGIN (saul)/login.php');
    if (!esAdmin()) {
        mensaje('No tienes permiso para acceder a esa sección.', 'error');
        ir('../07 - CRUD CATALOGO (alvarez)/catalogo.php');
    }
}

function requiereArrendador() {
    if (!haySesion()) ir('../09 - REGISTRO Y LOGIN (saul)/login.php');
    if (!esArrendador() && !esAdmin()) {
        mensaje('Esa sección es solo para cuentas de arrendador.', 'error');
        ir('../07 - CRUD CATALOGO (alvarez)/catalogo.php');
    }
}

function generarCodigoReserva() { return 'AR-' . random_int(1000000, 9999999); }

function mensaje($texto, $tipo = 'ok') {
    $_SESSION['flash'] = ['texto' => $texto, 'tipo' => $tipo];
}

function mostrarMensaje() {
    if (!isset($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="aviso aviso-' . e($f['tipo']) . '">' . e($f['texto']) . '</div>';
}

function vehiculoDisponible(PDO $pdo, $idVehiculo, $inicio, $fin, $excluir = null) {
    $sql = "SELECT COUNT(*) FROM reservas
            WHERE id_vehiculo = :veh AND estado IN ('confirmada','en_curso')
              AND fecha_inicio <= :fin AND fecha_fin >= :inicio";
    $p = ['veh' => $idVehiculo, 'inicio' => $inicio, 'fin' => $fin];
    if ($excluir) { $sql .= " AND id_reserva <> :ex"; $p['ex'] = $excluir; }
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $st->fetchColumn() == 0;
}

function vehiculosPendientes(PDO $pdo) {
    return (int)$pdo->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'pendiente'")->fetchColumn();
}

function usuariosSinVerificar(PDO $pdo) {
    return (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol <> 'admin' AND verificado = 0")->fetchColumn();
}

function estadisticasVehiculo(PDO $pdo, $idVehiculo) {
    $st = $pdo->prepare(
        "SELECT COUNT(*) AS total_reservas, COALESCE(SUM(total), 0) AS ingresos
         FROM reservas
         WHERE id_vehiculo = ? AND estado IN ('confirmada', 'en_curso', 'finalizada')"
    );
    $st->execute([$idVehiculo]);
    return $st->fetch();
}

function registrarCambio(PDO $pdo, $idReserva, $campo, $antes, $despues, $idUsuario) {
    if ((string)$antes === (string)$despues) return;
    $pdo->prepare(
        "INSERT INTO historial_reservas
         (id_reserva, campo_modificado, valor_anterior, valor_nuevo, id_usuario)
         VALUES (?, ?, ?, ?, ?)"
    )->execute([$idReserva, $campo, $antes, $despues, $idUsuario]);
}

function datosComprobante(array $r, $base = '../') {
    $estados = [
        'confirmada' => 'Confirmada',
        'en_curso'   => 'En curso',
        'finalizada' => 'Finalizada',
        'cancelada'  => 'Cancelada',
    ];
    $dias = (int)$r['dias'];
    return [
        'codigo'      => $r['codigo'],
        'estado'      => $estados[$r['estado']] ?? ucfirst($r['estado']),
        'estadoColor' => $r['estado'] === 'cancelada' ? '#c0392b' : '#1e8449',
        'vehiculo'    => $r['marca'] . ' ' . $r['modelo'],
        'imagen'      => $r['imagen'] ? $base . $r['imagen'] : '',
        'recogida'    => $r['recogida'],
        'devolucion'  => $r['devolucion'],
        'fechaInicio' => date('d/m/Y', strtotime($r['fecha_inicio'])),
        'fechaFin'    => date('d/m/Y', strtotime($r['fecha_fin'])),
        'lineaPrecio' => pesos($r['precio_dia']) . ' / día × ' . $dias . ' día' . ($dias === 1 ? '' : 's'),
        'subtotal'    => pesos($r['precio_dia'] * $dias),
        'total'       => pesos($r['total']),
        'reservado'   => 'Reservado el ' . date('d/m/Y \a \l\a\s H:i', strtotime($r['fecha_creacion'])),
    ];
}

function numeroTarjetaValido($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    $suma = 0;
    $doble = false;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $d = (int)$numero[$i];
        if ($doble) {
            $d *= 2;
            if ($d > 9) $d -= 9;
        }
        $suma += $d;
        $doble = !$doble;
    }
    return $suma % 10 === 0 && strlen($numero) >= 13;
}

function actualizarEstadosReservas(PDO $pdo) {
    if (($_SESSION['estados_revisados'] ?? '') === date('Y-m-d')) return;

    $pdo->query("UPDATE reservas SET estado = 'en_curso'
                 WHERE estado = 'confirmada' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin");
    $pdo->query("UPDATE reservas SET estado = 'finalizada'
                 WHERE estado IN ('confirmada','en_curso') AND fecha_fin < CURDATE()");

    $_SESSION['estados_revisados'] = date('Y-m-d');
}

function crearNotificacion(PDO $pdo, $idUsuario, $mensaje, $enlace = null) {
    if (!$idUsuario) return;
    $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace) VALUES (?, ?, ?)")
        ->execute([$idUsuario, $mensaje, $enlace]);
}

function notificacionesSinLeer(PDO $pdo) {
    if (!haySesion()) return 0;
    $st = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0");
    $st->execute([usuarioId()]);
    return (int)$st->fetchColumn();
}

function calificacionVehiculo(PDO $pdo, $idVehiculo) {
    $st = $pdo->prepare("SELECT ROUND(AVG(estrellas), 1) AS promedio, COUNT(*) AS total
                         FROM calificaciones WHERE id_vehiculo = ?");
    $st->execute([$idVehiculo]);
    return $st->fetch();
}

function resenasVehiculo(PDO $pdo, $idVehiculo) {
    $st = $pdo->prepare(
        "SELECT c.estrellas, c.comentario, c.fecha, u.nombre
         FROM calificaciones c
         JOIN usuarios u ON u.id_usuario = c.id_usuario
         WHERE c.id_vehiculo = ?
         ORDER BY c.fecha DESC"
    );
    $st->execute([$idVehiculo]);
    return $st->fetchAll();
}

function esFavorito(PDO $pdo, $idVehiculo) {
    if (!haySesion()) return false;
    $st = $pdo->prepare("SELECT 1 FROM favoritos WHERE id_usuario = ? AND id_vehiculo = ?");
    $st->execute([usuarioId(), $idVehiculo]);
    return (bool)$st->fetchColumn();
}

function estrellas($promedio) {
    $llenas = (int)round((float)$promedio);
    $texto = '';
    for ($i = 1; $i <= 5; $i++) {
        $texto .= icono('estrella', $i <= $llenas);
    }
    return $texto;
}

function strftime_es($mes) {
    $nombres = ['01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr',
                '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago',
                '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'];
    [$anio, $numero] = explode('-', $mes);
    return $nombres[$numero] . ' ' . substr($anio, 2);
}

const PUNTOS_POR_DIA   = 20;
const COMISION_PLATAFORMA = 0.25;
const PESOS_POR_PUNTO  = 100;

function puntosDisponibles(PDO $pdo, $idUsuario = null) {
    $idUsuario = $idUsuario ?: usuarioId();
    if (!$idUsuario) return 0;
    $st = $pdo->prepare("SELECT COALESCE(SUM(puntos), 0) FROM movimientos_puntos WHERE id_usuario = ?");
    $st->execute([$idUsuario]);
    return (int)$st->fetchColumn();
}

function registrarPuntos(PDO $pdo, $idUsuario, $detalle, $puntos) {
    if (!$idUsuario || $puntos === 0) return;
    $pdo->prepare("INSERT INTO movimientos_puntos (id_usuario, detalle, puntos) VALUES (?, ?, ?)")
        ->execute([$idUsuario, $detalle, $puntos]);
}

function fechaValida($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

function guardarImagen($campo, $carpeta, $prefijo, $maxMb, &$error) {
    $error = null;

    if (empty($_FILES[$campo]['name'])) return null;

    if (in_array($_FILES[$campo]['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        $error = 'La imagen es muy pesada, debe pesar menos de ' . $maxMb . ' MB.';
        return null;
    }
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        $error = 'Hubo un problema al subir la imagen.';
        return null;
    }

    $extension = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $error = 'La imagen debe ser JPG, PNG o WEBP.';
        return null;
    }
    if ($_FILES[$campo]['size'] > $maxMb * 1024 * 1024) {
        $error = 'La imagen no puede pesar más de ' . $maxMb . ' MB.';
        return null;
    }
    if (getimagesize($_FILES[$campo]['tmp_name']) === false) {
        $error = 'El archivo no es una imagen válida.';
        return null;
    }

    $destino = __DIR__ . '/../' . $carpeta;
    if (!is_dir($destino)) mkdir($destino, 0755, true);

    $nombre = $prefijo . '_' . time() . '_' . random_int(100, 999) . '.' . $extension;

    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino . $nombre)) {
        $error = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }

    return $carpeta . $nombre;
}

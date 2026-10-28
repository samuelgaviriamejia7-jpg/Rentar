<?php

function guardarVehiculoDesdeFormulario(PDO $pdo, $estado, &$errores) {
    $errores = [];

    $placa       = strtoupper(trim($_POST['placa'] ?? ''));
    $marca       = trim($_POST['marca'] ?? '');
    $modelo      = trim($_POST['modelo'] ?? '');
    $anio        = (int)($_POST['anio'] ?? 0);
    $idCategoria = (int)($_POST['id_categoria'] ?? 0) ?: null;
    $pasajeros   = (int)($_POST['pasajeros'] ?? 5);
    $puertas     = (int)($_POST['puertas'] ?? 5);
    $transmision = $_POST['transmision'] ?? 'Automático';
    $combustible = $_POST['combustible'] ?? 'Gasolina';
    $precioDia   = (float)str_replace(['.', ','], ['', '.'], $_POST['precio_dia'] ?? '0');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $kmIlimitado = isset($_POST['km_ilimitado']) ? 1 : 0;
    $tipo        = in_array($_POST['tipo'] ?? '', ['carro', 'moto'], true) ? $_POST['tipo'] : 'carro';

    if ($placa === '' || strlen($placa) > 10)       $errores[] = 'La placa es obligatoria (máximo 10 caracteres).';
    if ($marca === '')                              $errores[] = 'La marca es obligatoria.';
    if ($modelo === '')                             $errores[] = 'El modelo es obligatorio.';
    if ($anio < 1990 || $anio > (int)date('Y') + 1) $errores[] = 'El año no es válido.';
    if ($precioDia <= 0)                            $errores[] = 'El precio por día debe ser mayor a cero.';
    if (!in_array($transmision, TRANSMISIONES, true)) $errores[] = 'Transmisión no válida.';
    if (!in_array($combustible, COMBUSTIBLES, true)) $errores[] = 'Combustible no válido.';

    $errorImagen = null;
    $imagenRuta  = $errores ? null : guardarImagen('imagen', 'uploads/vehiculos/', 'veh', 2, $errorImagen);
    if ($errorImagen)                  $errores[] = $errorImagen;
    elseif (!$errores && !$imagenRuta) $errores[] = 'Debes subir una foto del vehículo.';

    if ($errores) return false;

    try {
        $pdo->prepare(
            "INSERT INTO vehiculos
             (placa, marca, modelo, anio, id_categoria, tipo, pasajeros, puertas,
              transmision, combustible, precio_dia, imagen, descripcion,
              kilometraje_ilimitado, estado, id_admin)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([$placa, $marca, $modelo, $anio, $idCategoria, $tipo, $pasajeros, $puertas,
                    $transmision, $combustible, $precioDia, $imagenRuta, $descripcion,
                    $kmIlimitado, $estado, usuarioId()]);
        return true;
    } catch (PDOException $ex) {
        $errores[] = (int)$ex->errorInfo[1] === 1062
            ? 'Ya existe un vehículo registrado con esa placa.'
            : 'No se pudo guardar el vehículo.';
        return false;
    }
}

function estadoVehiculo(PDO $pdo, $idVehiculo) {
    $st = $pdo->prepare(
        "SELECT r.fecha_inicio, r.fecha_fin, r.estado,
                CONCAT(u.nombre,' ',u.apellido) AS cliente,
                s1.nombre AS recogida, s2.nombre AS devolucion
         FROM reservas r
         JOIN usuarios u ON u.id_usuario = r.id_usuario
         JOIN sedes s1   ON s1.id_sede   = r.id_sede_recogida
         JOIN sedes s2   ON s2.id_sede   = r.id_sede_devolucion
         WHERE r.id_vehiculo = ? AND r.estado IN ('confirmada','en_curso')
           AND CURDATE() BETWEEN r.fecha_inicio AND r.fecha_fin
         ORDER BY r.fecha_inicio DESC LIMIT 1"
    );
    $st->execute([$idVehiculo]);
    $activa = $st->fetch();

    if ($activa) {
        return [
            'clave'    => 'en_ruta',
            'titulo'   => 'En alquiler',
            'ubicacion' => 'Lo tiene ' . $activa['cliente'],
            'detalle'  => 'Lo entregó en ' . $activa['recogida'] . ' y lo devuelve en '
                        . $activa['devolucion'] . ' el ' . date('d/m/Y', strtotime($activa['fecha_fin'])) . '.',
        ];
    }

    $st = $pdo->prepare(
        "SELECT r.fecha_fin, s.nombre AS devolucion
         FROM reservas r
         JOIN sedes s ON s.id_sede = r.id_sede_devolucion
         WHERE r.id_vehiculo = ? AND r.estado <> 'cancelada' AND r.fecha_fin < CURDATE()
         ORDER BY r.fecha_fin DESC LIMIT 1"
    );
    $st->execute([$idVehiculo]);
    $ultima = $st->fetch();

    $st = $pdo->prepare(
        "SELECT r.fecha_inicio, s.nombre AS recogida
         FROM reservas r
         JOIN sedes s ON s.id_sede = r.id_sede_recogida
         WHERE r.id_vehiculo = ? AND r.estado IN ('confirmada','en_curso') AND r.fecha_inicio > CURDATE()
         ORDER BY r.fecha_inicio ASC LIMIT 1"
    );
    $st->execute([$idVehiculo]);
    $proxima = $st->fetch();

    $detalle = $proxima
        ? 'Sale en alquiler el ' . date('d/m/Y', strtotime($proxima['fecha_inicio'])) . ' desde ' . $proxima['recogida'] . '.'
        : 'No tiene reservas próximas.';

    return [
        'clave'     => 'en_sede',
        'titulo'    => 'En sede',
        'ubicacion' => $ultima ? $ultima['devolucion'] : 'Aún no ha salido en alquiler',
        'detalle'   => $detalle,
    ];
}

function abonoDelUsuario(PDO $pdo, $idVehiculo, $idUsuario = null) {
    $idUsuario = $idUsuario ?: usuarioId();

    $st = $pdo->prepare(
        "SELECT v.precio_venta, v.porcentaje_abono, v.en_venta,
                COALESCE(SUM(r.total), 0) AS alquilado
         FROM vehiculos v
         LEFT JOIN reservas r ON r.id_vehiculo = v.id_vehiculo
              AND r.id_usuario = ? AND r.estado <> 'cancelada'
         WHERE v.id_vehiculo = ?
         GROUP BY v.id_vehiculo"
    );
    $st->execute([$idUsuario, $idVehiculo]);
    $v = $st->fetch();

    if (!$v || !$v['en_venta'] || !$v['precio_venta']) return null;

    $abonado = $v['alquilado'] * $v['porcentaje_abono'] / 100;
    $precio  = (float)$v['precio_venta'];

    return [
        'precio'     => $precio,
        'porcentaje' => (int)$v['porcentaje_abono'],
        'abonado'    => $abonado,
        'falta'      => max($precio - $abonado, 0),
        'avance'     => $precio > 0 ? min(round($abonado / $precio * 100, 1), 100) : 0,
    ];
}

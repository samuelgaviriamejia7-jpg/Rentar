<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT id_usuario, nombre, contrasena, activo, rol, foto, verificado
                         FROM usuarios WHERE correo = ?");
    $st->execute([trim($_POST['correo'] ?? '')]);
    $u = $st->fetch();

    if ($u && $u['activo'] && password_verify($_POST['contrasena'] ?? '', $u['contrasena'])) {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $u['id_usuario'];
        $_SESSION['nombre']     = $u['nombre'];
        $_SESSION['rol']        = $u['rol'];
        $_SESSION['foto']       = $u['foto'];
        $_SESSION['verificado'] = (int)$u['verificado'];

        if (isset($_SESSION['reserva_pendiente']) && usuarioVerificado()) {
            $r = $_SESSION['reserva_pendiente'];
            unset($_SESSION['reserva_pendiente']);

            $stV = $pdo->prepare("SELECT precio_dia FROM vehiculos WHERE id_vehiculo = ? AND estado = 'disponible'");
            $stV->execute([$r['id_vehiculo']]);
            $precioDia = $stV->fetchColumn();

            if ($precioDia && vehiculoDisponible($pdo, $r['id_vehiculo'], $r['fecha_inicio'], $r['fecha_fin'])) {
                $dias  = max((new DateTime($r['fecha_inicio']))->diff(new DateTime($r['fecha_fin']))->days, 1);
                $total = $precioDia * $dias;

                $pdo->prepare(
                    "INSERT INTO reservas
                     (codigo, id_usuario, id_vehiculo, id_sede_recogida, id_sede_devolucion,
                      fecha_inicio, fecha_fin, precio_dia, dias, total, estado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmada')"
                )->execute([generarCodigoReserva(), $u['id_usuario'], $r['id_vehiculo'],
                            $r['id_sede_recogida'], $r['id_sede_devolucion'],
                            $r['fecha_inicio'], $r['fecha_fin'], $precioDia, $dias, $total]);

                mensaje('Reserva confirmada. Tu comprobante se está descargando.');
                ir('../01 - CRUD RESERVAS (samuel)/mis-reservas.php?descargar=' . $pdo->lastInsertId());
            }
        } elseif (isset($_SESSION['reserva_pendiente'])) {
            mensaje('Tu cuenta todavía no ha sido verificada por un administrador. Guardamos tu reserva y podrás completarla en cuanto te verifiquen.', 'error');
        }

        ir($u['rol'] === 'admin' ? '../05 - ADMINISTRACION (samuel)/panel.php' : '../07 - CRUD CATALOGO (alvarez)/catalogo.php');
    }
    mensaje('Correo o contraseña incorrectos.', 'error');
}

$titulo = 'Iniciar sesión';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Iniciar sesión</h1>
            <p>Ingresa a tu cuenta para continuar</p>
        </article>

        <?php mostrarMensaje(); ?>

        <article class="formulario">
            <h2>Tus datos</h2>
            <form method="post">
                <input type="email" name="correo" placeholder="Correo electrónico" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>
                <button type="submit" class="btn-form">INGRESAR</button>
            </form>
        </article>

        <div class="extra-links espacio-arriba">
            <a href="registro.php" class="primario">Crear cuenta</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

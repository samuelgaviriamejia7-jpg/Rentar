<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$st = $pdo->prepare("SELECT nombre, apellido, telefono, correo, foto, verificado, rol FROM usuarios WHERE id_usuario = ?");
$st->execute([usuarioId()]);
$u = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cuenta'])) {
    if (esAdmin()) {
        mensaje('Una cuenta de administrador no se puede eliminar desde aquí.', 'error');
        ir('editar-perfil.php');
    }

    $stClave = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = ?");
    $stClave->execute([usuarioId()]);

    if (!password_verify($_POST['contrasena_actual'] ?? '', (string)$stClave->fetchColumn())) {
        mensaje('La contraseña no es correcta.', 'error');
        ir('editar-perfil.php');
    }

    $stActivas = $pdo->prepare(
        "SELECT COUNT(*) FROM reservas
         WHERE id_usuario = ? AND estado IN ('confirmada', 'en_curso')"
    );
    $stActivas->execute([usuarioId()]);

    if ($stActivas->fetchColumn() > 0) {
        mensaje('Tienes reservas activas. Cancélalas o espera a que terminen antes de eliminar tu cuenta.', 'error');
        ir('editar-perfil.php');
    }

    $stAlquilados = $pdo->prepare(
        "SELECT COUNT(*) FROM reservas r
         JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
         WHERE v.id_admin = ? AND r.estado IN ('confirmada', 'en_curso')"
    );
    $stAlquilados->execute([usuarioId()]);

    if ($stAlquilados->fetchColumn() > 0) {
        mensaje('Alguien tiene reservado uno de tus vehículos. No puedes eliminar la cuenta hasta que esa reserva termine.', 'error');
        ir('editar-perfil.php');
    }

    $stHistorial = $pdo->prepare(
        "SELECT (SELECT COUNT(*) FROM reservas       WHERE id_usuario = ?)
              + (SELECT COUNT(*) FROM pagos          WHERE id_usuario = ?)
              + (SELECT COUNT(*) FROM calificaciones WHERE id_usuario = ?)
              + (SELECT COUNT(*) FROM vehiculos      WHERE id_admin   = ?)"
    );
    $stHistorial->execute([usuarioId(), usuarioId(), usuarioId(), usuarioId()]);
    $borradoTotal = (int)$stHistorial->fetchColumn() === 0;

    if ($borradoTotal) {
        if ($u['foto'] && file_exists(__DIR__ . '/../' . $u['foto'])) {
            unlink(__DIR__ . '/../' . $u['foto']);
        }
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([usuarioId()]);
    } else {
        $pdo->prepare("UPDATE vehiculos SET estado = 'inactivo', en_venta = 0 WHERE id_admin = ?")
            ->execute([usuarioId()]);
        $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = ?")
            ->execute([usuarioId()]);
    }

    $_SESSION = [];
    session_destroy();
    session_start();

    mensaje($borradoTotal
        ? 'Tu cuenta fue eliminada. Gracias por usar RentAr.'
        : 'Tu cuenta fue cerrada y ya no puedes iniciar sesión. Conservamos tus reservas y pagos anteriores porque son parte del historial de otros usuarios.');
    ir('../09 - REGISTRO Y LOGIN (saul)/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_rol'])) {
    if (esAdmin()) {
        mensaje('Una cuenta de administrador no cambia de tipo.', 'error');
    } else {
        $nuevoRol = $u['rol'] === 'arrendador' ? 'arrendatario' : 'arrendador';
        $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ? AND rol <> 'admin'")
            ->execute([$nuevoRol, usuarioId()]);
        $_SESSION['rol'] = $nuevoRol;
        mensaje($nuevoRol === 'arrendador'
            ? 'Listo, ahora tu cuenta es de arrendador: ya puedes publicar vehículos.'
            : 'Listo, ahora tu cuenta es de arrendatario: puedes reservar vehículos del catálogo.');
    }
    ir('editar-perfil.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $fotoRuta = $u['foto'];
    $error    = false;

    if ($nombre === '' || $correo === '') {
        mensaje('Nombre y correo son obligatorios.', 'error');
        $error = true;
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mensaje('El correo no es válido.', 'error');
        $error = true;
    }

    if (!$error && !empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];

        if (in_array($ext, $permitidas) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $carpeta = __DIR__ . '/../uploads/fotos/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            $nombreArchivo = 'perfil_' . time() . '_' . random_int(100, 999) . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta . $nombreArchivo);

            if ($fotoRuta && file_exists(__DIR__ . '/../' . $fotoRuta)) {
                unlink(__DIR__ . '/../' . $fotoRuta);
            }
            $fotoRuta = 'uploads/fotos/' . $nombreArchivo;
        } else {
            mensaje('La foto debe ser JPG, PNG, WEBP o JFIF y pesar menos de 2MB.', 'error');
            $error = true;
        }
    }

    if (!$error) {
        try {
            $pdo->prepare(
                "UPDATE usuarios SET nombre = ?, apellido = ?, telefono = ?, correo = ?, foto = ? WHERE id_usuario = ?"
            )->execute([$nombre, $apellido, $telefono, $correo, $fotoRuta, usuarioId()]);

            $_SESSION['nombre'] = $nombre;
            $_SESSION['foto']   = $fotoRuta;

            mensaje('Perfil actualizado.');
            ir('editar-perfil.php');
        } catch (PDOException $ex) {
            mensaje($ex->errorInfo[1] == 1062 ? 'Ese correo ya está en uso.' : 'Error al guardar.', 'error');
        }
    }
}

$titulo = 'Editar perfil';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Editar perfil</h1>
            <p>Actualiza tus datos y tu foto</p>
        </article>

        <?php mostrarMensaje(); ?>

        <?php if (!esAdmin()): ?>
            <div class="aviso aviso-<?= $u['verificado'] ? 'ok' : 'error' ?>">
                <?= $u['verificado']
                    ? 'Tu cuenta está verificada. Puedes reservar sin restricciones.'
                    : 'Tu cuenta está pendiente de verificación por un administrador.' ?>
            </div>
        <?php endif; ?>

        <?php if (!esAdmin()): ?>
        <article class="formulario tipo-cuenta">
            <h2>Tipo de cuenta</h2>
            <div class="tipo-cuenta-fila">
                <div>
                    <span class="etiqueta-rol etiqueta-<?= e($u['rol']) ?>">
                        <?= $u['rol'] === 'arrendador' ? 'Arrendador' : 'Arrendatario' ?>
                    </span>
                    <p><?= $u['rol'] === 'arrendador'
                        ? 'Publicas tus vehículos para que otros los alquilen.'
                        : 'Reservas vehículos del catálogo de RentAr.' ?></p>
                </div>
                <form method="post"
                      onsubmit="return confirm('<?= $u['rol'] === 'arrendador'
                          ? 'Vas a pasar a arrendatario. Tus publicaciones se conservan, pero no podrás crear nuevas hasta volver a ser arrendador. ¿Continuar?'
                          : 'Vas a pasar a arrendador para poder publicar tus vehículos. ¿Continuar?' ?>');">
                    <input type="hidden" name="cambiar_rol" value="1">
                    <button type="submit" class="btn-form">
                        Cambiar a <?= $u['rol'] === 'arrendador' ? 'arrendatario' : 'arrendador' ?>
                    </button>
                </form>
            </div>
        </article>
        <?php endif; ?>

        <article class="formulario">
            <h2>Tus datos</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="foto-perfil-container">
                    <img src="../<?= e($u['foto'] ?: 'img/avatar.png') ?>" alt="Foto" class="foto-perfil-preview" id="previewFoto">
                    <label style="cursor:pointer; color:var(--secondary); font-weight:600;">
                        Cambiar foto de perfil
                        <input type="file" name="foto" accept="image/*" class="foto-perfil-input"
                               style="display:none;" onchange="previsualizarFoto(this)">
                    </label>
                </div>
                <input type="text" name="nombre" placeholder="Nombre" value="<?= e($u['nombre']) ?>" required>
                <input type="text" name="apellido" placeholder="Apellido" value="<?= e($u['apellido']) ?>">
                <input type="email" name="correo" placeholder="Correo electrónico" value="<?= e($u['correo']) ?>" required>
                <input type="text" name="telefono" placeholder="Teléfono" value="<?= e($u['telefono']) ?>">
                <button type="submit" class="btn-form">GUARDAR CAMBIOS</button>
            </form>
        </article>

        <article class="formulario zona-peligro">
            <h2>Eliminar mi cuenta</h2>
            <?php if (esAdmin()): ?>
                <p class="zona-peligro-texto">
                    Las cuentas de administrador no se eliminan desde aquí. Son las que verifican
                    usuarios y aprueban vehículos, así que si se borran la plataforma queda sin quien
                    revise. Para dar de baja un administrador se hace directamente en la base de datos.
                </p>
            <?php else: ?>
            <p class="zona-peligro-texto">
                Se cierra tu cuenta y dejas de poder iniciar sesión. Si nunca reservaste ni publicaste nada,
                se borra por completo. Si ya tienes reservas o pagos, esos registros se conservan porque
                también hacen parte del historial de la otra persona.
            </p>
            <form method="post"
                  onsubmit="return confirm('Esta acción no se puede deshacer. ¿Eliminar tu cuenta?');">
                <input type="hidden" name="eliminar_cuenta" value="1">
                <input type="password" name="contrasena_actual" placeholder="Escribe tu contraseña para confirmar" required>
                <button type="submit" class="btn-form btn-peligro">ELIMINAR MI CUENTA</button>
            </form>
            <?php endif; ?>
        </article>

        <div class="extra-links espacio-arriba">
            <a href="../01 - CRUD RESERVAS (samuel)/mis-reservas.php" class="primario">Volver a mis reservas</a>
        </div>
    </section>
</main>

<script>
function previsualizarFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $clave     = $_POST['contrasena'] ?? '';
    $rol       = ($_POST['rol'] ?? '') === 'arrendador' ? 'arrendador' : 'arrendatario';
    $fotoRuta  = null;

    if ($nombre === '' || $correo === '' || $clave === '' || $documento === '') {
        mensaje('Completa los campos obligatorios.', 'error');
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mensaje('El correo no es válido.', 'error');
    } elseif (strlen($clave) < 6) {
        mensaje('La contraseña debe tener mínimo 6 caracteres.', 'error');
    } else {
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $permitidas) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
                $carpeta = __DIR__ . '/../uploads/fotos/';
                if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
                $nombreArchivo = 'perfil_' . time() . '_' . random_int(100,999) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta . $nombreArchivo);
                $fotoRuta = 'uploads/fotos/' . $nombreArchivo;
            }
        }

        try {
            $pdo->prepare(
                "INSERT INTO usuarios (nombre, apellido, documento, correo, telefono, contrasena, foto, rol, verificado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)"
            )->execute([$nombre, $apellido, $documento, $correo, $telefono,
                        password_hash($clave, PASSWORD_DEFAULT), $fotoRuta, $rol]);
            mensaje($rol === 'arrendador'
                ? 'Cuenta creada. Un administrador debe verificarla antes de que puedas publicar vehículos.'
                : 'Cuenta creada. Un administrador debe verificarla antes de que puedas reservar.');
            ir('login.php');
        } catch (PDOException $ex) {
            mensaje($ex->errorInfo[1] == 1062
                ? 'Ese correo o documento ya está registrado.'
                : 'Error al registrar.', 'error');
        }
    }
}

$titulo = 'Crear cuenta';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Crear cuenta</h1>
            <p>Elige cómo quieres usar RentAr y completa tus datos</p>
        </article>

        <?php mostrarMensaje(); ?>

        <article class="formulario">
            <form method="post" enctype="multipart/form-data">

                <h2>¿Qué quieres hacer en RentAr?</h2>
                <div class="rol-picker">
                    <label class="tarjeta-rol seleccionada">
                        <input type="radio" name="rol" value="arrendatario" checked>
                        <svg class="ico-rol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="8.2"/>
                            <circle cx="12" cy="12" r="2.3"/>
                            <path d="M12 4.2v5.3M6.7 14.8l4.1-2.6M17.3 14.8l-4.1-2.6"/>
                        </svg>
                        <strong>Quiero rentar</strong>
                        <span>Buscar y reservar vehículos del catálogo</span>
                    </label>
                    <label class="tarjeta-rol">
                        <input type="radio" name="rol" value="arrendador">
                        <svg class="ico-rol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5.2 16.5v-3.8l1.6-3.8h10.4l1.6 3.8v3.8"/>
                            <path d="M4 16.5h1.2M18.8 16.5H20"/>
                            <circle cx="8" cy="16.5" r="1.7"/>
                            <circle cx="16" cy="16.5" r="1.7"/>
                            <path d="M12 3v4.3M9.6 5.4 12 3l2.4 2.4"/>
                        </svg>
                        <strong>Quiero publicar</strong>
                        <span>Ofrecer mi propio vehículo y generar ingresos</span>
                    </label>
                </div>

                <h2>Tus datos</h2>
                <div class="foto-perfil-container">
                    <img src="../img/avatar.png" alt="Foto" class="foto-perfil-preview" id="previewFoto">
                    <label style="cursor:pointer; color:var(--secondary); font-weight:600;">
                        Subir foto de perfil
                        <input type="file" name="foto" accept="image/*" class="foto-perfil-input"
                               style="display:none;" onchange="previsualizarFoto(this)">
                    </label>
                </div>
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellido" placeholder="Apellido">
                <input type="text" name="documento" placeholder="Documento" required>
                <input type="email" name="correo" placeholder="Correo electrónico" required>
                <input type="text" name="telefono" placeholder="Teléfono">
                <div class="campo-contrasena">
                    <input type="password" name="contrasena" id="clave-registro" placeholder="Contraseña (mínimo 6)" required>
                    <button type="button" class="toggle-ojo" onclick="alternarOjo('clave-registro', this)" aria-label="Mostrar contraseña">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <button type="submit" class="btn-form">CREAR CUENTA</button>
            </form>
        </article>

        <div class="extra-links espacio-arriba">
            <a href="login.php" class="primario">Ya tengo cuenta</a>
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

document.querySelectorAll('.tarjeta-rol input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.tarjeta-rol').forEach(t => t.classList.remove('seleccionada'));
        radio.closest('.tarjeta-rol').classList.add('seleccionada');
    });
});

function alternarOjo(idInput, boton) {
    const input = document.getElementById(idInput);
    const abierto = input.type === 'text';
    input.type = abierto ? 'password' : 'text';
    boton.innerHTML = abierto
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a21.6 21.6 0 0 1-2.94 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
}
</script>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

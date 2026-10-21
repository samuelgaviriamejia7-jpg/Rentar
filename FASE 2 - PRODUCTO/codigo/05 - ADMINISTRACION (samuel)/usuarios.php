<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = (int)($_POST['id_usuario'] ?? 0);
    $accion    = $_POST['accion'] ?? '';

    if ($idUsuario === (int)usuarioId()) {
        mensaje('No puedes cambiar tu propia verificación.', 'error');

    } elseif ($accion === 'verificar') {
        $pdo->prepare("UPDATE usuarios SET verificado = 1 WHERE id_usuario = ? AND rol <> 'admin'")
            ->execute([$idUsuario]);
        crearNotificacion($pdo, $idUsuario, 'Tu cuenta fue verificada. Ya puedes usar RentAr sin restricciones.', '07 - CRUD CATALOGO (alvarez)/catalogo.php');
        mensaje('Usuario verificado. Ya puede reservar.');

    } elseif ($accion === 'quitar') {
        $pdo->prepare("UPDATE usuarios SET verificado = 0 WHERE id_usuario = ? AND rol <> 'admin'")
            ->execute([$idUsuario]);
        mensaje('Se quitó la verificación al usuario.');
    }
    ir('usuarios.php?filtro=' . urlencode($_POST['filtro'] ?? 'pendientes'));
}

$filtro = ($_GET['filtro'] ?? 'pendientes') === 'todos' ? 'todos' : 'pendientes';

$sql = "SELECT id_usuario, nombre, apellido, documento, correo, telefono, foto, rol, verificado, fecha_registro
        FROM usuarios
        WHERE rol <> 'admin'";
if ($filtro === 'pendientes') $sql .= " AND verificado = 0";
$sql .= " ORDER BY fecha_registro DESC";

$usuarios = $pdo->query($sql)->fetchAll();

$titulo = 'Verificación de usuarios';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

<main class="hero hero-utility">
    <section class="hero-info">
        <article class="hero-title">
            <h1>Verificación de usuarios</h1>
            <p>Un arrendatario no puede reservar, y un arrendador no puede publicar, hasta que un administrador verifique la cuenta</p>
        </article>

        <?php mostrarMensaje(); ?>

        <nav class="extra-links espacio-abajo">
            <a href="usuarios.php?filtro=pendientes" style="<?= $filtro === 'pendientes' ? '' : 'background:var(--secondary);' ?>">Pendientes</a>
            <a href="usuarios.php?filtro=todos" style="<?= $filtro === 'todos' ? '' : 'background:var(--secondary);' ?>">Todos los usuarios</a>
            <a href="panel.php">Volver al panel</a>
        </nav>

        <?php if (!$usuarios): ?>
            <div class="estado-vacio">
                <img src="../img/usuario.png" alt="">
                <h3>No hay usuarios <?= $filtro === 'pendientes' ? 'pendientes' : 'registrados' ?></h3>
                <p><?= $filtro === 'pendientes'
                        ? 'Todos los usuarios registrados ya fueron verificados.'
                        : 'Todavía no se ha registrado ningún usuario.' ?></p>
            </div>
        <?php endif; ?>

        <?php foreach ($usuarios as $u): ?>
        <article class="formulario" style="margin-bottom:16px;">
            <div class="fila-flexible">

                <img src="<?= $u['foto'] ? '../' . e($u['foto']) : '../img/avatar.png' ?>" alt=""
                     class="foto-mini">

                <div class="columna-crece">
                    <h2><?= e(trim($u['nombre'] . ' ' . $u['apellido'])) ?>
                        <span class="etiqueta-rol etiqueta-<?= e($u['rol']) ?>">
                            <?= $u['rol'] === 'arrendador' ? 'Arrendador' : 'Arrendatario' ?>
                        </span>
                    </h2>
                    <p><strong>Documento:</strong> <?= e($u['documento']) ?></p>
                    <p><strong>Correo:</strong> <?= e($u['correo']) ?></p>
                    <?php if ($u['telefono']): ?>
                        <p><strong>Teléfono:</strong> <?= e($u['telefono']) ?></p>
                    <?php endif; ?>
                    <p><strong>Registrado:</strong> <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></p>
                    <p>
                        <span class="badge-estado badge-<?= $u['verificado'] ? 'disponible' : 'pendiente' ?>">
                            <?= $u['verificado'] ? 'Verificado' : 'Sin verificar' ?>
                        </span>
                    </p>
                </div>

                <form method="post" style="min-width:180px;"
                      onsubmit="return confirm('<?= $u['verificado']
                            ? '¿Quitar la verificación a este usuario?'
                            : '¿Verificar a este usuario? Podrá reservar de inmediato.' ?>');">
                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                    <input type="hidden" name="filtro" value="<?= e($filtro) ?>">
                    <?php if ($u['verificado']): ?>
                        <input type="hidden" name="accion" value="quitar">
                        <button type="submit" class="btn-form btn-rojo">Quitar verificación</button>
                    <?php else: ?>
                        <input type="hidden" name="accion" value="verificar">
                        <button type="submit" class="btn-form btn-verde">Verificar</button>
                    <?php endif; ?>
                </form>

            </div>
        </article>
        <?php endforeach; ?>

    </section>
</main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

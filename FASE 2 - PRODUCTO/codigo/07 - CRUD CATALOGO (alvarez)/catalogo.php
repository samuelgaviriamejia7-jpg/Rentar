<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';

$where = ["v.estado = 'disponible'"]; $params = [];

if (!empty($_GET['transmision'])) { $where[] = "v.transmision = :t"; $params['t'] = $_GET['transmision']; }
if (!empty($_GET['combustible'])) { $where[] = "v.combustible = :c"; $params['c'] = $_GET['combustible']; }

if (!empty($_GET['tipo']) && in_array($_GET['tipo'], ['carro', 'moto'], true)) {
    $where[] = "v.tipo = :tipo";
    $params['tipo'] = $_GET['tipo'];
}

$precioMax = (int)($_GET['precio_max'] ?? 0);
if ($precioMax > 0) {
    $where[] = "v.precio_dia <= :precio_max";
    $params['precio_max'] = $precioMax;
}

$desde = trim($_GET['desde'] ?? '');
$hasta = trim($_GET['hasta'] ?? '');
if (fechaValida($desde) && fechaValida($hasta) && $hasta >= $desde) {
    $where[] = "v.id_vehiculo NOT IN (
                    SELECT id_vehiculo FROM reservas
                    WHERE estado IN ('confirmada','en_curso')
                      AND fecha_inicio <= :hasta AND fecha_fin >= :desde)";
    $params['desde'] = $desde;
    $params['hasta'] = $hasta;
} else {
    $desde = $hasta = '';
}

$orden = ($_GET['orden'] ?? '') === 'precio' ? 'v.precio_dia ASC' : 'v.id_vehiculo ASC';

$st = $pdo->prepare("SELECT v.*, c.nombre AS categoria
                     FROM vehiculos v
                     LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
                     WHERE " . implode(' AND ', $where) . "
                     ORDER BY $orden");
$st->execute($params);
$vehiculos = $st->fetchAll();

$tipoActivo  = $_GET['tipo'] ?? '';
$transActiva = $_GET['transmision'] ?? '';
$combActivo  = $_GET['combustible'] ?? '';
$ordenActivo = $_GET['orden'] ?? '';
$sinFiltro   = !$tipoActivo && !$transActiva && !$combActivo && !$ordenActivo;

$titulo = 'Catálogo';
require_once __DIR__ . '/../06 - NUCLEO samuel/header.php';
?>

    <main class="catalogo">
        <div class="catalogo-text">
            <h2>¿Qué coche deseas conducir?</h2>
            <div class="filtros">

                <div class="filtro-grupo">
                    <span class="filtro-titulo">Tipo</span>
                    <div class="botones">
                        <a href="catalogo.php" class="<?= $sinFiltro ? 'activo' : '' ?>">
                            <img src="../img/filtrar.png" alt="">Todos
                        </a>
                        <a href="catalogo.php?tipo=carro" class="<?= $tipoActivo === 'carro' ? 'activo' : '' ?>">
                            <?= icono('carro') ?>Carros
                        </a>
                        <a href="catalogo.php?tipo=moto" class="<?= $tipoActivo === 'moto' ? 'activo' : '' ?>">
                            <?= icono('moto') ?>Motos
                        </a>
                    </div>
                </div>

                <div class="filtro-grupo">
                    <span class="filtro-titulo">Precio máximo</span>
                    <div class="botones">
                        <?php foreach ([150000, 250000, 350000] as $tope): ?>
                            <a href="?precio_max=<?= $tope ?>" class="<?= $precioMax === $tope ? 'activo' : '' ?>">
                                Hasta <?= pesos($tope) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filtro-grupo">
                    <span class="filtro-titulo">Características</span>
                    <div class="botones">
                        <a href="catalogo.php?transmision=Autom%C3%A1tico" class="<?= $transActiva === 'Automático' ? 'activo' : '' ?>">
                            <img src="../img/a.png" alt="">Automáticos
                        </a>
                        <a href="catalogo.php?combustible=H%C3%ADbrido" class="<?= $combActivo === 'Híbrido' ? 'activo' : '' ?>">
                            <img src="../img/cable.png" alt="">Híbridos
                        </a>
                        <a href="catalogo.php?orden=precio" class="<?= $ordenActivo === 'precio' ? 'activo' : '' ?>">
                            <img src="../img/precio.png" alt="">Precio más bajo
                        </a>
                    </div>
                </div>

            </div>

            <form method="get" class="filtro-fechas">
                <span class="filtro-titulo">Disponible entre</span>
                <div>
                    <input type="date" name="desde" value="<?= e($desde) ?>" min="<?= date('Y-m-d') ?>">
                    <input type="date" name="hasta" value="<?= e($hasta) ?>" min="<?= date('Y-m-d') ?>">
                    <button type="submit" class="btn-form">Buscar</button>
                </div>
            </form>
        </div>

        <?php if (haySesion() && !usuarioVerificado()): ?>
            <div class="aviso aviso-error aviso-centrado">
                <?= esArrendador()
                    ? 'Tu cuenta está pendiente de verificación por un administrador. Podrás publicar vehículos en cuanto la aprueben.'
                    : 'Tu cuenta está pendiente de verificación por un administrador. Podrás reservar en cuanto la aprueben.' ?>
            </div>
        <?php endif; ?>

        <?php if (!haySesion() || esArrendador() || esAdmin()): ?>
        <div class="banner-publicar">
            <div class="banner-publicar-texto">
                <h3>¿Tienes carro y no siempre lo usas?</h3>
                <p>Publícalo en RentAr y genera ingresos mientras estás de viaje.</p>
            </div>
            <?php if (haySesion()): ?>
                <a href="../02 - CRUD VEHICULOS (samuel)/publicar-vehiculo.php" class="banner-publicar-btn">Publicar mi vehículo</a>
            <?php else: ?>
                <a href="../09 - REGISTRO Y LOGIN (saul)/registro.php" class="banner-publicar-btn">Crear cuenta de arrendador</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="opciones">
            <?php foreach ($vehiculos as $v): ?>
            <?php $cal = calificacionVehiculo($pdo, $v['id_vehiculo']); ?>
            <div class="card-envoltura">
            <?php if (haySesion()): ?>
                <form method="post" action="../04 - PERFIL Y CUENTA (samuel)/favoritos.php" class="form-favorito">
                    <input type="hidden" name="id_vehiculo" value="<?= $v['id_vehiculo'] ?>">
                    <input type="hidden" name="volver" value="../07 - CRUD CATALOGO (alvarez)/catalogo.php">
                    <button type="submit" class="btn-favorito <?= esFavorito($pdo, $v['id_vehiculo']) ? 'activo' : '' ?>"
                            title="Guardar en favoritos"><?= icono('corazon', esFavorito($pdo, $v['id_vehiculo'])) ?></button>
                </form>
            <?php endif; ?>
            <a href="../01 - CRUD RESERVAS (samuel)/reservas.php?id=<?= $v['id_vehiculo'] ?>" class="card">
                <h3><?= e($v['marca'] . ' ' . $v['modelo']) ?></h3>
                <div class="card-info">
                    <span><img src="../img/usuario.png" alt=""><?= (int)$v['pasajeros'] ?></span>
                    <span><?= e($v['transmision']) ?></span>
                    <span class="card-tipo"><?= icono($v['tipo'] === 'moto' ? 'moto' : 'carro') ?><?= $v['tipo'] === 'moto' ? 'Moto' : 'Carro' ?></span>
                    <?php if ($v['combustible'] === 'Híbrido'): ?>
                    <span><img src="../img/cable.png" alt="">Híbrido</span>
                    <?php endif; ?>
                </div>
                <?php if ($cal['total']): ?>
                    <span class="card-estrellas"><?= estrellas($cal['promedio']) ?> <?= $cal['promedio'] ?></span>
                <?php endif; ?>
                <img src="../<?= e($v['imagen']) ?>" alt="" class="card-car">
            </a>
            </div>
            <?php endforeach; ?>

            <?php if (!$vehiculos): ?>
                <p>No hay vehículos que coincidan con el filtro.</p>
            <?php endif; ?>
        </div>
    </main>

<?php require_once __DIR__ . '/../06 - NUCLEO samuel/footer.php'; ?>

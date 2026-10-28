<section class="formularios">
    <form method="post" enctype="multipart/form-data" class="form-vehiculo">

        <div class="formulario">
            <h2>Datos del vehículo</h2>

            <div class="form-vehiculo-grid">

                <input type="text" name="placa" placeholder="Placa (ej: ABC123)" maxlength="10" required>
                <input type="text" name="marca" placeholder="Marca (ej: Toyota)" required>

                <input type="text" name="modelo" placeholder="Modelo (ej: Corolla Cross)" required>
                <input type="number" name="anio" placeholder="Año" min="1990" max="<?= date('Y') + 1 ?>" required>

                <select name="tipo" required>
                    <option value="carro">Carro</option>
                    <option value="moto">Moto</option>
                </select>
                <select name="id_categoria">
                    <option value="">Sin categoría</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="precio_dia" placeholder="Precio por día (ej: 150000)" required>
                <input type="number" name="pasajeros" placeholder="Pasajeros" min="1" max="9" value="5" required>

                <input type="number" name="puertas" placeholder="Puertas (0 si es moto)" min="0" max="5" value="5" required>
                <select name="transmision" required>
                    <?php foreach (TRANSMISIONES as $t): ?>
                        <option value="<?= e($t) ?>"><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="combustible" required>
                    <?php foreach (COMBUSTIBLES as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="form-vehiculo-full">
                    <textarea name="descripcion" placeholder="Cuéntale al equipo de RentAr sobre tu vehículo" rows="2"></textarea>
                </div>

                <label class="form-vehiculo-check">
                    <input type="checkbox" name="km_ilimitado" checked>
                    Ofrezco kilometraje ilimitado
                </label>

                <div class="form-vehiculo-foto">
                    <label>Foto del vehículo</label>
                    <input type="file" name="imagen" accept="image/*" required>
                    <span class="ayuda-campo">JPG, PNG o WEBP de máximo 2 MB</span>
                </div>

            </div>
        </div>

        <button type="submit" class="btn-form"><?= e($textoBoton) ?></button>
    </form>

    <nav class="extra-links">
        <?php foreach ($enlaces as $url => $texto): ?>
            <a href="<?= e($url) ?>"><?= e($texto) ?></a>
        <?php endforeach; ?>
    </nav>
</section>

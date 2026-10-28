DROP DATABASE IF EXISTS rentar;

CREATE DATABASE rentar
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rentar;

CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(60)  NOT NULL,
    apellido        VARCHAR(60)  NOT NULL,
    documento       VARCHAR(20)  NOT NULL UNIQUE,
    correo          VARCHAR(120) NOT NULL UNIQUE,
    telefono        VARCHAR(20),
    contrasena      VARCHAR(255) NOT NULL,
    ciudad          VARCHAR(60)  DEFAULT 'Medellín',
    foto            VARCHAR(255) DEFAULT NULL,
    rol             ENUM('arrendatario','arrendador','admin') NOT NULL DEFAULT 'arrendatario',
    activo          TINYINT(1)   NOT NULL DEFAULT 1,
    verificado      TINYINT(1)   NOT NULL DEFAULT 0,
    fecha_registro  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50) NOT NULL UNIQUE,
    descripcion     VARCHAR(200)
) ENGINE=InnoDB;

CREATE TABLE vehiculos (
    id_vehiculo     INT AUTO_INCREMENT PRIMARY KEY,
    placa           VARCHAR(10)  NOT NULL UNIQUE,
    marca           VARCHAR(40)  NOT NULL,
    modelo          VARCHAR(60)  NOT NULL,
    anio            YEAR         NOT NULL,
    id_categoria    INT,
    tipo            ENUM('carro','moto') NOT NULL DEFAULT 'carro',
    pasajeros       TINYINT      NOT NULL DEFAULT 5,
    puertas         TINYINT      NOT NULL DEFAULT 5,
    transmision     ENUM('Automático','Manual') NOT NULL DEFAULT 'Automático',
    combustible     ENUM('Gasolina','Diésel','Híbrido','Eléctrico') NOT NULL DEFAULT 'Gasolina',
    precio_dia      DECIMAL(12,2) NOT NULL,
    imagen          VARCHAR(255),
    descripcion     TEXT,
    kilometraje_ilimitado TINYINT(1) NOT NULL DEFAULT 1,
    estado          ENUM('pendiente','disponible','rechazado','mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
    en_venta        TINYINT(1) NOT NULL DEFAULT 0,
    precio_venta    DECIMAL(12,2) DEFAULT NULL,
    porcentaje_abono TINYINT NOT NULL DEFAULT 10,
    motivo_rechazo  VARCHAR(255) DEFAULT NULL,
    id_admin        INT,
    fecha_creacion  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehiculo_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON DELETE SET NULL,
    CONSTRAINT fk_vehiculo_admin
        FOREIGN KEY (id_admin) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL,
    CONSTRAINT chk_precio_dia CHECK (precio_dia > 0),
    CONSTRAINT chk_abono CHECK (porcentaje_abono BETWEEN 0 AND 50)
) ENGINE=InnoDB;

CREATE TABLE sedes (
    id_sede         INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    direccion       VARCHAR(150),
    ciudad          VARCHAR(60)  NOT NULL DEFAULT 'Medellín',
    telefono        VARCHAR(20),
    abierto_24h     TINYINT(1)   NOT NULL DEFAULT 0,
    activa          TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE reservas (
    id_reserva          INT AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(15) NOT NULL UNIQUE,
    id_usuario          INT  NOT NULL,
    id_vehiculo         INT  NOT NULL,
    id_sede_recogida    INT  NOT NULL,
    id_sede_devolucion  INT  NOT NULL,
    fecha_inicio        DATE NOT NULL,
    fecha_fin           DATE NOT NULL,
    precio_dia          DECIMAL(12,2) NOT NULL,
    dias                INT  NOT NULL DEFAULT 1,
    total               DECIMAL(12,2) NOT NULL,
    estado              ENUM('confirmada','en_curso','finalizada','cancelada') NOT NULL DEFAULT 'confirmada',
    fecha_creacion      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_reserva_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo),
    CONSTRAINT fk_reserva_sede_recogida FOREIGN KEY (id_sede_recogida) REFERENCES sedes(id_sede),
    CONSTRAINT fk_reserva_sede_devolucion FOREIGN KEY (id_sede_devolucion) REFERENCES sedes(id_sede),
    CONSTRAINT chk_fechas_reserva CHECK (fecha_fin >= fecha_inicio)
) ENGINE=InnoDB;

CREATE INDEX idx_reservas_usuario ON reservas(id_usuario);
CREATE INDEX idx_reservas_fechas  ON reservas(fecha_inicio, fecha_fin);
CREATE INDEX idx_reservas_estado  ON reservas(estado);
CREATE INDEX idx_vehiculos_admin  ON vehiculos(id_admin);

CREATE TABLE historial_reservas (
    id_historial     INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva       INT NOT NULL,
    campo_modificado VARCHAR(50)  NOT NULL,
    valor_anterior   VARCHAR(150),
    valor_nuevo      VARCHAR(150),
    id_usuario       INT,
    fecha_cambio     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pagos (
    id_pago         INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    id_reserva      INT DEFAULT NULL,
    concepto        VARCHAR(120) NOT NULL,
    monto           DECIMAL(12,2) NOT NULL,
    metodo          ENUM('tarjeta','pse','efectivo') NOT NULL DEFAULT 'tarjeta',
    ultimos_digitos CHAR(4) DEFAULT NULL,
    referencia      VARCHAR(20) NOT NULL UNIQUE,
    estado          ENUM('aprobado','rechazado') NOT NULL DEFAULT 'aprobado',
    fecha_pago      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_pago_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE SET NULL,
    CONSTRAINT chk_monto_pago CHECK (monto > 0)
) ENGINE=InnoDB;

CREATE INDEX idx_pagos_usuario ON pagos(id_usuario);

CREATE TABLE calificaciones (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva      INT NOT NULL UNIQUE,
    id_vehiculo     INT NOT NULL,
    id_usuario      INT NOT NULL,
    estrellas       TINYINT NOT NULL,
    comentario      VARCHAR(300) DEFAULT NULL,
    fecha           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_calificacion_reserva  FOREIGN KEY (id_reserva)  REFERENCES reservas(id_reserva) ON DELETE CASCADE,
    CONSTRAINT fk_calificacion_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo) ON DELETE CASCADE,
    CONSTRAINT fk_calificacion_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario),
    CONSTRAINT chk_estrellas CHECK (estrellas BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario  INT NOT NULL,
    id_vehiculo INT NOT NULL,
    fecha       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_favorito (id_usuario, id_vehiculo),
    CONSTRAINT fk_favorito_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_favorito_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    mensaje         VARCHAR(200) NOT NULL,
    enlace          VARCHAR(120) DEFAULT NULL,
    leida           TINYINT(1) NOT NULL DEFAULT 0,
    fecha           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_notificaciones_usuario ON notificaciones(id_usuario, leida);
CREATE INDEX idx_calificaciones_vehiculo ON calificaciones(id_vehiculo);

CREATE TABLE movimientos_puntos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario    INT NOT NULL,
    detalle       VARCHAR(150) NOT NULL,
    puntos        INT NOT NULL,
    fecha         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_puntos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_puntos_usuario ON movimientos_puntos(id_usuario);

INSERT INTO categorias (nombre, descripcion) VALUES
('SUV', 'Vehículos altos y espaciosos'),
('Sedán', 'Vehículos de cuatro puertas'),
('Hatchback', 'Compactos con portón trasero'),
('Moto', 'Motocicletas y scooters');

INSERT INTO usuarios (nombre, apellido, documento, correo, telefono, contrasena, rol, verificado) VALUES
('Samuel','Gaviria','1000000001','admin@rentar.com','+57 300 000 0000',
 '$2y$10$4FonJwS9AOOFBl2D9o55HOvTTdLJD9v/dbWVoUc1k51QRhCPeNlfq','admin',1);

INSERT INTO vehiculos (placa, marca, modelo, anio, id_categoria, tipo, pasajeros, puertas, transmision, combustible, precio_dia, imagen, descripcion, id_admin) VALUES
('ABC123','Toyota','Corolla Cross',2024,1,'carro',5,5,'Automático','Gasolina',160000.00,'img/cross.png','SUV cómoda y económica.',1),
('DEF456','Audi','A3',2024,2,'carro',5,5,'Automático','Gasolina',256000.00,'img/audi.png','Sedán premium con acabados de lujo.',1),
('GHI789','Cupra','Formentor',2025,1,'carro',5,5,'Automático','Híbrido',325000.00,'img/cupra.png','SUV deportiva híbrida.',1),
('MNX01A','Yamaha','NMAX 155',2024,4,'moto',2,0,'Automático','Gasolina',85000.00,'img/nmax.png','Scooter automática, ágil para moverse por la ciudad. Incluye casco.',1);

INSERT INTO sedes (nombre, direccion, ciudad, abierto_24h) VALUES
('Robledo, parque principal','Parque Robledo','Medellín',1),
('Aeropuerto Olaya Herrera','Carrera 65A #13-157','Medellín',1);

CREATE VIEW v_catalogo AS
SELECT v.id_vehiculo, v.marca, v.modelo, c.nombre AS categoria, v.tipo,
       v.pasajeros, v.transmision, v.combustible, v.precio_dia, v.imagen
FROM vehiculos v
LEFT JOIN categorias c ON c.id_categoria = v.id_categoria
WHERE v.estado = 'disponible';

CREATE VIEW v_mis_reservas AS
SELECT r.id_reserva, r.codigo, u.nombre, u.apellido,
       CONCAT(v.marca,' ',v.modelo) AS vehiculo,
       s1.nombre AS recogida, s2.nombre AS devolucion,
       r.fecha_inicio, r.fecha_fin, r.dias, r.total, r.estado
FROM reservas r
JOIN usuarios u  ON u.id_usuario  = r.id_usuario
JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
JOIN sedes s1 ON s1.id_sede = r.id_sede_recogida
JOIN sedes s2 ON s2.id_sede = r.id_sede_devolucion;

CREATE VIEW v_vehiculos_admin AS
SELECT v.id_vehiculo, v.marca, v.modelo, v.precio_dia, v.estado,
       CONCAT(u.nombre,' ',u.apellido) AS publicado_por, v.fecha_creacion
FROM vehiculos v
LEFT JOIN usuarios u ON u.id_usuario = v.id_admin;

CREATE VIEW v_usuarios_verificacion AS
SELECT u.id_usuario, u.nombre, u.apellido, u.documento, u.correo,
       u.telefono, u.rol, u.verificado, u.fecha_registro
FROM usuarios u
WHERE u.rol <> 'admin';

SHOW TABLES;
SELECT id_usuario, nombre, correo, rol, verificado FROM usuarios;
SELECT * FROM v_vehiculos_admin;

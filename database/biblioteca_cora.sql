-- ══════════════════════════════════════════════════════════════════════════
--  BIBLIOTECA CORA · Sistema Web de Gestión Bibliotecaria
--  Script de base de datos MySQL / MariaDB  (XAMPP)
--  Proyecto académico SENA – ADSO
--
--  Importar desde phpMyAdmin  →  Importar → biblioteca_cora.sql
-- ══════════════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS biblioteca_cora
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE biblioteca_cora;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS prestamos;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────────
-- TABLA usuarios  (RF-03, RF-04, RF-05)
-- ────────────────────────────────────────────────
CREATE TABLE usuarios (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre         VARCHAR(80)      NOT NULL,
    apellido       VARCHAR(80)      NOT NULL,
    email          VARCHAR(120)     NOT NULL,
    telefono       VARCHAR(20)      NULL,
    password       VARCHAR(255)     NOT NULL COMMENT 'Hash bcrypt (RNF-02)',
    rol            ENUM('Administrador','Bibliotecario','Lector') NOT NULL DEFAULT 'Lector',
    estado         ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_registro TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    INDEX idx_usuarios_rol (rol),
    INDEX idx_usuarios_estado (estado)
) ENGINE = InnoDB COMMENT = 'Usuarios del sistema: administradores, bibliotecarios y lectores';

-- ────────────────────────────────────────────────
-- TABLA categorias  (RF-08: búsqueda por categoría)
-- ────────────────────────────────────────────────
CREATE TABLE categorias (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE = InnoDB COMMENT = 'Categorías de clasificación de los libros';

-- ────────────────────────────────────────────────
-- TABLA libros  (RF-06 … RF-09, RF-13)
-- ────────────────────────────────────────────────
CREATE TABLE libros (
    id                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    titulo               VARCHAR(150)  NOT NULL,
    autor                VARCHAR(120)  NOT NULL,
    categoria_id         INT UNSIGNED  NOT NULL,
    isbn                 VARCHAR(20)   NOT NULL,
    editorial            VARCHAR(100)  NULL,
    anio_publicacion     SMALLINT      NULL,
    sinopsis             TEXT          NULL,
    portada              VARCHAR(120)  NULL COMMENT 'Nombre del archivo SVG en public/img/portadas',
    cantidad_total       INT UNSIGNED  NOT NULL DEFAULT 1 COMMENT 'Ejemplares registrados',
    cantidad_disponible  INT UNSIGNED  NOT NULL DEFAULT 1 COMMENT 'Ejemplares disponibles (RN-01)',
    fecha_registro       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_libros_isbn (isbn),
    INDEX idx_libros_titulo (titulo),
    INDEX idx_libros_autor (autor),
    CONSTRAINT fk_libros_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB COMMENT = 'Catálogo de libros con control de ejemplares';

-- ────────────────────────────────────────────────
-- TABLA prestamos  (RF-10 … RF-13 · RN-01, RN-02, RN-03)
-- ────────────────────────────────────────────────
CREATE TABLE prestamos (
    id                        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    libro_id                  INT UNSIGNED  NOT NULL,
    usuario_id                INT UNSIGNED  NOT NULL COMMENT 'Lector que recibe el libro',
    registrado_por            INT UNSIGNED  NULL COMMENT 'Bibliotecario/Admin que registra la operación',
    fecha_prestamo            DATE          NOT NULL,
    fecha_devolucion_esperada DATE          NOT NULL,
    fecha_devolucion_real     DATE          NULL,
    estado                    ENUM('Activo','Devuelto') NOT NULL DEFAULT 'Activo',
    observaciones             VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    INDEX idx_prestamos_estado (estado),
    INDEX idx_prestamos_fecha (fecha_prestamo),
    CONSTRAINT fk_prestamos_libro   FOREIGN KEY (libro_id)
        REFERENCES libros (id)   ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamos_biblio  FOREIGN KEY (registrado_por)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_prestamo_fechas CHECK (fecha_devolucion_esperada >= fecha_prestamo)
) ENGINE = InnoDB COMMENT = 'Préstamos y devoluciones de libros';

-- ══════════════════════════════════════════════════
--  DATOS DE DEMOSTRACIÓN
-- ══════════════════════════════════════════════════

-- Usuarios  ·  contraseñas: admin123 / biblio123 / lector123
INSERT INTO usuarios (nombre, apellido, email, telefono, password, rol, estado) VALUES
('Ana María',   'Rodríguez',    'admin@cora.com',   '3104567890', '$2y$10$x5wj3drOJ7EFUO2/.bzvIexuLfxYRKPbpQHt1jA/vZV8iyAksq5f6', 'Administrador', 'Activo'),
('Carlos',      'Gómez',        'biblio@cora.com',  '3115678901', '$2y$10$kHi5Alj0ZyCA6IAiZMhk4eWG4SskK842/VwhIMGdV/3sgRou.GweW', 'Bibliotecario', 'Activo'),
('Laura',       'Ruiz',         'lector@cora.com',  '3126789012', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Activo'),
('Juan',        'Pérez',        'juan@cora.com',    '3137890123', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Activo'),
('María',       'Camargo',      'maria@cora.com',   '3148901234', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Activo'),
('Sebastián',   'Torres',       'sebas@cora.com',   '3159012345', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Activo'),
('Valentina',   'Ochoa',        'valen@cora.com',   '3160123456', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Activo'),
('Andrés',      'Muñoz',        'andres@cora.com',  '3171234567', '$2y$10$jOD8Vv3iqQ5CeKC98cf3Se875gT/7GdecSUoOj085sUFr8WkWiEY2', 'Lector',        'Inactivo');

-- Categorías (clasificación usada por la biblioteca)
INSERT INTO categorias (id, nombre) VALUES
(1, 'Literatura'),
(2, 'Ciencia Ficción'),
(3, 'Tecnología'),
(4, 'Historia'),
(5, 'Infantil'),
(6, 'Educativo');

-- Libros del catálogo
INSERT INTO libros (titulo, autor, categoria_id, isbn, editorial, anio_publicacion, sinopsis, portada, cantidad_total, cantidad_disponible) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 1, '978-0-307-47472-8', 'Sudamericana', 1967, 'La historia de la familia Buendía a lo largo de siete generaciones en el mítico pueblo de Macondo. Una obra cumbre del realismo mágico que narra el amor, la soledad y el destino de toda una estirpe.', 'cien-anos-de-soledad.svg', 5, 5),
('El amor en los tiempos del cólera', 'Gabriel García Márquez', 1, '978-0-307-47727-9', 'Sudamericana', 1985, 'Florentino Ariza espera medio siglo para reencontrarse con Fermina Daza, su amor de juventud. Una novela sobre la persistencia del amor y el paso del tiempo en una ciudad portuaria del Caribe.', 'el-amor-en-los-tiempos.svg', 4, 4),
('Rayuela', 'Julio Cortázar', 1, '978-84-376-0233-6', 'Sudamericana', 1963, 'Horacio Oliveira va y viene entre París y Buenos Aires en una novela que puede leerse en varios órdenes. Un juego literario fundamental de la literatura latinoamericana.', 'rayuela.svg', 3, 3),
('Dune', 'Frank Herbert', 2, '978-0-441-17271-9', 'Chilton Books', 1965, 'En el planeta desértico Arrakis se libra una lucha por la especia melange, la sustancia más valiosa del universo. Paul Atreides deberá convertirse en el líder que el destino le exige.', 'dune.svg', 4, 4),
('Fundación', 'Isaac Asimov', 2, '978-0-553-29335-0', 'Gnome Press', 1951, 'Hari Seldon predice la caída del Imperio Galáctico y crea la Fundación para preservar el conocimiento de la civilización. La primera obra de la saga que definió la ciencia ficción moderna.', 'fundacion.svg', 3, 3),
('Clean Code', 'Robert C. Martin', 3, '978-0-13-235088-4', 'Prentice Hall', 2008, 'Un manual de referencia sobre cómo escribir código legible, mantenible y profesional. Con ejemplos prácticos en Java sobre nombres, funciones, comentarios y diseño de clases.', 'clean-code.svg', 6, 6),
('PHP y MySQL: Desarrollo Web', 'Luke Welling y Laura Thomson', 3, '978-84-415-2988-4', 'Anaya Multimedia', 2017, 'Guía completa para construir aplicaciones web dinámicas con PHP y MySQL: formularios, sesiones, seguridad, bases de datos y arquitectura de aplicaciones profesionales.', 'php-mysql.svg', 5, 5),
('Introducción a los algoritmos', 'Thomas H. Cormen', 3, '978-0-262-04630-5', 'MIT Press', 2009, 'El texto de referencia mundial sobre algoritmos: ordenamiento, estructuras de datos, grafos, programación dinámica y análisis de complejidad, con ejercicios y rigor matemático.', 'algoritmos.svg', 4, 4),
('Sapiens: De animales a dioses', 'Yuval Noah Harari', 4, '978-987-637-530-8', 'Debate', 2011, 'Un recorrido por la historia de la humanidad desde la sabana africana hasta la revolución tecnológica: cómo el Homo sapiens llegó a dominar el planeta y qué rumbo toma su futuro.', 'sapiens.svg', 6, 6),
('Historia de Colombia', 'Jorge Orlando Melo', 4, '978-958-42-5431-7', 'Debate', 2020, 'Síntesis rigurosa y amena del proceso histórico colombiano: la colonia, la independencia, los partidos, la violencia y los desafíos del país contemporáneo.', 'historia-colombia.svg', 4, 4),
('El principito', 'Antoine de Saint-Exupéry', 5, '978-84-9804-871-8', 'Salamandra', 1943, 'Un aviador varado en el desierto conoce a un pequeño príncipe que viene de otro planeta. Una fábula atemporal sobre la amistad, el amor y lo esencial que solo se ve con el corazón.', 'el-principito.svg', 8, 8),
('Matemáticas esenciales para programadores', 'Zamir Bello', 6, '978-958-774-123-4', 'SENA Editorial', 2022, 'Fundamentos de lógica, álgebra y estadística aplicados al desarrollo de software. Material de apoyo para la formación técnica en programación de aplicaciones.', 'matematicas-esenciales.svg', 4, 4);

-- ────────────────────────────────────────────────
-- Préstamos de demostración (fechas relativas a hoy)
-- Historial DEVUELTO de los últimos ~5 meses → alimenta gráficos
-- ────────────────────────────────────────────────
INSERT INTO prestamos (libro_id, usuario_id, registrado_por, fecha_prestamo, fecha_devolucion_esperada, fecha_devolucion_real, estado, observaciones) VALUES
-- Mes -5
(11, 3, 2, DATE_SUB(CURDATE(), INTERVAL 160 DAY), DATE_SUB(CURDATE(), INTERVAL 153 DAY), DATE_SUB(CURDATE(), INTERVAL 155 DAY), 'Devuelto', NULL),
(1,  4, 2, DATE_SUB(CURDATE(), INTERVAL 150 DAY), DATE_SUB(CURDATE(), INTERVAL 143 DAY), DATE_SUB(CURDATE(), INTERVAL 141 DAY), 'Devuelto', NULL),
(6,  5, 2, DATE_SUB(CURDATE(), INTERVAL 143 DAY), DATE_SUB(CURDATE(), INTERVAL 136 DAY), DATE_SUB(CURDATE(), INTERVAL 137 DAY), 'Devuelto', 'Devuelto en buen estado'),
-- Mes -4
(9,  6, 1, DATE_SUB(CURDATE(), INTERVAL 125 DAY), DATE_SUB(CURDATE(), INTERVAL 118 DAY), DATE_SUB(CURDATE(), INTERVAL 116 DAY), 'Devuelto', NULL),
(4,  3, 2, DATE_SUB(CURDATE(), INTERVAL 118 DAY), DATE_SUB(CURDATE(), INTERVAL 111 DAY), DATE_SUB(CURDATE(), INTERVAL 112 DAY), 'Devuelto', NULL),
(11, 7, 2, DATE_SUB(CURDATE(), INTERVAL 110 DAY), DATE_SUB(CURDATE(), INTERVAL 103 DAY), DATE_SUB(CURDATE(), INTERVAL 104 DAY), 'Devuelto', NULL),
(2,  5, 1, DATE_SUB(CURDATE(), INTERVAL 104 DAY), DATE_SUB(CURDATE(), INTERVAL 97 DAY),  DATE_SUB(CURDATE(), INTERVAL 95 DAY),  'Devuelto', NULL),
-- Mes -3
(10, 4, 2, DATE_SUB(CURDATE(), INTERVAL 90 DAY),  DATE_SUB(CURDATE(), INTERVAL 83 DAY),  DATE_SUB(CURDATE(), INTERVAL 81 DAY),  'Devuelto', NULL),
(5,  6, 2, DATE_SUB(CURDATE(), INTERVAL 82 DAY),  DATE_SUB(CURDATE(), INTERVAL 75 DAY),  DATE_SUB(CURDATE(), INTERVAL 77 DAY),  'Devuelto', 'Con retraso de 2 días'),
(1,  7, 2, DATE_SUB(CURDATE(), INTERVAL 74 DAY),  DATE_SUB(CURDATE(), INTERVAL 67 DAY),  DATE_SUB(CURDATE(), INTERVAL 66 DAY),  'Devuelto', NULL),
-- Mes -2
(12, 3, 1, DATE_SUB(CURDATE(), INTERVAL 60 DAY),  DATE_SUB(CURDATE(), INTERVAL 53 DAY),  DATE_SUB(CURDATE(), INTERVAL 52 DAY),  'Devuelto', NULL),
(9,  4, 2, DATE_SUB(CURDATE(), INTERVAL 55 DAY),  DATE_SUB(CURDATE(), INTERVAL 48 DAY),  DATE_SUB(CURDATE(), INTERVAL 46 DAY),  'Devuelto', NULL),
(3,  5, 2, DATE_SUB(CURDATE(), INTERVAL 46 DAY),  DATE_SUB(CURDATE(), INTERVAL 39 DAY),  DATE_SUB(CURDATE(), INTERVAL 40 DAY),  'Devuelto', NULL),
(11, 6, 2, DATE_SUB(CURDATE(), INTERVAL 40 DAY),  DATE_SUB(CURDATE(), INTERVAL 33 DAY),  DATE_SUB(CURDATE(), INTERVAL 31 DAY),  'Devuelto', NULL),
-- Mes -1
(6,  7, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY),  DATE_SUB(CURDATE(), INTERVAL 23 DAY),  DATE_SUB(CURDATE(), INTERVAL 22 DAY),  'Devuelto', NULL),
(7,  3, 1, DATE_SUB(CURDATE(), INTERVAL 25 DAY),  DATE_SUB(CURDATE(), INTERVAL 18 DAY),  DATE_SUB(CURDATE(), INTERVAL 15 DAY),  'Devuelto', NULL),
(8,  4, 2, DATE_SUB(CURDATE(), INTERVAL 18 DAY),  DATE_SUB(CURDATE(), INTERVAL 11 DAY),  DATE_SUB(CURDATE(), INTERVAL 10 DAY),  'Devuelto', NULL),
(9,  6, 2, DATE_SUB(CURDATE(), INTERVAL 12 DAY),  DATE_SUB(CURDATE(), INTERVAL 5 DAY),   DATE_SUB(CURDATE(), INTERVAL 4 DAY),   'Devuelto', NULL),
-- Préstamos ACTIVOS vigentes
(1,  3, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY),   DATE_ADD(CURDATE(), INTERVAL 4 DAY),   NULL, 'Activo', NULL),
(11, 4, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY),   DATE_ADD(CURDATE(), INTERVAL 6 DAY),   NULL, 'Activo', NULL),
(6,  5, 1, CURDATE(),                             DATE_ADD(CURDATE(), INTERVAL 7 DAY),   NULL, 'Activo', NULL),
-- Préstamos ACTIVOS VENCIDOS (no devueltos a tiempo)
(4,  7, 2, DATE_SUB(CURDATE(), INTERVAL 20 DAY),  DATE_SUB(CURDATE(), INTERVAL 13 DAY),  NULL, 'Activo', 'Recordatorio enviado por teléfono'),
(10, 6, 2, DATE_SUB(CURDATE(), INTERVAL 15 DAY),  DATE_SUB(CURDATE(), INTERVAL 8 DAY),   NULL, 'Activo', NULL),
(2,  5, 1, DATE_SUB(CURDATE(), INTERVAL 11 DAY),  DATE_SUB(CURDATE(), INTERVAL 4 DAY),   NULL, 'Activo', NULL);

-- ────────────────────────────────────────────────
-- Recalcular la disponibilidad real de cada libro:
-- disponible = total − préstamos activos   (integridad RN-01)
-- ────────────────────────────────────────────────
UPDATE libros l
SET l.cantidad_disponible = l.cantidad_total - (
    SELECT COUNT(*) FROM prestamos p
    WHERE p.libro_id = l.id AND p.estado = 'Activo'
);

-- ────────────────────────────────────────────────
-- Verificación rápida
-- ────────────────────────────────────────────────
SELECT 'Usuarios' AS tabla, COUNT(*) AS registros FROM usuarios
UNION ALL SELECT 'Categorías', COUNT(*) FROM categorias
UNION ALL SELECT 'Libros', COUNT(*) FROM libros
UNION ALL SELECT 'Préstamos', COUNT(*) FROM prestamos
UNION ALL SELECT '→ Activos', COUNT(*) FROM prestamos WHERE estado = 'Activo'
UNION ALL SELECT '→ Vencidos', COUNT(*) FROM prestamos
           WHERE estado = 'Activo' AND fecha_devolucion_esperada < CURDATE();

<?php
/**
 * DemoStore — almacén temporal en SESIÓN con datos de ejemplo.
 *
 * Sirve para que el FRONTEND funcione completo sin base de datos.
 * Cuando tengas tu BD (database/schema.sql), reemplaza las consultas
 * de los modelos Libro, Usuario y Prestamo por PDO. Las vistas y
 * controladores NO cambian.
 */
class DemoStore
{
    public static function iniciar(): void
    {
        if (isset($_SESSION['demo'])) {
            return;
        }
        $hoy = new DateTime('today');
        $f = fn(int $dias) => (clone $hoy)->modify(($dias >= 0 ? '+' : '') . $dias . ' days')->format('Y-m-d');

        $_SESSION['demo'] = [
            'usuarios' => [
                ['id' => 1, 'nombre' => 'Ana López',    'documento' => '1023456780', 'correo' => 'admin@cora.com',  'clave' => password_hash('admin123',  PASSWORD_DEFAULT), 'rol' => 'Administrador', 'estado' => 'Activo'],
                ['id' => 2, 'nombre' => 'Carlos Ruiz',  'documento' => '1034567890', 'correo' => 'biblio@cora.com', 'clave' => password_hash('biblio123', PASSWORD_DEFAULT), 'rol' => 'Bibliotecario',  'estado' => 'Activo'],
                ['id' => 3, 'nombre' => 'María Torres', 'documento' => '1045678901', 'correo' => 'lector@cora.com', 'clave' => password_hash('lector123', PASSWORD_DEFAULT), 'rol' => 'Lector',        'estado' => 'Activo'],
                ['id' => 4, 'nombre' => 'Juan Pérez',   'documento' => '1067890123', 'correo' => 'juan@cora.com',   'clave' => password_hash('juan123',   PASSWORD_DEFAULT), 'rol' => 'Lector',        'estado' => 'Inactivo'],
                ['id' => 5, 'nombre' => 'Sofía Gómez',  'documento' => '1089012345', 'correo' => 'sofia@cora.com',  'clave' => password_hash('sofia123',  PASSWORD_DEFAULT), 'rol' => 'Lector',        'estado' => 'Activo'],
            ],
            'libros' => [
                ['id' => 1, 'titulo' => 'Cien años de soledad', 'autor' => 'Gabriel García Márquez', 'categoria' => 'Novela',   'isbn' => '978-84-397-0646-7', 'ejemplares' => 10, 'disponibles' => 9, 'descripcion' => 'Obra maestra de la literatura latinoamericana que narra la historia de la familia Buendía en el mítico pueblo de Macondo.'],
                ['id' => 2, 'titulo' => '1984',                 'autor' => 'George Orwell',           'categoria' => 'Ciencia ficción', 'isbn' => '978-0-452-28423-4', 'ejemplares' => 5, 'disponibles' => 4, 'descripcion' => 'Distopía sobre un estado totalitario que vigila y controla todos los aspectos de la vida.'],
                ['id' => 3, 'titulo' => 'El principito',        'autor' => 'Antoine de Saint-Exupéry', 'categoria' => 'Infantil', 'isbn' => '978-0-15-601219-5', 'ejemplares' => 4, 'disponibles' => 0, 'descripcion' => 'Cuento poético sobre un pequeño príncipe que viaja por distintos planetas.'],
                ['id' => 4, 'titulo' => 'Don Quijote de la Mancha', 'autor' => 'Miguel de Cervantes', 'categoria' => 'Clásico', 'isbn' => '978-84-376-0494-7', 'ejemplares' => 6, 'disponibles' => 5, 'descripcion' => 'Las aventuras de un hidalgo que enloquece leyendo libros de caballerías.'],
                ['id' => 5, 'titulo' => 'Breve historia del tiempo', 'autor' => 'Stephen Hawking',     'categoria' => 'Ciencia',  'isbn' => '978-84-9892-004-3', 'ejemplares' => 3, 'disponibles' => 3, 'descripcion' => 'Divulgación científica sobre el origen y el destino del universo.'],
                ['id' => 6, 'titulo' => 'Sapiens',              'autor' => 'Yuval Noah Harari',       'categoria' => 'Historia', 'isbn' => '978-84-9992-424-2', 'ejemplares' => 4, 'disponibles' => 3, 'descripcion' => 'Recorrido por la historia de la humanidad desde la prehistoria hasta hoy.'],
                ['id' => 7, 'titulo' => 'La Odisea',            'autor' => 'Homero',                  'categoria' => 'Clásico',  'isbn' => '978-84-376-0420-6', 'ejemplares' => 3, 'disponibles' => 3, 'descripcion' => 'Poema épico sobre el regreso de Odiseo a Ítaca tras la guerra de Troya.'],
                ['id' => 8, 'titulo' => 'Introducción a la programación', 'autor' => 'Varios autores', 'categoria' => 'Tecnología', 'isbn' => '978-958-00-1234-5', 'ejemplares' => 5, 'disponibles' => 5, 'descripcion' => 'Fundamentos de lógica y programación para principiantes.'],
            ],
            'prestamos' => [
                ['id' => 1, 'usuario_id' => 3, 'libro_id' => 1, 'fecha_prestamo' => $f(-3),  'fecha_prevista' => $f(4),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 2, 'usuario_id' => 5, 'libro_id' => 2, 'fecha_prestamo' => $f(-10), 'fecha_prevista' => $f(-3), 'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 3, 'usuario_id' => 3, 'libro_id' => 3, 'fecha_prestamo' => $f(-5),  'fecha_prevista' => $f(2),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 4, 'usuario_id' => 4, 'libro_id' => 3, 'fecha_prestamo' => $f(-4),  'fecha_prevista' => $f(3),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 5, 'usuario_id' => 5, 'libro_id' => 3, 'fecha_prestamo' => $f(-2),  'fecha_prevista' => $f(5),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 6, 'usuario_id' => 3, 'libro_id' => 3, 'fecha_prestamo' => $f(-1),  'fecha_prevista' => $f(6),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 7, 'usuario_id' => 3, 'libro_id' => 4, 'fecha_prestamo' => $f(-20), 'fecha_prevista' => $f(-13), 'fecha_devolucion' => $f(-14), 'estado' => 'Devuelto'],
                ['id' => 8, 'usuario_id' => 5, 'libro_id' => 6, 'fecha_prestamo' => $f(-25), 'fecha_prevista' => $f(-18), 'fecha_devolucion' => $f(-19), 'estado' => 'Devuelto'],
                ['id' => 9, 'usuario_id' => 3, 'libro_id' => 6, 'fecha_prestamo' => $f(-2),  'fecha_prevista' => $f(5),  'fecha_devolucion' => null, 'estado' => 'Activo'],
                ['id' => 10, 'usuario_id' => 4, 'libro_id' => 4, 'fecha_prestamo' => $f(-1), 'fecha_prevista' => $f(6),  'fecha_devolucion' => null, 'estado' => 'Activo'],
            ],
        ];
    }

    /** Devuelve la "tabla" por referencia para poder modificarla. */
    public static function &tabla(string $nombre): array
    {
        self::iniciar();
        return $_SESSION['demo'][$nombre];
    }

    public static function siguienteId(string $nombre): int
    {
        $ids = array_column(self::tabla($nombre), 'id');
        return $ids ? max($ids) + 1 : 1;
    }

    public static function reiniciar(): void
    {
        unset($_SESSION['demo']);
    }
}
<?php
/** Modelo Libro — RF-06, RF-07, RF-08, RF-09, RF-13 */
class Libro
{
    public static function todos(string $q = '', string $categoria = ''): array
    {
        $libros = DemoStore::tabla('libros');
        $q = mb_strtolower(trim($q));
        return array_values(array_filter($libros, function ($l) use ($q, $categoria) {
            $okQ = $q === '' || str_contains(mb_strtolower($l['titulo'] . ' ' . $l['autor'] . ' ' . $l['categoria']), $q);
            $okC = $categoria === '' || $l['categoria'] === $categoria;
            return $okQ && $okC;
        }));
    }

    public static function buscar(int $id): ?array
    {
        foreach (DemoStore::tabla('libros') as $l) {
            if ($l['id'] === $id) return $l;
        }
        return null;
    }

    public static function categorias(): array
    {
        return ['Novela', 'Ciencia ficción', 'Infantil', 'Clásico', 'Ciencia', 'Historia', 'Tecnología', 'Literatura', 'Cuentos'];
    }

    public static function validar(array $d): array
    {
        $errores = [];
        if (mb_strlen(trim($d['titulo'] ?? '')) < 2)  $errores[] = 'El título es obligatorio.';
        if (mb_strlen(trim($d['autor'] ?? '')) < 2)   $errores[] = 'El autor es obligatorio.';
        if (!in_array($d['categoria'] ?? '', self::categorias(), true)) $errores[] = 'Selecciona una categoría válida.';
        if (!preg_match('/^[0-9\-Xx]{10,17}$/', trim($d['isbn'] ?? ''))) $errores[] = 'El ISBN debe tener entre 10 y 17 caracteres (números y guiones).';
        if (!ctype_digit((string)($d['ejemplares'] ?? '')) || (int)$d['ejemplares'] < 1) $errores[] = 'La cantidad de ejemplares debe ser un número mayor a 0.';
        return $errores;
    }

    public static function crear(array $d): void
    {
        $t = &DemoStore::tabla('libros');
        $n = (int) $d['ejemplares'];
        $t[] = [
            'id' => DemoStore::siguienteId('libros'),
            'titulo' => trim($d['titulo']), 'autor' => trim($d['autor']),
            'categoria' => $d['categoria'], 'isbn' => trim($d['isbn']),
            'ejemplares' => $n, 'disponibles' => $n,
            'descripcion' => trim($d['descripcion'] ?? ''),
        ];
    }

    public static function actualizar(int $id, array $d): void
    {
        $t = &DemoStore::tabla('libros');
        foreach ($t as &$l) {
            if ($l['id'] === $id) {
                $prestados = $l['ejemplares'] - $l['disponibles'];
                $nuevo = max((int) $d['ejemplares'], $prestados); // no bajar de los prestados
                $l['titulo'] = trim($d['titulo']); $l['autor'] = trim($d['autor']);
                $l['categoria'] = $d['categoria']; $l['isbn'] = trim($d['isbn']);
                $l['descripcion'] = trim($d['descripcion'] ?? '');
                $l['ejemplares'] = $nuevo;
                $l['disponibles'] = $nuevo - $prestados;
            }
        }
    }

    /** Solo se elimina si no tiene préstamos activos (RNF-06 integridad). */
    public static function eliminar(int $id): bool
    {
        foreach (DemoStore::tabla('prestamos') as $p) {
            if ($p['libro_id'] === $id && $p['estado'] === 'Activo') return false;
        }
        $t = &DemoStore::tabla('libros');
        $t = array_values(array_filter($t, fn($l) => $l['id'] !== $id));
        return true;
    }

    public static function ajustarDisponibles(int $id, int $delta): void
    {
        $t = &DemoStore::tabla('libros');
        foreach ($t as &$l) {
            if ($l['id'] === $id) $l['disponibles'] += $delta;
        }
    }
}
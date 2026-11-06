<?php
/** Modelo Prestamo — RF-10, RF-11, RF-12 · Reglas RN-01, RN-02, RN-03 */
class Prestamo
{
    /** Agrega nombre de lector, título de libro y estado "Vencido" calculado. */
    private static function enriquecer(array $p): array
    {
        $u = Usuario::buscar($p['usuario_id']);
        $l = Libro::buscar($p['libro_id']);
        $p['lector'] = $u['nombre'] ?? '—';
        $p['libro']  = $l['titulo'] ?? '—';
        $p['estado_visual'] = ($p['estado'] === 'Activo' && $p['fecha_prevista'] < date('Y-m-d')) ? 'Vencido' : $p['estado'];
        return $p;
    }

    /** $estado: '' | Activo | Vencido | Devuelto */
    public static function todos(string $estado = '', ?int $usuarioId = null): array
    {
        $lista = array_map([self::class, 'enriquecer'], DemoStore::tabla('prestamos'));
        $lista = array_filter($lista, function ($p) use ($estado, $usuarioId) {
            if ($usuarioId !== null && $p['usuario_id'] !== $usuarioId) return false;
            return $estado === '' || $p['estado_visual'] === $estado;
        });
        usort($lista, fn($a, $b) => $b['id'] <=> $a['id']);
        return array_values($lista);
    }

    public static function pendientes(): array
    {
        return array_values(array_filter(self::todos(), fn($p) => $p['estado'] === 'Activo'));
    }

    public static function buscar(int $id): ?array
    {
        foreach (DemoStore::tabla('prestamos') as $p) {
            if ($p['id'] === $id) return self::enriquecer($p);
        }
        return null;
    }

    /** RN-01 y RN-02. Devuelve lista de errores (vacía = éxito). */
    public static function crear(array $d): array
    {
        $errores = [];
        $usuario = Usuario::buscar((int) ($d['usuario_id'] ?? 0));
        $libro   = Libro::buscar((int) ($d['libro_id'] ?? 0));
        $fp = $d['fecha_prestamo'] ?? '';
        $fd = $d['fecha_prevista'] ?? '';

        if (!$usuario || $usuario['rol'] !== 'Lector' || $usuario['estado'] !== 'Activo') $errores[] = 'Selecciona un lector activo.';
        if (!$libro) $errores[] = 'Selecciona un libro registrado.';
        if (!strtotime($fp) || !strtotime($fd)) $errores[] = 'Las fechas no son válidas.';
        elseif ($fd <= $fp) $errores[] = 'La fecha de devolución debe ser posterior a la del préstamo.';
        if ($libro && $libro['disponibles'] < 1) $errores[] = 'El libro "' . $libro['titulo'] . '" no tiene ejemplares disponibles (RN-01).';

        if ($errores) return $errores;

        $t = &DemoStore::tabla('prestamos');
        $t[] = [
            'id' => DemoStore::siguienteId('prestamos'),
            'usuario_id' => $usuario['id'], 'libro_id' => $libro['id'],
            'fecha_prestamo' => $fp, 'fecha_prevista' => $fd,
            'fecha_devolucion' => null, 'estado' => 'Activo',
        ];
        Libro::ajustarDisponibles($libro['id'], -1);
        return [];
    }

    /** RN-03: no se puede devolver dos veces el mismo préstamo. */
    public static function devolver(int $id): bool
    {
        $t = &DemoStore::tabla('prestamos');
        foreach ($t as &$p) {
            if ($p['id'] === $id) {
                if ($p['estado'] === 'Devuelto') return false;
                $p['estado'] = 'Devuelto';
                $p['fecha_devolucion'] = date('Y-m-d');
                Libro::ajustarDisponibles($p['libro_id'], +1);
                return true;
            }
        }
        return false;
    }

    // ---------- Indicadores para dashboard y reportes ----------
    public static function conteos(): array
    {
        $todos = self::todos();
        return [
            'libros'      => array_sum(array_column(DemoStore::tabla('libros'), 'ejemplares')),
            'titulos'     => count(DemoStore::tabla('libros')),
            'usuarios'    => count(DemoStore::tabla('usuarios')),
            'activos'     => count(array_filter($todos, fn($p) => $p['estado'] === 'Activo')),
            'vencidos'    => count(array_filter($todos, fn($p) => $p['estado_visual'] === 'Vencido')),
            'devueltos'   => count(array_filter($todos, fn($p) => $p['estado'] === 'Devuelto')),
            'total'       => count($todos),
            'disponibles' => array_sum(array_column(DemoStore::tabla('libros'), 'disponibles')),
        ];
    }

    /** Préstamos por mes (año actual). En la versión con BD: GROUP BY MONTH(fecha_prestamo). */
    public static function porMes(): array
    {
        $base = [8, 12, 15, 20, 18, 25, 30, 22, 28, 35, 40, 18]; // datos de ejemplo del histórico
        foreach (DemoStore::tabla('prestamos') as $p) {
            $mes = (int) date('n', strtotime($p['fecha_prestamo'])) - 1;
            $base[$mes]++;
        }
        return $base;
    }

    public static function porCategoria(): array
    {
        $cat = [];
        foreach (DemoStore::tabla('prestamos') as $p) {
            $l = Libro::buscar($p['libro_id']);
            if ($l) $cat[$l['categoria']] = ($cat[$l['categoria']] ?? 0) + 1;
        }
        arsort($cat);
        return $cat;
    }
}

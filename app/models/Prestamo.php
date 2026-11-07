<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Modelo Préstamo (RF-10 … RF-13)
 *
 * Reglas de negocio implementadas:
 *  · RN-01  No se presta un libro sin ejemplares disponibles (transacción con FOR UPDATE)
 *  · RN-02  Todo préstamo se asocia a un lector y un libro, con fechas y estado
 *  · RN-03  Al devolver: estado → "Devuelto" + se incrementa la disponibilidad.
 *           Un préstamo no puede devolverse dos veces.
 */

final class Prestamo
{
    /** Fecha de hoy */
    private string $hoy;

    public function __construct(private PDO $db)
    {
        $this->hoy = date('Y-m-d');
    }

    public static function instancia(): self
    {
        return new self(Database::getConnection());
    }

    /* ══════════════ CONSULTAS (RF-12) ══════════════ */

    /** SELECT base con joins; "estado_actual" deriva el Vencido al vuelo */
    private const SELECT_BASE =
        'SELECT p.id, p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real,
                p.estado, p.observaciones,
                l.id AS libro_id, l.titulo AS libro_titulo, l.autor AS libro_autor, l.portada,
                u.id AS lector_id, u.nombre AS lector_nombre, u.apellido AS lector_apellido, u.email AS lector_email,
                CASE
                    WHEN p.estado = "Activo" AND p.fecha_devolucion_esperada < :hoy1 THEN "Vencido"
                    ELSE p.estado
                END AS estado_actual,
                DATEDIFF(p.fecha_devolucion_esperada, :hoy2) AS dias_restantes
         FROM prestamos p
         INNER JOIN libros l   ON l.id = p.libro_id
         INNER JOIN usuarios u ON u.id = p.usuario_id';

    /**
     * Listado general con filtros (RF-12).
     *
     * @param string $estado  Todos | Activo | Vencido | Devuelto
     * @param string $q       Búsqueda por libro o lector
     * @param int    $lectorId > 0 → solo los préstamos de ese lector (HU-09)
     */
    public function buscarTodos(string $estado = 'Todos', string $q = '', int $lectorId = 0, int $pagina = 1, int $porPagina = REGISTROS_POR_PAGINA): array
    {
        [$where, $params] = $this->filtros($estado, $q, $lectorId);
        $offset = max(0, ($pagina - 1) * $porPagina);

        $sql = str_replace(':hoy2', ':hoy1', self::SELECT_BASE) . ' ' . $where .
               ' ORDER BY p.fecha_prestamo DESC, p.id DESC LIMIT :limite OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hoy1', $this->hoy);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** Total de préstamos según los mismos filtros (paginación) */
    public function contar(string $estado = 'Todos', string $q = '', int $lectorId = 0): int
    {
        [$where, $params] = $this->filtros($estado, $q, $lectorId);

        $sql = 'SELECT COUNT(*)
                FROM prestamos p
                INNER JOIN libros l   ON l.id = p.libro_id
                INNER JOIN usuarios u ON u.id = p.usuario_id ' . $where;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function filtros(string $estado, string $q, int $lectorId): array
    {
        $condiciones = [];
        $params      = [];

        if ($estado === 'Pendiente') {          // Activos + vencidos (módulo Devoluciones)
            $condiciones[]     = "p.estado = 'Activo'";
        } elseif ($estado === 'Activo') {       // Vigentes, aún no vencen
            $condiciones[]     = "p.estado = 'Activo' AND p.fecha_devolucion_esperada >= CURDATE()";
        } elseif ($estado === 'Vencido') {      // No devueltos a tiempo
            $condiciones[]     = "p.estado = 'Activo' AND p.fecha_devolucion_esperada < CURDATE()";
        } elseif ($estado === 'Devuelto') {
            $condiciones[]     = "p.estado = 'Devuelto'";
        }
        if ($q !== '') {
            $condiciones[]     = '(l.titulo LIKE :q OR l.autor LIKE :q OR u.nombre LIKE :q OR u.apellido LIKE :q)';
            $params[':q']      = '%' . $q . '%';
        }
        if ($lectorId > 0) {
            $condiciones[]     = 'p.usuario_id = :lector';
            $params[':lector'] = $lectorId;
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        return [$where, $params];
    }

    /** Detalle completo de un préstamo */
    public function buscarPorId(int $id): ?array
    {
        $sql  = str_replace(':hoy2', ':hoy1', self::SELECT_BASE) . ' WHERE p.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hoy1', $this->hoy);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    /** Préstamos pendientes de devolución: activos y vencidos (módulo Devoluciones) */
    public function pendientes(string $q = '', int $pagina = 1, int $porPagina = REGISTROS_POR_PAGINA): array
    {
        return $this->buscarTodos('Pendiente', $q, 0, $pagina, $porPagina);
    }

    /** Últimos préstamos para el dashboard */
    public function ultimos(int $cantidad = 5, int $lectorId = 0): array
    {
        $extra = $lectorId > 0 ? ' WHERE p.usuario_id = ' . (int) $lectorId : '';
        $sql   = str_replace(':hoy2', ':hoy1', self::SELECT_BASE) . $extra .
                 ' ORDER BY p.fecha_prestamo DESC, p.id DESC LIMIT :limite';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hoy1', $this->hoy);
        $stmt->bindValue(':limite', $cantidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ══════════════ CONTADORES ══════════════ */

    public function contadores(int $lectorId = 0): array
    {
        $filtroLector = $lectorId > 0 ? ' AND usuario_id = ' . (int) $lectorId : '';

        $sql = "SELECT
                    SUM(estado = 'Activo')                                                        AS activos,
                    SUM(estado = 'Activo' AND fecha_devolucion_esperada < CURDATE())              AS vencidos,
                    SUM(estado = 'Devuelto')                                                       AS devueltos,
                    COUNT(*)                                                                       AS total
                FROM prestamos WHERE 1 = 1 " . $filtroLector;

        $fila  = $this->db->query($sql)->fetch() ?: [];
        return [
            'activos'  => (int) ($fila['activos']  ?? 0),
            'vencidos' => (int) ($fila['vencidos'] ?? 0),
            'devueltos'=> (int) ($fila['devueltos']?? 0),
            'total'    => (int) ($fila['total']    ?? 0),
        ];
    }

    /* ══════════════ REGLAS DE NEGOCIO ══════════════ */

    /**
     * RF-10 · Registra un préstamo validando la RN-01.
     * Transacción con SELECT … FOR UPDATE para evitar condiciones de carrera.
     *
     * @return array [bool $ok, string $mensaje, int $id]
     */
    public function prestar(array $datos): array
    {
        $libroId   = (int) $datos['libro_id'];
        $lectorId  = (int) $datos['usuario_id'];
        $esperada  = $datos['fecha_devolucion_esperada'];
        $hoy       = $datos['fecha_prestamo'] ?: $this->hoy;

        /* RN-02 · Validaciones básicas */
        if ($libroId <= 0 || $lectorId <= 0) {
            return [false, 'Debe seleccionar un libro y un lector válidos (RN-02).', 0];
        }
        if ($esperada < $hoy) {
            return [false, 'La fecha de devolución no puede ser anterior a la fecha del préstamo.', 0];
        }

        try {
            $this->db->beginTransaction();

            /* Bloquea la fila del libro mientras se verifica el stock (RN-01) */
            $stmt = $this->db->prepare('SELECT titulo, cantidad_disponible FROM libros WHERE id = ? FOR UPDATE');
            $stmt->execute([$libroId]);
            $libro = $stmt->fetch();

            if (!$libro) {
                $this->db->rollBack();
                return [false, 'El libro seleccionado no existe.', 0];
            }
            if ((int) $libro['cantidad_disponible'] <= 0) {
                $this->db->rollBack();
                return [false, 'RN-01 · No hay ejemplares disponibles de «' . $libro['titulo'] . '». El préstamo fue rechazado.', 0];
            }

            /* Registra el préstamo (RN-02) */
            $stmt = $this->db->prepare(
                'INSERT INTO prestamos (libro_id, usuario_id, registrado_por, fecha_prestamo, fecha_devolucion_esperada, estado, observaciones)
                 VALUES (:libro, :lector, :biblio, :hoy, :esperada, "Activo", :obs)'
            );
            $stmt->execute([
                ':libro'    => $libroId,
                ':lector'   => $lectorId,
                ':biblio'   => $datos['registrado_por'] ?: null,
                ':hoy'      => $hoy,
                ':esperada' => $esperada,
                ':obs'      => $datos['observaciones'] ?: null,
            ]);
            $prestamoId = (int) $this->db->lastInsertId();

            /* Descuenta un ejemplar (RF-13) */
            $this->db->prepare('UPDATE libros SET cantidad_disponible = cantidad_disponible - 1 WHERE id = ?')
                     ->execute([$libroId]);

            $this->db->commit();
            return [true, 'Préstamo registrado correctamente. «' . $libro['titulo'] . '» debe devolverse el ' . date('d/m/Y', strtotime($esperada)) . '.', $prestamoId];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[CORA] Error al prestar: ' . $e->getMessage());
            return [false, 'No fue posible registrar el préstamo. Intente de nuevo.', 0];
        }
    }

    /**
     * RF-11 · Registra la devolución aplicando la RN-03:
     * estado → Devuelto, se incrementa la disponibilidad y no se puede devolver dos veces.
     */
    public function devolver(int $prestamoId, string $observaciones = ''): array
    {
        try {
            $this->db->beginTransaction();

            /* Bloquea el préstamo para evitar una doble devolución */
            $stmt = $this->db->prepare('SELECT id, libro_id, estado FROM prestamos WHERE id = ? FOR UPDATE');
            $stmt->execute([$prestamoId]);
            $prestamo = $stmt->fetch();

            if (!$prestamo) {
                $this->db->rollBack();
                return [false, 'El préstamo indicado no existe.'];
            }
            if ($prestamo['estado'] === 'Devuelto') {
                $this->db->rollBack(); // RN-03 · no se puede devolver dos veces
                return [false, 'RN-03 · Este préstamo ya fue devuelto anteriormente.'];
            }

            /* Cambia el estado y registra la fecha real de devolución */
            $this->db->prepare(
                "UPDATE prestamos
                 SET estado = 'Devuelto',
                     fecha_devolucion_real = CURDATE(),
                     observaciones = COALESCE(NULLIF(:obs, ''), observaciones)
                 WHERE id = :id"
            )->execute([':obs' => $observaciones, ':id' => $prestamoId]);

            /* Devuelve el ejemplar al inventario (RN-03) */
            $this->db->prepare('UPDATE libros SET cantidad_disponible = cantidad_disponible + 1 WHERE id = ?')
                     ->execute([$prestamo['libro_id']]);

            $this->db->commit();
            return [true, 'Devolución registrada: el ejemplar volvió al inventario y el préstamo quedó «Devuelto» (RN-03).'];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[CORA] Error al devolver: ' . $e->getMessage());
            return [false, 'No fue posible registrar la devolución. Intente de nuevo.'];
        }
    }

    /* ══════════════ ESTADÍSTICAS (RF-14) ══════════════ */

    /** Préstamos por mes de los últimos N meses (gráfico del dashboard) */
    public function porMes(int $meses = 6): array
    {
        $sql = "SELECT DATE_FORMAT(fecha_prestamo, '%Y-%m') AS mes, COUNT(*) AS total
                FROM prestamos
                WHERE fecha_prestamo >= DATE_SUB(CURDATE(), INTERVAL :meses MONTH)
                GROUP BY mes ORDER BY mes";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();

        /* Índice mes → total (ej. '2026-06' => 4) */
        $porClave = array_column($stmt->fetchAll(), 'total', 'mes');

        /* Completa los meses sin préstamos con 0 */
        $mesesCortos = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];

        $resultado = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $clave = date('Y-m', strtotime("-$i months"));
            $resultado[] = [
                'etiqueta' => $mesesCortos[(int) date('n', strtotime($clave . '-01'))] . ' ' . date('Y', strtotime($clave . '-01')),
                'total'    => (int) ($porClave[$clave] ?? 0),
            ];
        }

        return $resultado;
    }

    /** Datos para el módulo de reportes por rango de fechas */
    public function reportePorRango(string $desde, string $hasta): array
    {
        $rango = 'fecha_prestamo BETWEEN :desde AND :hasta';

        /* 1 · Resumen por estado */
        $sql = "SELECT
                    COUNT(*)                                                              AS total,
                    SUM(estado = 'Devuelto')                                              AS devueltos,
                    SUM(estado = 'Activo' AND fecha_devolucion_esperada >= CURDATE())      AS activos,
                    SUM(estado = 'Activo' AND fecha_devolucion_esperada <  CURDATE())      AS vencidos
                FROM prestamos WHERE $rango";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
        $resumen = $stmt->fetch() ?: [];

        /* 2 · Serie por día (gráfico de línea) */
        $sql = "SELECT fecha_prestamo AS dia, COUNT(*) AS total
                FROM prestamos WHERE $rango GROUP BY dia ORDER BY dia";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
        $porDia = $stmt->fetchAll();

        /* 3 · Top libros más prestados */
        $sql = "SELECT l.titulo, l.autor, COUNT(*) AS total
                FROM prestamos p INNER JOIN libros l ON l.id = p.libro_id
                WHERE $rango
                GROUP BY p.libro_id, l.titulo, l.autor
                ORDER BY total DESC, l.titulo LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);

        /* 4 · Top lectores más activos */
        $sql2 = "SELECT u.nombre, u.apellido, u.email, COUNT(*) AS total
                FROM prestamos p INNER JOIN usuarios u ON u.id = p.usuario_id
                WHERE p.fecha_prestamo BETWEEN :desde AND :hasta
                GROUP BY p.usuario_id, u.nombre, u.apellido, u.email
                ORDER BY total DESC, u.nombre LIMIT 5";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([':desde' => $desde, ':hasta' => $hasta]);

        return [
            'resumen' => [
                'total'     => (int) ($resumen['total']     ?? 0),
                'devueltos' => (int) ($resumen['devueltos'] ?? 0),
                'activos'   => (int) ($resumen['activos']   ?? 0),
                'vencidos'  => (int) ($resumen['vencidos']  ?? 0),
            ],
            'porDia'      => $porDia,
            'topLibros'   => $stmt->fetchAll(),
            'topLectores' => $stmt2->fetchAll(),
        ];
    }
}

<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Modelo Libro (RF-06 … RF-09, RF-13)
 * Gestión del catálogo y de la cantidad de ejemplares disponibles.
 */

final class Libro
{
    public function __construct(private PDO $db)
    {
    }

    public static function instancia(): self
    {
        return new self(Database::getConnection());
    }

    /**
     * Listado del catálogo con búsqueda y filtros (RF-07, RF-08).
     *
     * @param string $q       Búsqueda por título, autor o ISBN
     * @param int    $categoriaId  Filtro por categoría (0 = todas)
     */
    public function buscarTodos(string $q = '', int $categoriaId = 0, int $pagina = 1, int $porPagina = REGISTROS_POR_PAGINA): array
    {
        [$where, $params] = $this->filtros($q, $categoriaId);
        $offset = max(0, ($pagina - 1) * $porPagina);

        $sql = 'SELECT l.*, c.nombre AS categoria
                FROM libros l
                INNER JOIN categorias c ON c.id = l.categoria_id ' .
                $where . '
                ORDER BY l.titulo ASC
                LIMIT :limite OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** Total de libros según los mismos filtros (paginación) */
    public function contar(string $q = '', int $categoriaId = 0): int
    {
        [$where, $params] = $this->filtros($q, $categoriaId);
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM libros l INNER JOIN categorias c ON c.id = l.categoria_id ' . $where
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Ficha técnica completa del libro (módulo 7) */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, c.nombre AS categoria
             FROM libros l
             INNER JOIN categorias c ON c.id = l.categoria_id
             WHERE l.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** RF-06 · Registra un libro nuevo */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO libros (titulo, autor, categoria_id, isbn, editorial, anio_publicacion, sinopsis, portada, cantidad_total, cantidad_disponible)
             VALUES (:titulo, :autor, :categoria_id, :isbn, :editorial, :anio, :sinopsis, :portada, :total, :disponible)'
        );
        $stmt->execute([
            ':titulo'      => $datos['titulo'],
            ':autor'       => $datos['autor'],
            ':categoria_id'=> $datos['categoria_id'],
            ':isbn'        => $datos['isbn'],
            ':editorial'   => $datos['editorial'] ?: null,
            ':anio'        => $datos['anio_publicacion'] ?: null,
            ':sinopsis'    => $datos['sinopsis'] ?: null,
            ':portada'     => $datos['portada'] ?: null,
            ':total'       => $datos['cantidad_total'],
            ':disponible'  => $datos['cantidad_total'], // Al registrarse, todos los ejemplares están disponibles
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** RF-09 · Actualiza un libro (no reduce la disponibilidad por debajo de los préstamos activos) */
    public function actualizar(int $id, array $datos): bool
    {
        // Préstamos activos que mantienen ejemplares fuera de la biblioteca
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prestamos WHERE libro_id = ? AND estado = 'Activo'");
        $stmt->execute([$id]);
        $prestados  = (int) $stmt->fetchColumn();
        $disponible = max(0, $datos['cantidad_total'] - $prestados);

        $sql = 'UPDATE libros
                SET titulo = :titulo, autor = :autor, categoria_id = :categoria_id, isbn = :isbn,
                    editorial = :editorial, anio_publicacion = :anio, sinopsis = :sinopsis,
                    portada = :portada, cantidad_total = :total, cantidad_disponible = :disponible
                WHERE id = :id';

        return $this->db->prepare($sql)->execute([
            ':titulo'       => $datos['titulo'],
            ':autor'        => $datos['autor'],
            ':categoria_id' => $datos['categoria_id'],
            ':isbn'         => $datos['isbn'],
            ':editorial'    => $datos['editorial'] ?: null,
            ':anio'         => $datos['anio_publicacion'] ?: null,
            ':sinopsis'     => $datos['sinopsis'] ?: null,
            ':portada'      => $datos['portada'] ?: null,
            ':total'        => $datos['cantidad_total'],
            ':disponible'   => $disponible,
            ':id'           => $id,
        ]);
    }

    /** Elimina un libro solo si no tiene préstamos registrados (integridad RNF-06) */
    public function eliminar(int $id): bool
    {
        try {
            return $this->db->prepare('DELETE FROM libros WHERE id = ?')->execute([$id]);
        } catch (PDOException) {
            return false; // FK: el libro tiene historial de préstamos
        }
    }

    public function tienePrestamos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM prestamos WHERE libro_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /** Libros con ejemplares disponibles para el modal de préstamo (RN-01) */
    public function disponiblesParaPrestamo(): array
    {
        return $this->db->query(
            'SELECT id, titulo, autor, cantidad_disponible
             FROM libros
             WHERE cantidad_disponible > 0
             ORDER BY titulo'
        )->fetchAll();
    }

    /** ── Estadísticas para el dashboard ── */

    public function contarTodos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM libros')->fetchColumn();
    }

    public function totalEjemplares(): int
    {
        return (int) $this->db->query('SELECT COALESCE(SUM(cantidad_total), 0) FROM libros')->fetchColumn();
    }

    public function totalDisponibles(): int
    {
        return (int) $this->db->query('SELECT COALESCE(SUM(cantidad_disponible), 0) FROM libros')->fetchColumn();
    }

    /* ── Privados ── */

    private function filtros(string $q, int $categoriaId): array
    {
        $condiciones = [];
        $params      = [];

        if ($q !== '') {
            $condiciones[] = '(l.titulo LIKE :q OR l.autor LIKE :q OR l.isbn LIKE :q)';
            $params[':q']  = '%' . $q . '%';
        }
        if ($categoriaId > 0) {
            $condiciones[]           = 'l.categoria_id = :cat';
            $params[':cat']          = $categoriaId;
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        return [$where, $params];
    }
}

<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Modelo Categoría
 * Clasificación de los libros (RF-08: búsqueda por categoría)
 */

final class Categoria
{
    public function __construct(private PDO $db)
    {
    }

    public static function instancia(): self
    {
        return new self(Database::getConnection());
    }

    /** Todas las categorías ordenadas alfabéticamente */
    public function todas(): array
    {
        return $this->db->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre FROM categorias WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Libros por categoría para gráficos del dashboard y reportes */
    public function conteoLibros(): array
    {
        return $this->db->query(
            'SELECT c.nombre, COUNT(l.id) AS total
             FROM categorias c
             LEFT JOIN libros l ON l.categoria_id = c.id
             GROUP BY c.id, c.nombre
             ORDER BY total DESC, c.nombre'
        )->fetchAll();
    }
}

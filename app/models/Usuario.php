<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Modelo Usuario (RF-03, RF-04, RF-05)
 * Acceso a datos mediante PDO con consultas preparadas.
 */

final class Usuario
{
    public function __construct(private PDO $db)
    {
    }

    public static function instancia(): self
    {
        return new self(Database::getConnection());
    }

    /**
     * Listado con búsqueda y filtros (RF-04).
     *
     * @param string $q     Búsqueda por nombre, apellido o correo
     * @param string $rol   Rol exacto o '' para todos
     * @param string $estado Estado exacto o '' para todos
     */
    public function buscarTodos(string $q = '', string $rol = '', string $estado = '', int $pagina = 1, int $porPagina = REGISTROS_POR_PAGINA): array
    {
        [$where, $params] = $this->filtros($q, $rol, $estado);
        $offset = max(0, ($pagina - 1) * $porPagina);

        $sql = 'SELECT id, nombre, apellido, email, telefono, rol, estado, fecha_registro
                FROM usuarios ' . $where . '
                ORDER BY fecha_registro DESC, id DESC
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

    /** Total de registros según los mismos filtros (paginación) */
    public function contar(string $q = '', string $rol = '', string $estado = ''): int
    {
        [$where, $params] = $this->filtros($q, $rol, $estado);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios ' . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Contadores para el dashboard */
    public function contarTodos(bool $soloActivos = false): int
    {
        $sql    = 'SELECT COUNT(*) FROM usuarios' . ($soloActivos ? " WHERE estado = 'Activo'" : '');
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, apellido, email, telefono, rol, estado, fecha_registro FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, email FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Usuarios activos para el formulario de préstamo (RN-02) */
    public function activosParaPrestamo(): array
    {
        return $this->db->query(
            "SELECT id, nombre, apellido, email, rol
             FROM usuarios
             WHERE estado = 'Activo'
             ORDER BY nombre, apellido"
        )->fetchAll();
    }

    /** RF-03 · Registra un usuario (la contraseña llega cifrada) */
    public function crear(array $datos, string $hash): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, apellido, email, telefono, password, rol, estado)
             VALUES (:nombre, :apellido, :email, :telefono, :password, :rol, :estado)'
        );
        $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':email'    => $datos['email'],
            ':telefono' => $datos['telefono'] ?: null,
            ':password' => $hash,
            ':rol'      => $datos['rol'],
            ':estado'   => $datos['estado'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** RF-05 · Actualiza los datos de un usuario */
    public function actualizar(int $id, array $datos, ?string $hash = null): bool
    {
        $sql = 'UPDATE usuarios
                SET nombre = :nombre, apellido = :apellido, email = :email,
                    telefono = :telefono, rol = :rol, estado = :estado'
            . ($hash ? ', password = :password' : '')
            . ' WHERE id = :id';

        $params = [
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':email'    => $datos['email'],
            ':telefono' => $datos['telefono'] ?: null,
            ':rol'      => $datos['rol'],
            ':estado'   => $datos['estado'],
            ':id'       => $id,
        ];
        if ($hash) {
            $params[':password'] = $hash;
        }

        return $this->db->prepare($sql)->execute($params);
    }

    /** Activa o desactiva una cuenta (módulo de usuarios) */
    public function cambiarEstado(int $id, string $estado): bool
    {
        return $this->db->prepare('UPDATE usuarios SET estado = ? WHERE id = ?')->execute([$estado, $id]);
    }

    /** Elimina un usuario solo si no tiene préstamos activos (integridad RNF-06) */
    public function eliminar(int $id): bool
    {
        try {
            return $this->db->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
        } catch (PDOException) {
            return false; // Restricción de llave foránea: tiene historial de préstamos
        }
    }

    public function tienePrestamosActivos(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND estado = 'Activo'");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /* ── Privados ── */

    /** Construye la cláusula WHERE dinámica con los filtros seguros */
    private function filtros(string $q, string $rol, string $estado): array
    {
        $condiciones = [];
        $params      = [];

        if ($q !== '') {
            $condiciones[]       = '(nombre LIKE :q OR apellido LIKE :q OR email LIKE :q OR CONCAT(nombre, " ", apellido) LIKE :q)';
            $params[':q']        = '%' . $q . '%';
        }
        if ($rol !== '') {
            $condiciones[]       = 'rol = :rol';
            $params[':rol']      = $rol;
        }
        if ($estado !== '') {
            $condiciones[]       = 'estado = :estado';
            $params[':estado']   = $estado;
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        return [$where, $params];
    }
}

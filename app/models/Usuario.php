<?php
/** Modelo Usuario — RF-01, RF-03, RF-04, RF-05 */
class Usuario
{
    public const ROLES = ['Administrador', 'Bibliotecario', 'Lector'];

    public static function autenticar(string $correo, string $clave): ?array
    {
        foreach (DemoStore::tabla('usuarios') as $u) {
            if (strcasecmp($u['correo'], $correo) === 0 && $u['estado'] === 'Activo' && password_verify($clave, $u['clave'])) {
                return $u;
            }
        }
        return null;
    }

    public static function todos(string $q = ''): array
    {
        $q = mb_strtolower(trim($q));
        return array_values(array_filter(DemoStore::tabla('usuarios'), function ($u) use ($q) {
            return $q === '' || str_contains(mb_strtolower($u['nombre'] . ' ' . $u['documento'] . ' ' . $u['correo']), $q);
        }));
    }

    public static function lectoresActivos(): array
    {
        return array_values(array_filter(DemoStore::tabla('usuarios'), fn($u) => $u['rol'] === 'Lector' && $u['estado'] === 'Activo'));
    }

    public static function buscar(int $id): ?array
    {
        foreach (DemoStore::tabla('usuarios') as $u) {
            if ($u['id'] === $id) return $u;
        }
        return null;
    }

    public static function validar(array $d, ?int $idActual = null): array
    {
        $errores = [];
        if (mb_strlen(trim($d['nombre'] ?? '')) < 3) $errores[] = 'El nombre es obligatorio (mínimo 3 caracteres).';
        if (!preg_match('/^[0-9]{6,12}$/', trim($d['documento'] ?? ''))) $errores[] = 'El documento debe tener entre 6 y 12 dígitos.';
        if (!filter_var($d['correo'] ?? '', FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingresa un correo válido.';
        if (!in_array($d['rol'] ?? '', self::ROLES, true)) $errores[] = 'Selecciona un rol válido.';
        if ($idActual === null && strlen($d['clave'] ?? '') < 6) $errores[] = 'La contraseña debe tener mínimo 6 caracteres.';
        foreach (DemoStore::tabla('usuarios') as $u) {
            if ($u['id'] !== $idActual && strcasecmp($u['correo'], trim($d['correo'] ?? '')) === 0) $errores[] = 'Ya existe un usuario con ese correo.';
            if ($u['id'] !== $idActual && $u['documento'] === trim($d['documento'] ?? '')) $errores[] = 'Ya existe un usuario con ese documento.';
        }
        return $errores;
    }

    public static function crear(array $d): void
    {
        $t = &DemoStore::tabla('usuarios');
        $t[] = [
            'id' => DemoStore::siguienteId('usuarios'),
            'nombre' => trim($d['nombre']), 'documento' => trim($d['documento']),
            'correo' => trim($d['correo']), 'clave' => password_hash($d['clave'], PASSWORD_DEFAULT),
            'rol' => $d['rol'], 'estado' => 'Activo',
        ];
    }

    public static function actualizar(int $id, array $d): void
    {
        $t = &DemoStore::tabla('usuarios');
        foreach ($t as &$u) {
            if ($u['id'] === $id) {
                $u['nombre'] = trim($d['nombre']); $u['documento'] = trim($d['documento']);
                $u['correo'] = trim($d['correo']); $u['rol'] = $d['rol'];
                if (!empty($d['clave'])) $u['clave'] = password_hash($d['clave'], PASSWORD_DEFAULT);
            }
        }
    }

    /** Activar / desactivar usuario (módulo usuarios del alcance). */
    public static function cambiarEstado(int $id): void
    {
        $t = &DemoStore::tabla('usuarios');
        foreach ($t as &$u) {
            if ($u['id'] === $id) $u['estado'] = $u['estado'] === 'Activo' ? 'Inactivo' : 'Activo';
        }
    }
}
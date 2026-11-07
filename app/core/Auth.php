<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Autenticación y control de roles (RF-01, RF-02, RNF-02)
 * Roles: Administrador, Bibliotecario, Lector
 */

final class Auth
{
    /**
     * Intenta iniciar sesión con correo y contraseña.
     * Devuelve [bool $ok, string $mensaje, ?array $usuario]
     */
    public static function attempt(string $email, string $password): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return [false, 'El correo o la contraseña son incorrectos.', null];
        }
        if ($usuario['estado'] !== 'Activo') {
            return [false, 'Su cuenta se encuentra inactiva. Contacte al administrador.', null];
        }

        unset($usuario['password']);
        return [true, '¡Bienvenido(a) a la Biblioteca CORA!', $usuario];
    }

    /** Crea la sesión del usuario (regenera el ID → previene fijación de sesión) */
    public static function login(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
    }

    /** Cierra la sesión de forma segura (RF-02) */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
    }

    /* ── Datos del usuario autenticado ── */

    public static function check(): bool   { return isset($_SESSION['usuario']); }
    public static function user(): ?array  { return $_SESSION['usuario'] ?? null; }
    public static function id(): int       { return (int) ($_SESSION['usuario']['id'] ?? 0); }
    public static function rol(): string   { return $_SESSION['usuario']['rol'] ?? ''; }

    public static function nombre(): string
    {
        return trim(($_SESSION['usuario']['nombre'] ?? '') . ' ' . ($_SESSION['usuario']['apellido'] ?? ''));
    }

    public static function es(string ...$roles): bool
    {
        return in_array(self::rol(), $roles, true);
    }

    /* ── Guardas de acceso ── */

    /** Exige sesión iniciada */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::warning('Debe iniciar sesión para acceder al sistema.');
            redirect('login');
        }
    }

    /** Exige uno de los roles indicados */
    public static function requireRole(array $roles, string $mensaje = 'No tiene permisos para acceder a esta sección.'): void
    {
        self::requireLogin();
        if (!in_array(self::rol(), $roles, true)) {
            Flash::error($mensaje);
            redirect('dashboard');
        }
    }

    /* ── Protección CSRF ── */

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return $token !== '' && hash_equals($_SESSION['_csrf'] ?? '', $token);
    }
}

<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Sistema Web de Gestión Bibliotecaria
 * Front Controller + Enrutador principal
 * PHP 8 · Arquitectura MVC · PDO · Bootstrap 5
 * Proyecto académico SENA – ADSO
 *
 * Flujo:  Navegador → index.php → Controlador → Modelo (PDO) → Vista
 * Rutas:  /controlador/metodo/parametros   (ej: /libros/editar/3)
 */

/* ────────────────────────────────────────────────
 * 1. Carga de configuración y núcleo MVC
 * ──────────────────────────────────────────────── */
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/Flash.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/helpers/helpers.php';

/* Autocargador de modelos y controladores */
spl_autoload_register(function (string $clase): void {
    foreach (['app/models/', 'app/controllers/'] as $dir) {
        $archivo = __DIR__ . '/' . $dir . $clase . '.php';
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

/* ────────────────────────────────────────────────
 * 2. Servidor embebido de PHP (php -S):
 *    los archivos estáticos se sirven directamente
 * ──────────────────────────────────────────────── */
if (php_sapi_name() === 'cli-server') {
    $archivo = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($archivo)) {
        return false;
    }
}

/* ────────────────────────────────────────────────
 * 3. Sesión segura
 * ──────────────────────────────────────────────── */
session_name('CORASESSION');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/* ────────────────────────────────────────────────
 * 4. Ruta solicitada
 * ──────────────────────────────────────────────── */
$ruta      = $_GET['url'] ?? ($_SERVER['PATH_INFO'] ?? '');
$segmentos = array_values(array_filter(
    explode('/', trim((string) $ruta, '/')),
    fn (string $s): bool => $s !== ''
));

$controladorSlug = strtolower($segmentos[0] ?? 'dashboard');
$metodo          = $segmentos[1] ?? 'index';
$parametros      = array_slice($segmentos, 2);

/* ────────────────────────────────────────────────
 * 5. Tabla de rutas del sistema
 * ──────────────────────────────────────────────── */
$rutas = [
    // slug => [Controlador, método por defecto, requiere sesión iniciada]
    'login'        => ['AuthController', 'login',  false],
    'logout'       => ['AuthController', 'logout', true],
    'dashboard'    => ['DashboardController', 'index', true],
    'inicio'       => ['DashboardController', 'index', true],
    'libros'       => ['LibrosController', 'index', true],
    'usuarios'     => ['UsuariosController', 'index', true],
    'prestamos'    => ['PrestamosController', 'index', true],
    'devoluciones' => ['DevolucionesController', 'index', true],
    'reportes'     => ['ReportesController', 'index', true],
];

/* ────────────────────────────────────────────────
 * 6. Despacho de la petición
 * ──────────────────────────────────────────────── */
try {
    if (!isset($rutas[$controladorSlug])) {
        throw new RuntimeException('Página no encontrada', 404);
    }

    [$clase, $metodoPorDefecto, $requiereSesion] = $rutas[$controladorSlug];
    if (empty($segmentos[1])) {
        $metodo = $metodoPorDefecto;
    }

    /* Protección CSRF para todas las peticiones POST (RNF-02) */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Auth::verifyCsrf($_POST['_token'] ?? '')) {
        Flash::add('error', 'Token de seguridad inválido. Por favor intente de nuevo.');
        redirect('login');
    }

    /* Control de acceso por sesión y rol (RF-01 / RNF-02) */
    if ($requiereSesion) {
        Auth::requireLogin();
    } elseif ($controladorSlug === 'login' && Auth::check()) {
        redirect('dashboard');
    }

    $controlador = new $clase();

    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $metodo) || !method_exists($controlador, $metodo)) {
        throw new RuntimeException('Página no encontrada', 404);
    }

    call_user_func_array([$controlador, $metodo], $parametros);

} catch (Throwable $e) {
    http_response_code($e->getCode() === 404 ? 404 : 500);
    error_log('[CORA] ' . $e->getMessage());
    require __DIR__ . '/app/views/errors/404.php';
}

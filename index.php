<?php
/**
 * index.php — Front Controller (router) de Biblioteca CORA
 * Flujo MVC: Navegador -> index.php -> Controller -> Model -> View
 * URL: index.php?url=controlador/accion/id   (ej: index.php?url=libros/editar/3)
 */
session_start();

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/helpers.php';

// Cargar modelos
foreach (['DemoStore', 'Libro', 'Usuario', 'Prestamo'] as $m) {
    require_once APP_PATH . '/models/' . $m . '.php';
}
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/controllers/ErrorController.php';

// ---- Interpretar la URL ----
$partes = explode('/', trim($_GET['url'] ?? '', '/'));
$controlador = $partes[0] !== '' ? strtolower($partes[0]) : (esta_logueado() ? 'dashboard' : 'auth');
$accion      = $partes[1] ?? 'index';
$parametro   = $partes[2] ?? null;

// Rutas públicas (no requieren sesión)
$publicas = ['auth'];
if (!in_array($controlador, $publicas, true) && !esta_logueado()) {
    redirigir('auth/login');
}

// Protección CSRF para todos los POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valido()) {
    flash('error', 'La sesión del formulario expiró. Intenta de nuevo.');
    redirigir($controlador === 'auth' ? 'auth/login' : 'dashboard');
}

// ---- Despachar al controlador ----
$clase   = ucfirst($controlador) . 'Controller';
$archivo = APP_PATH . '/controllers/' . $clase . '.php';

if (!preg_match('/^[a-z]+$/', $controlador) || !is_file($archivo)) {
    (new ErrorController())->noEncontrado();
    exit;
}

require_once $archivo;
$obj = new $clase();

if (!preg_match('/^[a-zA-Z]+$/', $accion) || !method_exists($obj, $accion)) {
    (new ErrorController())->noEncontrado();
    exit;
}

$obj->$accion($parametro);

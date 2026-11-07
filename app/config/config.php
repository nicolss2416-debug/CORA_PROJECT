<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Configuración general de la aplicación
 */

/* Identidad de la aplicación */
define('APP_NAME',    'Biblioteca CORA');
define('APP_TAGLINE', 'Sistema Web de Gestión Bibliotecaria');
define('APP_VERSION', '1.0.0');
define('APP_PATH',    dirname(__DIR__));

/* ── Conexión a base de datos MySQL (valores por defecto de XAMPP) ── */
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'biblioteca_cora');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

/* ── Reglas de negocio ── */
define('DIAS_PRESTAMO', 7);         // Duración por defecto de un préstamo (RN-02)
define('REGISTROS_POR_PAGINA', 8);  // Registros por página en los listados

/* Zona horaria de la biblioteca */
date_default_timezone_set('America/Bogota');

/* ── URL base detectada automáticamente ──
 * Permite que el sistema funcione en cualquier subcarpeta de htdocs,
 * con Apache (mod_rewrite) o con el servidor embebido de PHP. */
$__protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$__host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__dir       = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
define('BASE_URL', $__protocolo . '://' . $__host . $__dir);

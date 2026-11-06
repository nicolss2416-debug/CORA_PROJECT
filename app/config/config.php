<?php
/**
 * Configuración general de Biblioteca CORA.
 * Para la conexión a MySQL (cuando conectes PDO) crea
 * app/config/database.local.php (ya está en .gitignore).
 */
define('APP_NAME', 'Biblioteca CORA');
define('APP_SLOGAN', 'Conocimiento que nos une');
define('APP_PATH', dirname(__DIR__));          // .../app
define('ROOT_PATH', dirname(APP_PATH));        // .../biblioteca-cora

// BASE_URL se detecta sola: http://localhost/biblioteca-cora/
$carpeta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $carpeta . '/');

// Días de préstamo por defecto (RN-02: fecha prevista de devolución)
define('DIAS_PRESTAMO', 7);

date_default_timezone_set('America/Bogota');
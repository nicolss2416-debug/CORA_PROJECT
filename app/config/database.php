<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Conexión a base de datos
 * Patrón Singleton con PDO (consultas preparadas → previene inyección SQL)
 */

final class Database
{
    private static ?PDO $instancia = null;

    /** Devuelve la conexión PDO única de la aplicación */
    public static function getConnection(): PDO
    {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Errores → excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Arreglos asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Consultas preparadas reales
            ];

            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            } catch (PDOException $e) {
                error_log('[CORA] Error de conexión BD: ' . $e->getMessage());
                http_response_code(500);
                exit('<div style="font-family:Arial,sans-serif;max-width:560px;margin:80px auto;padding:24px;border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:8px">'
                    . '<h2 style="margin-top:0">Error de conexión a la base de datos</h2>'
                    . '<p>No fue posible conectar con MySQL. Verifique que el servicio esté activo en XAMPP '
                    . 'y que la base de datos <b>' . DB_NAME . '</b> exista (importe <code>database/biblioteca_cora.sql</code>).</p></div>');
            }
        }

        return self::$instancia;
    }

    /** Evita instanciar la clase directamente */
    private function __construct() {}
    private function __clone() {}
}

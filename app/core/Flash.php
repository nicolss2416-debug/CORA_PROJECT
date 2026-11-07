<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Mensajes de estado (Módulo 10)
 * Alertas visuales dinámicas: éxito, error, advertencia e información.
 * Los mensajes sobreviven a una redirección mediante la sesión.
 */

final class Flash
{
    /** Registra un mensaje: éxito | error | advertencia | informacion */
    public static function add(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    /** Atajos semánticos */
    public static function success(string $m): void { self::add('exito', $m); }
    public static function error(string $m): void   { self::add('error', $m); }
    public static function warning(string $m): void { self::add('advertencia', $m); }
    public static function info(string $m): void    { self::add('informacion', $m); }

    /** Devuelve los mensajes pendientes y los limpia de la sesión */
    public static function all(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    /** Guarda los datos de un formulario para repoblarlo tras un error */
    public static function setOld(array $datos): void
    {
        unset($datos['_token'], $datos['password'], $datos['password_confirm']);
        $_SESSION['_old'] = $datos;
    }

    /** Devuelve (y limpia) los datos del último formulario enviado */
    public static function getOld(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
        return $old;
    }
}

<?php
/** Controlador base: utilidades comunes a todos los controladores. */
class BaseController
{
    /** Bloquea el acceso si el rol del usuario no está permitido (RNF-02). */
    protected function requerirRol(string ...$roles): void
    {
        if (!tiene_rol(...$roles)) {
            http_response_code(403);
            flash('warning', 'No tienes permisos para acceder a ese módulo.');
            redirigir('dashboard');
        }
    }

    protected function esPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function guardarViejo(array $datos): void
    {
        $_SESSION['viejo'] = $datos;
    }

    protected function limpiarViejo(): void
    {
        unset($_SESSION['viejo']);
    }
}
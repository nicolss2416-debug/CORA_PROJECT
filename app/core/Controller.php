<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador base MVC
 * Todas las vistas se renderizan dentro del layout principal:
 * header → sidebar → topbar → contenido → footer
 */

abstract class Controller
{
    /**
     * Renderiza una vista.
     *
     * @param string $vista      Ruta de la vista dentro de /app/views (ej. 'libros/index')
     * @param array  $datos      Variables extraídas para la vista
     * @param bool   $conLayout false → vista independiente (ej. login, errores)
     */
    protected function view(string $vista, array $datos = [], bool $conLayout = true): void
    {
        extract($datos, EXTR_SKIP);

        if ($conLayout) {
            require APP_PATH . '/views/layouts/header.php';
            require APP_PATH . '/views/layouts/sidebar.php';
            echo '<main class="cora-main">';
            require APP_PATH . '/views/layouts/topbar.php';
            echo '<div class="cora-content">';
            require APP_PATH . '/views/layouts/alert.php';
        }

        require APP_PATH . '/views/' . $vista . '.php';

        if ($conLayout) {
            echo '</div></main>';
            require APP_PATH . '/views/layouts/footer.php';
        }
    }
}

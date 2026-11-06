<?php
/** Página de error 404 (pantalla "Mensajes de error" del diseño). */
class ErrorController extends BaseController
{
    public function noEncontrado(): void
    {
        http_response_code(404);
        if (esta_logueado()) {
            layout('errores/404', ['titulo' => 'Página no encontrada']);
        } else {
            vista_simple('errores/404_publica', ['titulo' => 'Página no encontrada']);
        }
    }
}
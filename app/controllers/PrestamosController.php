<?php
/** Préstamos — RF-10, RF-12 (pantalla 8: modal de préstamo). El lector ve solo los suyos (HU-09). */
class PrestamosController extends BaseController
{
    public function index(): void
    {
        $estado = $_GET['estado'] ?? '';
        $soloMios = tiene_rol('Lector') ? usuario_actual()['id'] : null;

        layout('prestamos/index', [
            'titulo'    => tiene_rol('Lector') ? 'Mis préstamos' : 'Préstamos',
            'prestamos' => Prestamo::todos($estado, $soloMios),
            'estado'    => $estado,
            'lectores'  => Usuario::lectoresActivos(),
            'libros'    => Libro::todos(),
            'extraJs'   => ['public/js/prestamos.js'],
        ]);
    }

    public function guardar(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        if (!$this->esPost()) redirigir('prestamos');

        $errores = Prestamo::crear($_POST);
        if ($errores) {
            foreach ($errores as $err) flash('error', $err);
        } else {
            flash('success', 'El préstamo se registró correctamente.');
        }
        redirigir('prestamos');
    }
}
<?php
/** Devoluciones — RF-11 · RN-03 */
class DevolucionesController extends BaseController
{
    public function index(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        layout('devoluciones/index', [
            'titulo'     => 'Devoluciones',
            'pendientes' => Prestamo::pendientes(),
        ]);
    }

    public function registrar($id = null): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        if (!$this->esPost()) redirigir('devoluciones');

        if (Prestamo::devolver((int) $id)) {
            flash('success', 'La devolución se registró correctamente.');
        } else {
            flash('warning', 'Ese préstamo ya fue devuelto o no existe.');
        }
        redirigir('devoluciones');
    }
}
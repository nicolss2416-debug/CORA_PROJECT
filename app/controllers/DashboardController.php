<?php
/** Panel principal (pantalla 2) */
class DashboardController extends BaseController
{
    public function index(): void
    {
        // El lector solo ve su información de préstamos y el catálogo
        if (tiene_rol('Lector')) redirigir('prestamos');

        $recientes = array_slice(Prestamo::todos(), 0, 5);

        layout('dashboard/index', [
            'titulo'    => 'Panel principal',
            'conteos'   => Prestamo::conteos(),
            'recientes' => $recientes,
            'porMes'    => Prestamo::porMes(),
            'extraJs'   => ['https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', 'public/js/dashboard.js'],
        ]);
    }
}

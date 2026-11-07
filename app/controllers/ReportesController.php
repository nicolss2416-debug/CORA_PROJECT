<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de reportes (RF-14)
 * Panel analítico con filtros por rango de fechas, métricas y gráficos.
 */

final class ReportesController extends Controller
{
    /** GET /reportes?desde=YYYY-MM-DD&hasta=YYYY-MM-DD */
    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Bibliotecario'], 'Los reportes están disponibles para el personal de la biblioteca.');

        /* Rango de fechas: por defecto, los últimos 30 días */
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-29 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            Flash::warning('El rango de fechas indicado no es válido; se mostraron los últimos 30 días.');
            $desde = date('Y-m-d', strtotime('-29 days'));
            $hasta = date('Y-m-d');
        }
        if ($desde > $hasta) {
            Flash::warning('La fecha inicial no puede ser posterior a la fecha final; se intercambiaron.');
            [$desde, $hasta] = [$hasta, $desde];
        }

        $libroModelo    = Libro::instancia();
        $usuarioModelo  = Usuario::instancia();
        $prestamoModelo = Prestamo::instancia();

        $this->view('reportes/index', [
            'pageTitle'  => 'Reportes y estadísticas',
            'desde'      => $desde,
            'hasta'      => $hasta,
            'reporte'    => $prestamoModelo->reportePorRango($desde, $hasta),
            'inventario' => [
                'titulos'     => $libroModelo->contarTodos(),
                'ejemplares'  => $libroModelo->totalEjemplares(),
                'disponibles' => $libroModelo->totalDisponibles(),
                'usuarios'    => $usuarioModelo->contarTodos(),
                'lectores'    => $usuarioModelo->contarTodos(soloActivos: true),
            ],
            'porCategoria' => Categoria::instancia()->conteoLibros(),
        ]);
    }
}

<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador del panel principal (Dashboard)
 * Tarjetas de contadores + gráficos de estadísticas de préstamos.
 * El contenido se adapta al rol: personal de la biblioteca / lector.
 */

final class DashboardController extends Controller
{
    public function index(): void
    {
        $esLector  = Auth::es('Lector');
        $lectorId  = Auth::id();

        $libroModelo    = Libro::instancia();
        $usuarioModelo  = Usuario::instancia();
        $prestamoModelo = Prestamo::instancia();
        $categoriaModelo= Categoria::instancia();

        if ($esLector) {
            /* HU-05, HU-09 · El lector ve sus propios indicadores */
            $datos = [
                'pageTitle'    => 'Mis préstamos',
                'tarjetas'     => [
                    ['label' => 'Libros en el catálogo', 'valor' => $libroModelo->contarTodos(), 'icono' => 'bi-bookshelf',      'color' => 'primario', 'enlace' => 'libros'],
                    ['label' => 'Mis préstamos activos', 'valor' => $prestamoModelo->contadores($lectorId)['activos'], 'icono' => 'bi-journal-arrow-down', 'color' => 'exito',   'enlace' => 'prestamos'],
                    ['label' => 'Mis préstamos vencidos','valor' => $prestamoModelo->contadores($lectorId)['vencidos'],'icono' => 'bi-exclamation-triangle', 'color' => 'peligro', 'enlace' => 'prestamos'],
                ],
                'contadores'   => $prestamoModelo->contadores($lectorId),
                'ultimos'      => $prestamoModelo->ultimos(6, $lectorId),
                'porCategoria' => $categoriaModelo->conteoLibros(),
                'porMes'       => $prestamoModelo->porMes(6),
                'esLector'     => true,
            ];
        } else {
            /* Personal: visión general de la biblioteca */
            $contadoresPrestamos = $prestamoModelo->contadores();

            $datos = [
                'pageTitle'    => 'Panel principal',
                'tarjetas'     => [
                    ['label' => 'Libros totales',       'valor' => $libroModelo->contarTodos(),       'icono' => 'bi-bookshelf',          'color' => 'primario', 'enlace' => 'libros'],
                    ['label' => 'Usuarios registrados',  'valor' => $usuarioModelo->contarTodos(),     'icono' => 'bi-people',             'color' => 'acento',   'enlace' => 'usuarios'],
                    ['label' => 'Préstamos activos',     'valor' => $contadoresPrestamos['activos'],   'icono' => 'bi-journal-arrow-down',  'color' => 'exito',    'enlace' => 'prestamos'],
                    ['label' => 'Préstamos vencidos',    'valor' => $contadoresPrestamos['vencidos'],  'icono' => 'bi-exclamation-triangle','color' => 'peligro',  'enlace' => 'prestamos?estado=Vencido'],
                ],
                'contadores'   => $contadoresPrestamos + [
                    'ejemplares'  => $libroModelo->totalEjemplares(),
                    'disponibles' => $libroModelo->totalDisponibles(),
                    'lectores'    => $usuarioModelo->contarTodos(soloActivos: true),
                ],
                'ultimos'      => $prestamoModelo->ultimos(6),
                'porCategoria' => $categoriaModelo->conteoLibros(),
                'porMes'       => $prestamoModelo->porMes(6),
                'esLector'     => false,
            ];
        }

        $this->view('dashboard/index', $datos);
    }
}

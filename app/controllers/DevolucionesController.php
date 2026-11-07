<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de devoluciones
 * RF-11 Registrar devolución · RN-03: estado → Devuelto + devuelve el ejemplar
 * Identifica préstamos activos y vencidos pendientes de devolución.
 */

final class DevolucionesController extends Controller
{
    /** GET /devoluciones · Préstamos pendientes (activos y vencidos) */
    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Bibliotecario'], 'Solo el personal de la biblioteca puede gestionar devoluciones.');

        $q      = trim($_GET['q'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $modelo = Prestamo::instancia();
        $total  = $modelo->contar('Pendiente', $q);

        $pendientes = $modelo->pendientes($q, $pagina);

        $this->view('devoluciones/index', [
            'pageTitle'      => 'Devoluciones',
            'prestamos'      => $pendientes,
            'filtros'        => ['q' => $q],
            'pagina'         => $pagina,
            'totalPaginas'   => (int) ceil($total / REGISTROS_POR_PAGINA),
            'totalRegistros' => $total,
            'contadores'     => $modelo->contadores(),
        ]);
    }

    /** POST /devoluciones/registrar · Aplica la RN-03 sobre el préstamo indicado */
    public function registrar(): void
    {
        Auth::requireRole(['Administrador', 'Bibliotecario']);

        $prestamoId = (int) ($_POST['prestamo_id'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($prestamoId <= 0) {
            Flash::error('Debe indicar el préstamo a devolver.');
            redirect('devoluciones');
        }

        [$ok, $mensaje] = Prestamo::instancia()->devolver($prestamoId, $observaciones);

        $ok ? Flash::success($mensaje) : Flash::error($mensaje);   // RN-03: incluye anti doble devolución
        redirect('devoluciones');
    }
}

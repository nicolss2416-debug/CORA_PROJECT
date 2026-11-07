<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de préstamos
 * RF-10 Registrar préstamo · RF-12 Consultar préstamos · RF-13 Disponibilidad
 * RN-01 (stock) y RN-02 (asociación lector-libro-fechas) validadas en el modelo.
 */

final class PrestamosController extends Controller
{
    /** GET /prestamos · Listado con filtros por estado y búsqueda (RF-12) */
    public function index(): void
    {
        $esLector = Auth::es('Lector');
        $lectorId = $esLector ? Auth::id() : 0;

        $estado = in_array($_GET['estado'] ?? 'Todos', ['Todos', 'Activo', 'Vencido', 'Devuelto'], true)
            ? $_GET['estado'] : 'Todos';
        $q      = trim($_GET['q'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $modelo = Prestamo::instancia();
        $total  = $modelo->contar($estado, $q, $lectorId);

        $this->view('prestamos/index', [
            'pageTitle'  => $esLector ? 'Mis préstamos' : 'Gestión de préstamos',
            'prestamos'  => $modelo->buscarTodos($estado, $q, $lectorId, $pagina),
            'filtros'    => ['estado' => $estado, 'q' => $q],
            'pagina'     => $pagina,
            'totalPaginas' => (int) ceil($total / REGISTROS_POR_PAGINA),
            'totalRegistros' => $total,
            'contadores' => $modelo->contadores($lectorId),
            'esLector'   => $esLector,
            'esStaff'    => Auth::es('Administrador', 'Bibliotecario'),
            /* Datos del modal "Nuevo préstamo" (RF-10) */
            'librosDisponibles' => $esLector ? [] : Libro::instancia()->disponiblesParaPrestamo(),
            'lectoresActivos'   => $esLector ? [] : Usuario::instancia()->activosParaPrestamo(),
            'libroPreseleccionado' => (int) ($_GET['libro_id'] ?? 0),
            'abrirModal' => isset($_GET['nuevo']) || (int) ($_GET['libro_id'] ?? 0) > 0,
        ]);
    }

    /** POST /prestamos/guardar · Registra el préstamo desde el modal (RF-10, RN-01) */
    public function guardar(): void
    {
        Auth::requireRole(['Administrador', 'Bibliotecario'], 'Solo el personal de la biblioteca puede registrar préstamos.');

        $hoy      = date('Y-m-d');
        $defecto  = date('Y-m-d', strtotime('+' . DIAS_PRESTAMO . ' days'));

        $datos = [
            'libro_id'       => (int) ($_POST['libro_id'] ?? 0),
            'usuario_id'     => (int) ($_POST['usuario_id'] ?? 0),
            'fecha_prestamo' => ($_POST['fecha_prestamo'] ?? '') ?: $hoy,
            'fecha_devolucion_esperada' => ($_POST['fecha_devolucion_esperada'] ?? '') ?: $defecto,
            'observaciones'  => trim($_POST['observaciones'] ?? ''),
            'registrado_por' => Auth::id(),
        ];

        if ($datos['libro_id'] <= 0 || $datos['usuario_id'] <= 0) {
            Flash::error('Debe seleccionar el libro y el lector para registrar el préstamo (RN-02).');
            redirect('prestamos');
        }

        [$ok, $mensaje] = Prestamo::instancia()->prestar($datos);

        $ok ? Flash::success($mensaje) : Flash::error($mensaje);   // RN-01: mensaje si no hay ejemplares
        redirect('prestamos');
    }

    /**
     * POST /prestamos/directo/{libroId} · Módulo 7: botón "Solicitar préstamo"
     * en la ficha del libro. El lector toma prestado el libro por sí mismo.
     */
    public function directo(?string $id = null): void
    {
        $libroId = (int) $id;

        /* El staff también puede usarlo: presta al lector indicado en el formulario */
        if (Auth::es('Administrador', 'Bibliotecario')) {
            redirect('prestamos?nuevo=1&libro_id=' . $libroId);
        }

        [$ok, $mensaje] = Prestamo::instancia()->prestar([
            'libro_id'       => $libroId,
            'usuario_id'     => Auth::id(),                 // Lector autenticado (HU-07)
            'fecha_prestamo' => date('Y-m-d'),
            'fecha_devolucion_esperada' => date('Y-m-d', strtotime('+' . DIAS_PRESTAMO . ' days')),
            'observaciones'  => 'Préstamo directo desde la ficha del libro',
            'registrado_por' => null,
        ]);

        $ok ? Flash::success($mensaje) : Flash::error($mensaje);   // RN-01 controla el stock
        redirect('libros/detalle/' . $libroId);
    }
}

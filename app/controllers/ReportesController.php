<?php
/** Reportes — RF-14 (pantalla 9) */
class ReportesController extends BaseController
{
    private const TIPOS = [
        'prestamos' => 'Todos los préstamos',
        'Activo'    => 'Préstamos activos',
        'Vencido'   => 'Préstamos vencidos',
        'Devuelto'  => 'Devoluciones',
    ];

    private function filtrar(): array
    {
        $tipo  = $_GET['tipo'] ?? 'prestamos';
        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';
        $estado = $tipo === 'prestamos' ? '' : $tipo;

        $filas = Prestamo::todos($estado);
        if ($desde) $filas = array_filter($filas, fn($p) => $p['fecha_prestamo'] >= $desde);
        if ($hasta) $filas = array_filter($filas, fn($p) => $p['fecha_prestamo'] <= $hasta);
        return [array_values($filas), $tipo, $desde, $hasta];
    }

    public function index(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        [$filas, $tipo, $desde, $hasta] = $this->filtrar();

        layout('reportes/index', [
            'titulo'  => 'Reportes',
            'filas'   => $filas,
            'tipo'    => $tipo,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'tipos'   => self::TIPOS,
            'conteos' => Prestamo::conteos(),
            'porCategoria' => Prestamo::porCategoria(),
            'extraJs' => ['https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js', 'public/js/reportes.js'],
        ]);
    }

    /** Exporta el reporte filtrado a CSV (se abre en Excel). */
    public function exportar(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        [$filas] = $this->filtrar();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporte_cora_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM para tildes en Excel
        fputcsv($out, ['ID', 'Lector', 'Libro', 'Fecha préstamo', 'Fecha prevista', 'Fecha devolución', 'Estado'], ';');
        foreach ($filas as $p) {
            fputcsv($out, [$p['id'], $p['lector'], $p['libro'], $p['fecha_prestamo'], $p['fecha_prevista'], $p['fecha_devolucion'] ?? '', $p['estado_visual']], ';');
        }
        fclose($out);
    }
}
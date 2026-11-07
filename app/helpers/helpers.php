<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Funciones de ayuda globales
 * Usadas por controladores y vistas.
 */

/** Escapa texto para prevenir XSS (RNF-02) */
function e(null|string|int|float $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** URL absoluta de una ruta del sistema (ej. url('libros')) */
function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

/** URL de un recurso estático dentro de /public */
function asset(string $ruta): string
{
    return url('public/' . ltrim($ruta, '/'));
}

/** Redirección y corte de ejecución */
function redirect(string $ruta): never
{
    header('Location: ' . url($ruta));
    exit;
}

/** Campo oculto con el token CSRF para los formularios POST */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Auth::csrfToken()) . '">';
}

/** Valor anterior de un formulario (re-población tras errores) */
function old(string $campo, mixed $porDefecto = ''): mixed
{
    static $viejo = null;
    if ($viejo === null) {
        $viejo = Flash::getOld();
    }
    return $viejo[$campo] ?? $porDefecto;
}

/** Fecha en formato colombiano dd/mm/yyyy */
function formato_fecha(?string $fecha): string
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '—';
}

/** Fecha actual larga en español (ej. "sábado, 7 de noviembre de 2026") */
function fecha_larga_es(): string
{
    $dias  = ['Sunday' => 'domingo', 'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miércoles',
              'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sábado'];
    $meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
              7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];

    return ($dias[date('l')] ?? date('l')) . ', ' . (int) date('j') . ' de '
         . ($meses[(int) date('n')] ?? '') . ' de ' . date('Y');
}

/** Días transcurridos entre dos fechas (positivo = ya venció) */
function dias_retraso(string $fechaEsperada): int
{
    return (int) floor((time() - strtotime($fechaEsperada)) / 86400);
}

/** Iniciales para el avatar del usuario */
function iniciales(string $nombre, string $apellido = ''): string
{
    return mb_strtoupper(mb_substr(trim($nombre), 0, 1) . mb_substr(trim($apellido), 0, 1));
}

/** Etiqueta de color según el estado real de un préstamo (RF-12) */
function badge_prestamo(string $estado, string $fechaEsperada): string
{
    if ($estado === 'Devuelto') {
        return '<span class="badge cora-badge cora-badge-devuelto"><i class="bi bi-check2-circle me-1"></i>Devuelto</span>';
    }
    if (dias_retraso($fechaEsperada) >= 0) {
        return '<span class="badge cora-badge cora-badge-vencido"><i class="bi bi-exclamation-triangle me-1"></i>Vencido</span>';
    }
    return '<span class="badge cora-badge cora-badge-activo"><i class="bi bi-arrow-repeat me-1"></i>Activo</span>';
}

/** Etiqueta de color según la disponibilidad del libro (RF-13) */
function badge_disponibilidad(int $disponible): string
{
    if ($disponible <= 0) {
        return '<span class="badge cora-badge cora-badge-vencido">Agotado</span>';
    }
    if ($disponible === 1) {
        return '<span class="badge cora-badge cora-badge-advertencia">Último ejemplar</span>';
    }
    return '<span class="badge cora-badge cora-badge-disponible">' . $disponible . ' disponibles</span>';
}

/** Etiqueta de estado de usuario */
function badge_estado_usuario(string $estado): string
{
    $clase   = $estado === 'Activo' ? 'cora-badge-disponible' : 'cora-badge-devuelto';
    $icono   = $estado === 'Activo' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    return '<span class="badge cora-badge ' . $clase . '"><i class="bi ' . $icono . ' me-1"></i>' . e($estado) . '</span>';
}

/** Etiqueta de rol */
function badge_rol(string $rol): string
{
    $clases = [
        'Administrador'  => 'cora-badge-rol-admin',
        'Bibliotecario'  => 'cora-badge-rol-biblio',
        'Lector'         => 'cora-badge-rol-lector',
    ];
    return '<span class="badge cora-badge ' . ($clases[$rol] ?? '') . '">' . e($rol) . '</span>';
}

/**
 * Paginación Bootstrap que conserva los filtros activos.
 *
 * @param array $params Filtros GET actuales (ej. ['q' => 'sql', 'categoria_id' => 2])
 */
function paginacion(int $pagina, int $totalPaginas, array $params = []): string
{
    if ($totalPaginas <= 1) {
        return '';
    }

    $enlace = fn (int $p): string => '?' . http_build_query(array_merge($params, ['pagina' => $p]));

    $html = '<nav aria-label="Navegación de páginas"><ul class="pagination justify-content-center mb-0">';

    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s" aria-label="Anterior">%s</a></li>',
        $pagina <= 1 ? 'disabled' : '',
        e($enlace(max(1, $pagina - 1))),
        '<i class="bi bi-chevron-left"></i>'
    );

    for ($i = 1; $i <= $totalPaginas; $i++) {
        $lejana = $totalPaginas > 9 && $i > 2 && $i < $totalPaginas - 1 && abs($i - $pagina) > 1;
        if ($lejana) {
            if ($i === 3 || $i === $totalPaginas - 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            continue;
        }
        $html .= sprintf(
            '<li class="page-item %s"><a class="page-link" href="%s">%d</a></li>',
            $i === $pagina ? 'active' : '',
            e($enlace($i)),
            $i
        );
    }

    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s" aria-label="Siguiente">%s</a></li>',
        $pagina >= $totalPaginas ? 'disabled' : '',
        e($enlace(min($totalPaginas, $pagina + 1))),
        '<i class="bi bi-chevron-right"></i>'
    );

    return $html . '</ul></nav>';
}

/** Portada del libro: imagen real o marcador generado con el título */
function portada_libro(array $libro, string $clase = ''): string
{
    if (!empty($libro['portada']) && is_file(APP_PATH . '/public/img/portadas/' . $libro['portada'])) {
        return '<img src="' . e(asset('img/portadas/' . $libro['portada'])) . '" class="' . e($clase) . '" alt="Portada de ' . e($libro['titulo']) . '">';
    }

    $colores = ['4A6BBA', '3A568F', 'D9A441', '5B7FC7', '8A6D2F', '2E4A7A'];
    $color   = $colores[($libro['id'] ?? 0) % count($colores)];
    $titulo  = mb_strimwidth($libro['titulo'] ?? 'Sin título', 0, 46, '…');

    return '<div class="' . e($clase) . ' cora-portada-placeholder" style="background:linear-gradient(150deg,#' . $color . ',#' . $colores[($libro['id'] ?? 1) % 3] . ')">'
        . '<i class="bi bi-book-half"></i><span>' . e($titulo) . '</span></div>';
}

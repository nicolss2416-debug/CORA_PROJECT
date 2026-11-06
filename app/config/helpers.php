<?php
/** Funciones de ayuda usadas por controladores y vistas. */

function url(string $ruta = ''): string
{
    return BASE_URL . 'index.php?url=' . ltrim($ruta, '/');
}

function asset(string $ruta): string
{
    return BASE_URL . 'public/' . ltrim($ruta, '/');
}

/** Escapa texto para evitar XSS en las vistas. */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirigir(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}

/** Renderiza una vista dentro del layout (header + sidebar + footer). */
function layout(string $vista, array $datos = []): void
{
    extract($datos);
    require APP_PATH . '/views/layouts/header.php';
    require APP_PATH . '/views/layouts/sidebar.php';
    echo '<main class="cora-main">';
    require APP_PATH . '/views/layouts/topbar.php';
    echo '<div class="cora-content">';
    require APP_PATH . '/views/layouts/alert.php';
    require APP_PATH . '/views/' . $vista . '.php';
    echo '</div></main>';
    require APP_PATH . '/views/layouts/footer.php';
}

/** Renderiza una vista sin layout (login, 404). */
function vista_simple(string $vista, array $datos = []): void
{
    extract($datos);
    require APP_PATH . '/views/' . $vista . '.php';
}

// ---------- Sesión y roles ----------
function esta_logueado(): bool
{
    return isset($_SESSION['usuario']);
}

function usuario_actual(): array
{
    return $_SESSION['usuario'] ?? [];
}

function tiene_rol(string ...$roles): bool
{
    return in_array(usuario_actual()['rol'] ?? '', $roles, true);
}

// ---------- Mensajes flash (Éxito / Error / Advertencia / Información) ----------
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function flash_obtener(): array
{
    $m = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $m;
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_valido(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Valor anterior de un campo de formulario (para no perder lo escrito). */
function viejo(string $campo, $defecto = ''): string
{
    return e($_SESSION['viejo'][$campo] ?? $defecto);
}

function formato_fecha(?string $f): string
{
    return $f ? date('d/m/Y', strtotime($f)) : '—';
}
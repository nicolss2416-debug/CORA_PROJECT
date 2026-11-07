<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de usuarios
 * RF-03 Registrar · RF-04 Consultar · RF-05 Actualizar · Activar/Desactivar
 * Roles: Administrador (CRUD completo) · Bibliotecario (solo consulta, HU-03)
 */

final class UsuariosController extends Controller
{
    /** GET /usuarios · RF-04: listado con búsqueda y paginación */
    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Bibliotecario'], 'El módulo de usuarios está disponible para el personal de la biblioteca.');

        $q      = trim($_GET['q'] ?? '');
        $rol    = trim($_GET['rol'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $modelo = Usuario::instancia();
        $total  = $modelo->contar($q, $rol, $estado);

        $this->view('usuarios/index', [
            'pageTitle'      => 'Gestión de usuarios',
            'usuarios'       => $modelo->buscarTodos($q, $rol, $estado, $pagina),
            'filtros'        => ['q' => $q, 'rol' => $rol, 'estado' => $estado],
            'pagina'         => $pagina,
            'totalPaginas'   => (int) ceil($total / REGISTROS_POR_PAGINA),
            'totalRegistros' => $total,
            'esAdmin'        => Auth::es('Administrador'),
        ]);
    }

    /** GET /usuarios/crear · RF-03 */
    public function crear(): void
    {
        Auth::requireRole(['Administrador'], 'Solo el administrador puede registrar usuarios.');

        $this->view('usuarios/form', [
            'pageTitle' => 'Registrar usuario',
            'modo'      => 'crear',
            'usuario'   => null,
        ]);
    }

    /** POST /usuarios/guardar · RF-03 */
    public function guardar(): void
    {
        Auth::requireRole(['Administrador']);

        $datos   = $this->datosFormulario();
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if ($errores = $this->validar($datos, $password, $confirm, esCreacion: true)) {
            Flash::setOld($_POST);
            foreach ($errores as $error) {
                Flash::error($error);
            }
            redirect('usuarios/crear');
        }

        $modelo = Usuario::instancia();
        if ($modelo->buscarPorEmail($datos['email'])) {
            Flash::error('Ya existe un usuario registrado con el correo «' . $datos['email'] . '».');
            Flash::setOld($_POST);
            redirect('usuarios/crear');
        }

        $modelo->crear($datos, password_hash($password, PASSWORD_BCRYPT)); // RNF-02: cifrado
        Flash::success('Usuario «' . $datos['nombre'] . ' ' . $datos['apellido'] . '» registrado correctamente.');
        redirect('usuarios');
    }

    /** GET /usuarios/editar/{id} · RF-05 */
    public function editar(?string $id = null): void
    {
        Auth::requireRole(['Administrador'], 'Solo el administrador puede editar usuarios.');

        $usuario = Usuario::instancia()->buscarPorId((int) $id);
        if (!$usuario) {
            Flash::error('El usuario que intenta editar no existe.');
            redirect('usuarios');
        }

        $this->view('usuarios/form', [
            'pageTitle' => 'Editar usuario',
            'modo'      => 'editar',
            'usuario'   => $usuario,
        ]);
    }

    /** POST /usuarios/actualizar/{id} · RF-05 */
    public function actualizar(?string $id = null): void
    {
        Auth::requireRole(['Administrador']);

        $id     = (int) $id;
        $modelo = Usuario::instancia();
        $actual = $modelo->buscarPorId($id);

        if (!$actual) {
            Flash::error('El usuario que intenta actualizar no existe.');
            redirect('usuarios');
        }

        $datos     = $this->datosFormulario();
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['password_confirm'] ?? '';

        if ($errores = $this->validar($datos, $password, $confirm, esCreacion: false)) {
            Flash::setOld($_POST);
            foreach ($errores as $error) {
                Flash::error($error);
            }
            redirect('usuarios/editar/' . $id);
        }

        /* El correo no puede coincidir con el de otro usuario */
        $existente = $modelo->buscarPorEmail($datos['email']);
        if ($existente && (int) $existente['id'] !== $id) {
            Flash::error('El correo «' . $datos['email'] . '» ya pertenece a otro usuario.');
            Flash::setOld($_POST);
            redirect('usuarios/editar/' . $id);
        }

        /* Protección: no auto-degradar el último administrador activo */
        if ($id === Auth::id() && ($datos['rol'] !== 'Administrador' || $datos['estado'] !== 'Activo')) {
            Flash::warning('No puede cambiar su propio rol o desactivar su propia cuenta administradora.');
            redirect('usuarios/editar/' . $id);
        }

        $hash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
        $modelo->actualizar($id, $datos, $hash);

        /* Si el usuario edita su propia cuenta, refresca la sesión */
        if ($id === Auth::id()) {
            $_SESSION['usuario'] = array_merge($_SESSION['usuario'], $datos);
        }

        Flash::success('Datos del usuario actualizados correctamente.');
        redirect('usuarios');
    }

    /** POST /usuarios/estado/{id} · Activa o desactiva la cuenta */
    public function estado(?string $id = null): void
    {
        Auth::requireRole(['Administrador']);

        $id     = (int) $id;
        $modelo = Usuario::instancia();
        $usuario = $modelo->buscarPorId($id);

        if (!$usuario) {
            Flash::error('El usuario indicado no existe.');
        } elseif ($id === Auth::id()) {
            Flash::warning('No puede desactivar su propia cuenta mientras la está usando.');
        } else {
            $nuevo        = $usuario['estado'] === 'Activo' ? 'Inactivo' : 'Activo';
            $bloqueaPrestamo = $nuevo === 'Inactivo' && $modelo->tienePrestamosActivos($id);

            if ($bloqueaPrestamo) {
                Flash::warning('«' . $usuario['nombre'] . ' ' . $usuario['apellido'] . '» tiene préstamos activos. Registre las devoluciones antes de desactivarlo.');
            } else {
                $modelo->cambiarEstado($id, $nuevo);
                Flash::success('Usuario «' . $usuario['nombre'] . ' ' . $usuario['apellido'] . '» ahora está ' . mb_strtolower($nuevo) . '.');
            }
        }

        redirect('usuarios');
    }

    /** POST /usuarios/eliminar/{id} */
    public function eliminar(?string $id = null): void
    {
        Auth::requireRole(['Administrador']);

        $id      = (int) $id;
        $modelo  = Usuario::instancia();
        $usuario = $modelo->buscarPorId($id);

        if (!$usuario) {
            Flash::error('El usuario indicado no existe.');
        } elseif ($id === Auth::id()) {
            Flash::warning('No puede eliminar su propia cuenta.');
        } elseif ($usuario['rol'] === 'Administrador' && $usuario['estado'] === 'Activo' && $this->otrosAdministradores($id) === 0) {
            Flash::warning('Debe existir al menos otro administrador activo antes de eliminar esta cuenta.');
        } elseif ($modelo->tienePrestamosActivos($id)) {
            Flash::warning('«' . $usuario['nombre'] . '» tiene préstamos activos. Registre las devoluciones primero.');
        } elseif ($modelo->eliminar($id)) {
            Flash::success('Usuario «' . $usuario['nombre'] . ' ' . $usuario['apellido'] . '» eliminado del sistema.');
        } else {
            Flash::warning('El usuario tiene historial de préstamos; por integridad de los datos solo puede desactivarse.');
        }

        redirect('usuarios');
    }

    /* ── Privados ── */

    private function otrosAdministradores(int $exceptoId): int
    {
        $modelo = Usuario::instancia();
        return $modelo->contar(rol: 'Administrador', estado: 'Activo') - ($exceptoId > 0 ? 1 : 0);
    }

    private function datosFormulario(): array
    {
        return [
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'email'    => mb_strtolower(trim($_POST['email'] ?? '')),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'rol'      => in_array($_POST['rol'] ?? '', ['Administrador', 'Bibliotecario', 'Lector'], true) ? $_POST['rol'] : 'Lector',
            'estado'   => ($_POST['estado'] ?? 'Activo') === 'Inactivo' ? 'Inactivo' : 'Activo',
        ];
    }

    private function validar(array $datos, string $password, string $confirm, bool $esCreacion): array
    {
        $errores = [];

        if (mb_strlen($datos['nombre']) < 2)       $errores[] = 'El nombre debe tener al menos 2 caracteres.';
        if (mb_strlen($datos['apellido']) < 2)     $errores[] = 'El apellido debe tener al menos 2 caracteres.';
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingrese un correo electrónico válido.';
        if ($datos['telefono'] !== '' && !preg_match('/^[0-9+\s\-]{7,15}$/', $datos['telefono'])) $errores[] = 'El teléfono no tiene un formato válido.';

        if ($esCreacion && mb_strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if (!$esCreacion && $password !== '' && mb_strlen($password) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }
        if ($password !== $confirm) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        return $errores;
    }
}

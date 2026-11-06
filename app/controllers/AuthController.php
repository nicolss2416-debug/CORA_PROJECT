<?php
/** Autenticación — RF-01 Iniciar sesión, RF-02 Cerrar sesión */
class AuthController extends BaseController
{
    public function index(): void
    {
        redirigir('auth/login');
    }

    public function login(): void
    {
        if (esta_logueado()) redirigir('dashboard');

        if ($this->esPost()) {
            $correo = trim($_POST['correo'] ?? '');
            $clave  = $_POST['clave'] ?? '';

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $clave === '') {
                flash('warning', 'Ingresa un correo válido y tu contraseña.');
                redirigir('auth/login');
            }

            $usuario = Usuario::autenticar($correo, $clave);
            if ($usuario) {
                session_regenerate_id(true);
                unset($usuario['clave']);          // nunca guardar la clave en sesión
                $_SESSION['usuario'] = $usuario;
                redirigir('dashboard');
            }
            flash('error', 'El correo o la contraseña son incorrectos.');
            redirigir('auth/login');
        }

        vista_simple('auth/login', ['titulo' => 'Iniciar sesión']);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('info', 'Cerraste sesión correctamente.');
        redirigir('auth/login');
    }
}

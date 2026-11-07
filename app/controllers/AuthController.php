<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de autenticación
 * RF-01 Iniciar sesión · RF-02 Cerrar sesión
 */

final class AuthController extends Controller
{
    /** GET /login → formulario · POST /login → validar credenciales */
    public function login(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            /* Validación del formulario */
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores['email'] = 'Ingrese un correo electrónico válido.';
            }
            if ($password === '') {
                $errores['password'] = 'Ingrese su contraseña.';
            }

            if (!$errores) {
                [$ok, $mensaje, $usuario] = Auth::attempt($email, $password);

                if ($ok) {
                    Auth::login($usuario);            // Crea la sesión
                    Flash::success($mensaje);
                    redirect('dashboard');            // Acceso según rol (controlado en cada módulo)
                }

                Flash::error($mensaje);               // Credenciales inválidas o cuenta inactiva
            }

            Flash::setOld(['email' => $email]);       // Repoblar el correo
        }

        $this->view('auth/login', ['errores' => $errores], conLayout: false);
    }

    /** POST /logout → destruye la sesión (RF-02) */
    public function logout(): void
    {
        Auth::logout();
        session_start();
        Flash::info('Sesión finalizada correctamente. ¡Hasta pronto!');
        redirect('login');
    }
}

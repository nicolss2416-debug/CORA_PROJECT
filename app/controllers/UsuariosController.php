<?php
/** Usuarios — RF-03, RF-04, RF-05 (pantalla 5) */
class UsuariosController extends BaseController
{
    public function index(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        $q = trim($_GET['q'] ?? '');
        layout('usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => Usuario::todos($q),
            'q'        => $q,
            'roles'    => Usuario::ROLES,
            'extraJs'  => ['public/js/usuarios.js'],
        ]);
        $this->limpiarViejo();
    }

    public function guardar(): void
    {
        $this->requerirRol('Administrador');
        $errores = Usuario::validar($_POST);
        if ($errores) {
            $this->guardarViejo($_POST);
            foreach ($errores as $err) flash('error', $err);
            redirigir('usuarios');
        }
        Usuario::crear($_POST);
        flash('success', 'El usuario se registró correctamente.');
        redirigir('usuarios');
    }

    public function actualizar($id = null): void
    {
        $this->requerirRol('Administrador');
        $id = (int) $id;
        $errores = Usuario::validar($_POST, $id);
        if ($errores) {
            foreach ($errores as $err) flash('error', $err);
            redirigir('usuarios');
        }
        Usuario::actualizar($id, $_POST);
        flash('success', 'El usuario se actualizó correctamente.');
        redirigir('usuarios');
    }

    public function estado($id = null): void
    {
        $this->requerirRol('Administrador');
        if ($this->esPost()) {
            if ((int) $id === usuario_actual()['id']) {
                flash('warning', 'No puedes desactivar tu propia cuenta.');
            } else {
                Usuario::cambiarEstado((int) $id);
                flash('info', 'El estado del usuario fue actualizado.');
            }
        }
        redirigir('usuarios');
    }
}

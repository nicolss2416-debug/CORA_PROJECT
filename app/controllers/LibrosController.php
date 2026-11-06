<?php
/** Libros y catálogo — RF-06 a RF-09, RF-13 (pantallas 4, 6 y 7) */
class LibrosController extends BaseController
{
    /** Listado / catálogo con búsqueda por título, autor o categoría */
    public function index(): void
    {
        $q   = trim($_GET['q'] ?? '');
        $cat = trim($_GET['categoria'] ?? '');
        layout('libros/index', [
            'titulo'     => 'Libros',
            'libros'     => Libro::todos($q, $cat),
            'q'          => $q,
            'categoria'  => $cat,
            'categorias' => Libro::categorias(),
        ]);
    }

    public function ver($id = null): void
    {
        $libro = Libro::buscar((int) $id);
        if (!$libro) { (new ErrorController())->noEncontrado(); return; }
        layout('libros/detalle', ['titulo' => $libro['titulo'], 'libro' => $libro]);
    }

    public function crear(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        layout('libros/form', [
            'titulo' => 'Nuevo libro', 'libro' => null,
            'categorias' => Libro::categorias(), 'accion' => 'libros/guardar',
        ]);
        $this->limpiarViejo();
    }

    public function guardar(): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        $errores = Libro::validar($_POST);
        if ($errores) {
            $this->guardarViejo($_POST);
            foreach ($errores as $err) flash('error', $err);
            redirigir('libros/crear');
        }
        Libro::crear($_POST);
        flash('success', 'El libro se registró correctamente.');
        redirigir('libros');
    }

    public function editar($id = null): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        $libro = Libro::buscar((int) $id);
        if (!$libro) { (new ErrorController())->noEncontrado(); return; }
        layout('libros/form', [
            'titulo' => 'Editar libro', 'libro' => $libro,
            'categorias' => Libro::categorias(), 'accion' => 'libros/actualizar/' . $libro['id'],
        ]);
        $this->limpiarViejo();
    }

    public function actualizar($id = null): void
    {
        $this->requerirRol('Administrador', 'Bibliotecario');
        $id = (int) $id;
        $errores = Libro::validar($_POST);
        if ($errores) {
            $this->guardarViejo($_POST);
            foreach ($errores as $err) flash('error', $err);
            redirigir('libros/editar/' . $id);
        }
        Libro::actualizar($id, $_POST);
        flash('success', 'El libro se actualizó correctamente.');
        redirigir('libros');
    }

    public function eliminar($id = null): void
    {
        $this->requerirRol('Administrador');
        if (!$this->esPost()) redirigir('libros');
        if (Libro::eliminar((int) $id)) {
            flash('success', 'El libro se eliminó correctamente.');
        } else {
            flash('warning', 'No se puede eliminar: el libro tiene ejemplares prestados.');
        }
        redirigir('libros');
    }
}

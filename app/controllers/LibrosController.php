<?php
declare(strict_types=1);

/**
 * BIBLIOTECA CORA · Controlador de libros
 * RF-06 Registrar · RF-07 Consultar catálogo · RF-08 Buscar · RF-09 Actualizar
 * Roles: Administrador y Bibliotecario (CRUD) · Lector (solo consulta y ficha)
 */

final class LibrosController extends Controller
{
    private const ROLES_CRUD = ['Administrador', 'Bibliotecario'];

    /** GET /libros · RF-07, RF-08: listado con filtros y paginación */
    public function index(): void
    {
        $q           = trim($_GET['q'] ?? '');
        $categoriaId = (int) ($_GET['categoria_id'] ?? 0);
        $pagina      = max(1, (int) ($_GET['pagina'] ?? 1));

        $modelo = Libro::instancia();
        $total  = $modelo->contar($q, $categoriaId);

        $this->view('libros/index', [
            'pageTitle'   => 'Gestión de libros',
            'libros'      => $modelo->buscarTodos($q, $categoriaId, $pagina),
            'categorias'  => Categoria::instancia()->todas(),
            'filtros'     => ['q' => $q, 'categoria_id' => $categoriaId],
            'pagina'      => $pagina,
            'totalPaginas' => (int) ceil($total / REGISTROS_POR_PAGINA),
            'totalRegistros' => $total,
            'puedeCrear'  => Auth::es(...self::ROLES_CRUD),
        ]);
    }

    /** GET /libros/detalle/{id} · Módulo 7: ficha técnica con préstamo directo */
    public function detalle(?string $id = null): void
    {
        $libro = Libro::instancia()->buscarPorId((int) $id);

        if (!$libro) {
            Flash::error('El libro solicitado no existe en el catálogo.');
            redirect('libros');
        }

        $this->view('libros/detalle', [
            'pageTitle'    => $libro['titulo'],
            'libro'        => $libro,
            'puedePrestar' => Auth::es('Administrador', 'Bibliotecario'),
            'esLector'     => Auth::es('Lector'),
        ]);
    }

    /** GET /libros/crear · RF-06: formulario de registro */
    public function crear(): void
    {
        Auth::requireRole(self::ROLES_CRUD, 'Solo el administrador y el bibliotecario pueden registrar libros.');

        $this->view('libros/form', [
            'pageTitle'  => 'Registrar libro',
            'modo'       => 'crear',
            'libro'      => null,
            'categorias' => Categoria::instancia()->todas(),
        ]);
    }

    /** POST /libros/guardar · Guarda un libro nuevo (RF-06) */
    public function guardar(): void
    {
        Auth::requireRole(self::ROLES_CRUD);
        $datos = $this->datosFormulario();

        if ($errores = $this->validar($datos)) {
            Flash::setOld($datos);
            foreach ($errores as $error) {
                Flash::error($error);
            }
            redirect('libros/crear');
        }

        try {
            Libro::instancia()->crear($datos);
            Flash::success('Libro «' . $datos['titulo'] . '» registrado correctamente en el catálogo.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                Flash::error('Ya existe un libro registrado con el ISBN «' . $datos['isbn'] . '».');
            } else {
                Flash::error('No fue posible registrar el libro. Verifique los datos.');
            }
            Flash::setOld($datos);
            redirect('libros/crear');
        }

        redirect('libros');
    }

    /** GET /libros/editar/{id} · RF-09: formulario de actualización */
    public function editar(?string $id = null): void
    {
        Auth::requireRole(self::ROLES_CRUD, 'Solo el administrador y el bibliotecario pueden editar libros.');

        $libro = Libro::instancia()->buscarPorId((int) $id);
        if (!$libro) {
            Flash::error('El libro que intenta editar no existe.');
            redirect('libros');
        }

        $this->view('libros/form', [
            'pageTitle'  => 'Editar libro',
            'modo'       => 'editar',
            'libro'      => $libro,
            'categorias' => Categoria::instancia()->todas(),
        ]);
    }

    /** POST /libros/actualizar/{id} · Aplica los cambios (RF-09) */
    public function actualizar(?string $id = null): void
    {
        Auth::requireRole(self::ROLES_CRUD);
        $id   = (int) $id;
        $datos = $this->datosFormulario();

        if ($errores = $this->validar($datos)) {
            Flash::setOld($datos);
            foreach ($errores as $error) {
                Flash::error($error);
            }
            redirect('libros/editar/' . $id);
        }

        try {
            Libro::instancia()->actualizar($id, $datos);
            Flash::success('Información del libro «' . $datos['titulo'] . '» actualizada correctamente.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                Flash::error('El ISBN «' . $datos['isbn'] . '» ya pertenece a otro libro.');
            } else {
                Flash::error('No fue posible actualizar el libro. Verifique los datos.');
            }
            Flash::setOld($datos);
            redirect('libros/editar/' . $id);
        }

        redirect('libros');
    }

    /** POST /libros/eliminar/{id} · Elimina un libro sin historial de préstamos */
    public function eliminar(?string $id = null): void
    {
        Auth::requireRole(self::ROLES_CRUD, 'Solo el administrador y el bibliotecario pueden eliminar libros.');

        $modelo = Libro::instancia();
        $id     = (int) $id;
        $libro  = $modelo->buscarPorId($id);

        if (!$libro) {
            Flash::error('El libro que intenta eliminar no existe.');
        } elseif ($modelo->tienePrestamos($id)) {
            Flash::warning('«' . $libro['titulo'] . '» tiene historial de préstamos y no puede eliminarse (integridad de los datos).');
        } elseif ($modelo->eliminar($id)) {
            Flash::success('Libro «' . $libro['titulo'] . '» eliminado del catálogo.');
        } else {
            Flash::error('No fue posible eliminar el libro.');
        }

        redirect('libros');
    }

    /* ── Privados ── */

    /** Recolecta y normaliza los campos del formulario (RF-06) */
    private function datosFormulario(): array
    {
        return [
            'titulo'          => trim($_POST['titulo'] ?? ''),
            'autor'           => trim($_POST['autor'] ?? ''),
            'categoria_id'    => (int) ($_POST['categoria_id'] ?? 0),
            'isbn'            => trim($_POST['isbn'] ?? ''),
            'editorial'       => trim($_POST['editorial'] ?? ''),
            'anio_publicacion'=> (int) ($_POST['anio_publicacion'] ?? 0) ?: null,
            'sinopsis'        => trim($_POST['sinopsis'] ?? ''),
            'portada'         => trim($_POST['portada'] ?? ''),
            'cantidad_total'  => (int) ($_POST['cantidad_total'] ?? 0),
        ];
    }

    /** Validación servidor de los campos del libro */
    private function validar(array $datos): array
    {
        $errores = [];

        if (mb_strlen($datos['titulo']) < 3)          $errores[] = 'El título debe tener al menos 3 caracteres.';
        if (mb_strlen($datos['autor']) < 3)           $errores[] = 'El autor debe tener al menos 3 caracteres.';
        if ($datos['categoria_id'] <= 0)              $errores[] = 'Seleccione una categoría válida.';
        if (!preg_match('/^[0-9Xx\-]{10,17}$/', $datos['isbn'])) $errores[] = 'El ISBN debe tener entre 10 y 13 caracteres (números y guiones).';
        if ($datos['cantidad_total'] < 1)             $errores[] = 'La cantidad de ejemplares debe ser al menos 1.';
        if ($datos['cantidad_total'] > 999)           $errores[] = 'La cantidad de ejemplares no puede exceder 999.';
        if ($datos['anio_publicacion'] !== null && ($datos['anio_publicacion'] < 1000 || $datos['anio_publicacion'] > (int) date('Y'))) {
            $errores[] = 'El año de publicación no es válido.';
        }

        return $errores;
    }
}

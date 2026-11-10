# 📚 Biblioteca CORA — Sistema Web de Gestión Bibliotecaria

Sistema web para administrar libros, usuarios, préstamos y devoluciones de la Biblioteca CORA (proyecto académico SENA – ADSO), desarrollado con **PHP 8 nativo**, **MySQL/MariaDB**, **Bootstrap 5** y **arquitectura MVC**.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4) ![MySQL](https://img.shields.io/badge/MySQL%2FMariaDB-10%2B-4479A1) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3) ![Arquitectura](https://img.shields.io/badge/Arquitectura-MVC-D9A441)

---

## 🚀 Instalación rápida (XAMPP)

1. **Copiar el proyecto** en `C:\xampp\htdocs\CORA_PROJECT\CORA_PROJECT`.
2. **Iniciar Apache** desde el Panel de Control de XAMPP.
3. **Crear la base de datos** importando `database/biblioteca_cora.sql`
   (phpMyAdmin → Importar, o por consola: `mysql -u root < database/biblioteca_cora.sql`).
4. **Revisar credenciales de BD** en `app/config/config.php`
   (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`).
5. Abrir **http://localhost/CORA_PROJECT/CORA_PROJECT/login**

> ⚙️ **Nota del entorno actual:** el puerto 3306 está ocupado por un servicio
> Windows **MySQL80**, por lo que la aplicación está configurada contra la
> **MariaDB de XAMPP en el puerto 3307**. Para levantarla:
>
> ```bat
> :: MariaDB dedicada en 3307 (no interfiere con MySQL80)
> C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my_3307.ini --standalone
> ```
>
> Si prefieres usar tu MySQL 8 del puerto 3306, cambia `DB_PORT` a `3306`
> en `app/config/config.php` y coloca el usuario/contraseña correspondientes.

## 👤 Usuarios de demostración

| Rol           | Correo           | Contraseña  |
|---------------|------------------|-------------|
| Administrador | admin@cora.com   | admin123    |
| Bibliotecario | biblio@cora.com  | biblio123   |
| Lector        | lector@cora.com  | lector123   |

## 🧩 Módulos

| # | Módulo | Funcionalidad |
|---|--------|---------------|
| 1 | **Login** | Autenticación con sesiones PHP, contraseñas cifradas (bcrypt) y control de roles (Administrador, Bibliotecario, Lector) |
| 2 | **Dashboard** | Tarjetas de contadores (libros, usuarios, préstamos activos/vencidos) + gráficos Chart.js |
| 3 | **Navegación** | Sidebar responsivo según rol: Inicio, Libros, Usuarios, Préstamos, Devoluciones, Reportes, Cerrar sesión |
| 4–6 | **Libros** | CRUD completo con búsqueda (título/autor/ISBN), filtro por categoría, paginación y ficha técnica con préstamo directo |
| 5 | **Usuarios** | CRUD, roles y control de estados Activo/Inactivo |
| 8 | **Préstamos y Devoluciones** | Registro con validación de stock, historial por estado, devolución con actualización automática de inventario |
| 9 | **Reportes** | Métricas y gráficos filtrables por rango de fechas, top libros y top lectores |
| 10 | **Mensajes** | Alertas dinámicas de éxito, error, advertencia e información |

## 📏 Reglas de negocio implementadas

- **RN-01** — No se presta un libro con 0 ejemplares disponibles (transacción `SELECT … FOR UPDATE`).
- **RN-02** — Todo préstamo se asocia a un lector activo y un libro, con fecha, fecha prevista de devolución y estado.
- **RN-03** — Al devolver: estado → `Devuelto`, se incrementa la disponibilidad y **no se puede devolver dos veces**.

## 🏗️ Estructura del proyecto

```
CORA_PROJECT/
├── index.php                  # Front Controller (enrutador)
├── .htaccess                  # URLs limpias + protección de archivos
├── app/
│   ├── config/                # config.php (constantes) · database.php (PDO singleton)
│   ├── core/                  # Auth (sesiones/roles/CSRF) · Controller · Flash
│   ├── controllers/           # Auth, Dashboard, Libros, Usuarios, Prestamos,
│   │                          # Devoluciones, Reportes
│   ├── models/                # Libro, Usuario, Categoria, Prestamo (PDO preparado)
│   ├── views/                 # layouts/ · auth/ · libros/ · usuarios/ ·
│   │                          # prestamos/ · devoluciones/ · reportes/ · errors/
│   └── helpers/               # Funciones de vista (escape, badges, paginación)
├── public/
│   ├── css/style.css          # Sistema de diseño CORA
│   ├── js/app.js              # Sidebar, modales, confirmaciones
│   └── img/                   # logo, favicon, portadas SVG
├── database/
│   └── biblioteca_cora.sql    # Esquema + datos de demostración
└── docs/                      # Documentación SENA (requisitos, matriz, etc.)
```

## 🎨 Sistema de diseño

| Token | Valor |
|-------|-------|
| Principal (azul) | `#4A6BBA` |
| Acento (dorado) | `#D9A441` |
| Fondo | `#F8F9FA` |
| Superficie | `#FFFFFF` |
| Texto | `#263238` |
| Tipografías | **Poppins** (títulos) · **Roboto** (texto y botones) |

Responsive: escritorio (1440 px), tablet y móvil (390 px) con sidebar off-canvas.

## 🔒 Seguridad (RNF-02)

- Contraseñas cifradas con **bcrypt** (`password_hash` / `password_verify`).
- Consultas **100 % preparadas con PDO** (previene inyección SQL).
- Tokens **CSRF** en todos los formularios POST.
- Escape de salida con `htmlspecialchars` en las vistas (previene XSS).
- `session_regenerate_id` al iniciar sesión y cookies `HttpOnly`.
- Control de acceso por rol en cada módulo (`Auth::requireRole`).

## 🧪 Pruebas ejecutadas

Smoke test end-to-end automatizado (26 verificaciones ✔): login válido/inválido,
CSRF, acceso a los 6 módulos, búsqueda, registro de préstamo con descuento de
stock (RF-13), bloqueo RN-01 sin ejemplares, devolución RN-03 con restauración
de inventario, rechazo de doble devolución y cierre de sesión.

---

Proyecto académico SENA – ADSO · Aprendiz Evelyn Nicole Gaviria Manzo · 2026

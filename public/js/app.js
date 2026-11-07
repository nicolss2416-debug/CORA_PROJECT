/* ═══════════════════════════════════════════════════════════════
   BIBLIOTECA CORA · Comportamientos de la interfaz
   Sidebar responsivo, confirmaciones y utilerías de formularios
   ═══════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ── Sidebar responsivo (módulo 3) ── */
        const toggle  = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('coraSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        function cerrarSidebar() {
            document.body.classList.remove('cora-sidebar-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }

        if (toggle && sidebar) {
            toggle.addEventListener('click', function () {
                const abierto = document.body.classList.toggle('cora-sidebar-open');
                toggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            });
        }
        if (backdrop) backdrop.addEventListener('click', cerrarSidebar);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarSidebar();
        });
        // En móvil, navegar cierra el menú
        if (window.innerWidth < 992) {
            sidebar?.querySelectorAll('.cora-nav-link').forEach(function (enlace) {
                enlace.addEventListener('click', cerrarSidebar);
            });
        }

        /* ── Confirmaciones para formularios y enlaces con data-confirm ── */
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.dataset.confirm)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });

        /* ── Modal de préstamo: abrir automáticamente cuando viene preseleccionado ── */
        const modalPrestamo = document.getElementById('modalPrestamo');
        if (modalPrestamo && modalPrestamo.dataset.abrir === '1') {
            const modal = new bootstrap.Modal(modalPrestamo);
            modal.show();
        }

        /* ── Modal de devolución: rellenar datos del préstamo seleccionado ── */
        const modalDevolucion = document.getElementById('modalDevolucion');
        if (modalDevolucion) {
            modalDevolucion.addEventListener('show.bs.modal', function (evento) {
                const boton = evento.relatedTarget;
                if (!boton) return;
                try {
                    const datos = JSON.parse(boton.dataset.prestamo);
                    document.getElementById('dev_prestamo_id').value = datos.id || '';
                    document.getElementById('dev_libro').textContent    = datos.libro || '—';
                    document.getElementById('dev_lector').textContent   = datos.lector || '—';
                    document.getElementById('dev_prestamo').textContent = datos.prestamo || '—';
                    document.getElementById('dev_esperada').textContent = datos.esperada || '—';
                } catch (e) { /* JSON inválido: se ignoran los datos */ }
            });
        }

        /* ── Mostrar / ocultar contraseña en el login ── */
        document.querySelectorAll('[data-bs-toggle="password"]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                const campo = document.querySelector(boton.dataset.target);
                if (!campo) return;
                const visible = campo.type === 'password';
                campo.type = visible ? 'text' : 'password';
                boton.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
                boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });

        /* ── Alertas: autocierre a los 6 s (módulo 10) ── */
        document.querySelectorAll('.cora-alert').forEach(function (alerta) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(alerta).close();
            }, 6000);
        });
    });
})();

<?php
// Views/Usuario/Templates/footerUsuario.php
?>
</main> <!-- /user-content -->
</div> <!-- /user-right -->
</div> <!-- /user-layout -->
<script type="module">
  import { Chess } from "https://cdn.jsdelivr.net/npm/chess.js@1.0.0/+esm";
  window.Chess = Chess;
</script>
<script>
  window.BASE_URL = '<?= rtrim(BASE_URL, '/') . '/' ?>';
  // Valores seguros por defecto (evita notices si no estás en "resolver")
  window.EJ_FEN   = <?= json_encode($fen   ?? null) ?>;
  window.EJ_TURNO = <?= json_encode($turno ?? null) ?>;
  window.ASIG_ID  = <?= isset($asig['id']) ? (int)$asig['id'] : 0 ?>;
  window.CSRF     = <?= json_encode($csrf ?? csrfToken()) ?>;
</script>

<script src="<?= BASE_URL ?>Assets/vendor/chessboard/jquery-3.7.1.js"></script>
<script src="<?= BASE_URL ?>Assets/vendor/chessboard/chessboard-1.0.0.min.js"></script>
<!-- JS comunes -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.2.0/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
  const BASE_URL = "<?= BASE_URL ?>";
  const USER_ROL = "<?= strtolower($rol) ?>";
</script>
<script src="<?= BASE_URL ?>Assets/js/mensajes.js?v=<?= time() ?>" defer></script>
<script src="<?= BASE_URL ?>Assets/js/notificaciones.js?v=<?= time() ?>" defer></script>
<script src="<?= BASE_URL ?>Assets/js/usuario-ejercicios.js?v=<?= time() ?>" defer></script>
<!-- Resolver (seguro: no hace nada si no hay #board) -->
<?php if (!empty($asig['id'])): ?>
<script src="<?= BASE_URL ?>Assets/js/usuario-ejercicios-resolver.js?v=<?= time() ?>"></script>
<?php endif; ?>

<script>
    (function() {
        const body = document.body;
        const btn = document.querySelector('.menu-btn');
        const sidebar = document.getElementById('userSidebar');
        const overlay = document.querySelector('.user-overlay');
        if (!btn || !sidebar || !overlay) return;

        const open = () => {
            body.classList.add('sidebar-open');
            btn.setAttribute('aria-expanded', 'true');
            overlay.hidden = false;
        };
        const close = () => {
            body.classList.remove('sidebar-open');
            btn.setAttribute('aria-expanded', 'false');
            overlay.hidden = true;
        };
        const toggle = () => body.classList.contains('sidebar-open') ? close() : open();

        btn.addEventListener('click', toggle);
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });

        // Cerrar al navegar por algún enlace del menú en móvil
        sidebar.querySelectorAll('a[href]').forEach(a => {
            a.addEventListener('click', () => {
                if (window.innerWidth < 992) close();
            });
        });

        // Si se redimensiona a escritorio, asegurar cerrado y overlay oculto
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) close();
        });
    })();
</script>
<script>
    (function() {
        const btn = document.getElementById('notifBtn');
        if (!btn) return;

        // Cuando se abre el dropdown, marca como leídas (opcional)
        btn.addEventListener('click', async () => {
            try {
                await fetch('<?= BASE_URL ?>UsuarioNotificaciones/marcarLeidas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        csrf: window.CSRF_TOKEN || ''
                    })
                });
                // opcional: quitar el punto verde sin recargar
                const dot = btn.querySelector('.badge-dot');
                if (dot) dot.remove();
            } catch (e) {}
        });
    })();
</script>
<script>
    // CSRF global por si lo necesitas en fetch()
    window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || window.CSRF_TOKEN || '';

    // Ejemplo de helper para peticiones POST con x-www-form-urlencoded
    window.postForm = async function(url, data = {}) {
        const body = new URLSearchParams({
            ...(data || {}),
            csrf: window.CSRF_TOKEN
        });
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body
        });
        return res;
    };
</script>
</body>

</html>
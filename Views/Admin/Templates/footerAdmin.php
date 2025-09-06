      </main><!-- /admin-content -->

      <footer class="admin-footer">
        <div>© <?= date('Y') ?> Club de Ajedrez de Berriozar — Admin</div>
      </footer>
      </div><!-- /admin-right -->
      </div><!-- /editor-layout -->

      <?php if (!empty($_SESSION['flash_success']) || !empty($_SESSION['flash_error'])): ?>
        <?php
        $flashTipo = !empty($_SESSION['flash_success']) ? 'success' : 'error';
        $flashMsg  = !empty($_SESSION['flash_success']) ? $_SESSION['flash_success'] : $_SESSION['flash_error'];
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        ?>
        <div id="js-flash-datos"
          data-tipo="<?= htmlspecialchars($flashTipo) ?>"
          data-mensaje="<?= htmlspecialchars($flashMsg) ?>"
          hidden></div>
      <?php endif; ?>
      <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const USER_ROL = "<?= strtolower($rol) ?>";
      </script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/tinymce@7.2.0/tinymce.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

      <script src="<?= BASE_URL ?>Assets/js/mensajes.js?v=<?= time() ?>" defer></script>
      <script src="<?= BASE_URL ?>Assets/js/notificaciones.js?v=<?= time() ?>" defer></script>
      <script src="<?= BASE_URL ?>Assets/js/admin-ejercicios.js?v=<?= time() ?>" defer></script>
      

      <script>
        // TinyMCE para el campo 'contenido' (tema oscuro)
        if (document.querySelector('textarea[name="contenido"]')) {
          tinymce.init({
            selector: 'textarea[name="contenido"]',
            height: 420,
            menubar: false,
            plugins: 'link lists code image media table autoresize',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link media image table | code',
            skin: 'oxide-dark',
            content_css: 'dark'
          });
        }

        // SweetAlert2: confirmar borrado de noticia y de medio (evita el confirm() del inline)
        (function() {
          const hook = (selector, title) => {
            document.querySelectorAll(selector).forEach(f => {
              f.addEventListener('submit', e => {
                e.preventDefault();
                Swal.fire({
                  title,
                  text: 'Esta acción no se puede deshacer',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonText: 'Sí, borrar',
                  cancelButtonText: 'Cancelar',
                  confirmButtonColor: '#dc3545'
                }).then(r => {
                  if (r.isConfirmed) {
                    f.onsubmit = null; // anula onsubmit inline para evitar el confirm() nativo
                    f.submit();
                  }
                });
              });
            });
          };
          hook('form[action*="AdminNoticias/borrar/"]', '¿Borrar noticia?');
          hook('form[action*="AdminNoticias/borrarMedio/"]', '¿Quitar archivo?');
        })();

        // SortableJS para reordenar la galería
        (function() {
          // Localizamos la cuadrícula de la galería buscando formularios de borrarMedio
          const forms = Array.from(document.querySelectorAll('form[action*="AdminNoticias/borrarMedio/"]'));
          if (!forms.length) return;

          // El contenedor suele ser el .row que envuelve los .col-md-4
          const row = forms[0].closest('.row');
          if (!row) return;

          new Sortable(row, {
            animation: 150,
            onEnd: async () => {
              // Extraemos medio_id de la URL del form de cada tarjeta en el nuevo orden
              const ids = Array.from(row.querySelectorAll('.col-md-4')).map(col => {
                const fm = col.querySelector('form[action*="AdminNoticias/borrarMedio/"]');
                if (!fm) return null;
                const m = fm.action.match(/borrarMedio\/\d+\/(\d+)/);
                return m ? m[1] : null;
              }).filter(Boolean);

              const url = '<?= BASE_URL ?>AdminNoticias/ordenarMediosAjax/<?= isset($n['id']) ? (int)$n['id'] : 0 ?>';
              const csrf = '<?= $data['csrf'] ?? csrfToken() ?>';

              try {
                await fetch(url, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'fetch'
                  },
                  body: JSON.stringify({
                    csrf,
                    orden: ids
                  })
                });
              } catch (e) {
                console.error(e);
              }
            }
          });
        })();
      </script>


      <?php if (!empty($_SESSION['flash_ok']) || !empty($_SESSION['flash_error'])): ?>
        <script>
          (function() {
            const ok = <?= json_encode($_SESSION['flash_ok']   ?? '') ?>;
            const er = <?= json_encode($_SESSION['flash_error'] ?? '') ?>;
            <?php unset($_SESSION['flash_ok'], $_SESSION['flash_error']); ?>
            if (ok) Swal.fire({
              icon: 'success',
              title: ok
            });
            if (er) Swal.fire({
              icon: 'error',
              title: 'Error',
              text: er
            });
          })();
        </script>
      <?php endif; ?>
      </body>

      </html>
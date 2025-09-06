<script>
    window.BASE_URL = '<?= rtrim(BASE_URL, '/') . '/' ?>';
</script>


<!-- Botón WhatsApp -->
<a href="https://wa.me/34600111222"
    class="btn btn-success position-fixed bottom-0 end-0 m-4 rounded-circle shadow"
    style="z-index: 999; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;"
    target="_blank" rel="noopener" aria-label="Abrir chat de WhatsApp">
    <i class="bi bi-whatsapp fs-3" aria-hidden="true"></i>
</a>


<footer class="py-4 text-center">
    <small>&copy; <?= date('Y') ?> Club de Ajedrez de Berriozar</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Swiper JS (no module, sin defer) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
    const USER_ROL = "<?= strtolower($rol) ?>";
</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="<?= BASE_URL ?>Assets/vendor/chessboard/jquery-3.7.1.js"></script>
<script type="text/javascript" src="<?= BASE_URL ?>Assets/vendor/chessboard/chessboard-1.0.0.min.js"></script>
<script type="module">
    import {
        Chess
    } from "https://cdn.jsdelivr.net/npm/chess.js@1.0.0/+esm";
    window.Chess = Chess;
    console.log('[bridge] Chess listo:', typeof window.Chess);
</script>

<script src="<?= BASE_URL ?>Assets/js/ejercicios-listado.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>Assets/js/testimonios.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>Assets/js/noticias-listado.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>Assets/js/eventos-listado.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>Assets/js/personas-listado.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>Assets/js/torneos-listado.js?v=<?= time() ?>"></script>

<script src="<?= BASE_URL ?>Assets/js/alerts.js?v=<?= time() ?>"></script>



<?php if (!empty($_SESSION['flash_ok'])): ?>
    <script>
        showToast('success', <?= json_encode($_SESSION['flash_ok']) ?>);
    </script>
<?php unset($_SESSION['flash_ok']);
endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <script>
        showToast('error', <?= json_encode($_SESSION['flash_error']) ?>);
    </script>
<?php unset($_SESSION['flash_error']);
endif; ?>

<?php if (!empty($_SESSION['flash_info'])): ?>
    <script>
        showToast('info', <?= json_encode($_SESSION['flash_info']) ?>);
    </script>
<?php unset($_SESSION['flash_info']);
endif; ?>

<?php if (!empty($_SESSION['flash_warn'])): ?>
    <script>
        showToast('warning', <?= json_encode($_SESSION['flash_warn']) ?>);
    </script>
<?php unset($_SESSION['flash_warn']);
endif; ?>
</body>

</html>
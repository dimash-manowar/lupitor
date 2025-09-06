<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5" style="max-width:720px;">
    <h2 class="text-info mb-4">Cambiar contraseña</h2>


    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['flash_error'];
                                        unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash_ok'];
                                            unset($_SESSION['flash_ok']); ?></div>
    <?php endif; ?>


    <form method="post" action="<?= BASE_URL ?>perfil/passwordPost">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">


        <div class="mb-3">
            <label class="form-label">Contraseña actual</label>
            <input type="password" name="actual" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Repite la nueva contraseña</label>
            <input type="password" name="password2" class="form-control" minlength="6" required>
        </div>
        <button class="btn btn-success">Actualizar contraseña</button>
    </form>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>
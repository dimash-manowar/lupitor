<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5" style="max-width:720px;">
<h2 class="text-info mb-4">Mi perfil</h2>


<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_ok'])): ?>
<div class="alert alert-success"><?php echo $_SESSION['flash_ok']; unset($_SESSION['flash_ok']); ?></div>
<?php endif; ?>


<form method="post" action="<?= BASE_URL ?>perfil/actualizar">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
<div class="mb-3">
<label class="form-label">Nombre</label>
<input type="text" name="nombre" value="<?= htmlspecialchars($data['user']['nombre']) ?>" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($data['user']['email']) ?>" class="form-control" required>
</div>
<div class="d-flex gap-2">
<button class="btn btn-primary">Guardar cambios</button>
<a class="btn btn-outline-light" href="<?= BASE_URL ?>perfil/password">Cambiar contraseña</a>
</div>
</form>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>
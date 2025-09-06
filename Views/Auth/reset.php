<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5" style="max-width:480px">
  <h2 class="text-info mb-4"><?= $data['titulo'] ?></h2>
  <form method="post" action="<?= BASE_URL ?>auth/resetPost">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
    <input type="hidden" name="selector" value="<?= htmlspecialchars($data['selector']) ?>">
    <input type="hidden" name="validator" value="<?= htmlspecialchars($data['validator']) ?>">

    <div class="mb-3">
      <label class="form-label">Nueva contraseña</label>
      <input type="password" name="password" class="form-control" minlength="6" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Repite contraseña</label>
      <input type="password" name="password2" class="form-control" minlength="6" required>
    </div>
    <button class="btn btn-success w-100">Restablecer contraseña</button>
  </form>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>
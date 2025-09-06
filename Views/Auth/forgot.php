<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5" style="max-width:480px">
  <h2 class="text-info mb-4"><?= $data['titulo'] ?></h2> 

  <form method="post" action="<?= BASE_URL ?>auth/forgotPost">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
    <div class="mb-3">
      <label class="form-label">Introduce tu email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100">Enviar enlace</button>
  </form>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>
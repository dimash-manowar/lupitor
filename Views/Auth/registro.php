<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>

<section class="container my-5" style="max-width: 640px;">
  <h2 class="text-info mb-4">Crear cuenta</h2>

  <form id="registroForm" method="post" action="<?= BASE_URL ?>auth/registroPost" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" minlength="6" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Repetir contraseña</label>
        <input type="password" name="password2" class="form-control" minlength="6" required>
      </div>

      <!-- Foto de perfil + previsualización -->
      <div class="col-12">
        <label class="form-label">Foto de perfil (opcional)</label>
        <div class="d-flex align-items-center gap-3">
          <!-- Preview redondo -->
          <div class="border rounded-circle overflow-hidden" style="width:96px;height:96px;">
            <img id="fotoPreview" alt="Vista previa" style="width:100%;height:100%;object-fit:cover;display:none;">
            <div id="fotoPlaceholder" class="w-100 h-100 d-flex align-items-center justify-content-center text-secondary" style="font-size:32px;">👤</div>
          </div>
          <div class="flex-grow-1">
            <input type="file" name="foto" id="fotoInput" class="form-control"
                   accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Formatos: JPG/PNG/WebP · Máx 5&nbsp;MB</div>
          </div>
        </div>
      </div>
    </div>

    <button class="btn btn-success w-100 mt-3">Crear cuenta</button>
  </form>

  <p class="mt-3 text-center">
    ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>auth/login">Inicia sesión</a>
  </p>
</section>

<script>
(function(){
  const input = document.getElementById('fotoInput');
  const img   = document.getElementById('fotoPreview');
  const ph    = document.getElementById('fotoPlaceholder');
  if (!input) return;

  input.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if (!f) {
      img.style.display = 'none';
      ph.style.display  = 'flex';
      return;
    }
    const okType = ['image/jpeg','image/png','image/webp'].includes(f.type);
    const okSize = f.size <= 5 * 1024 * 1024; // 5MB
    if (!okType || !okSize) {
      input.value = '';
      img.style.display = 'none';
      ph.style.display  = 'flex';
      const msg = !okType ? 'Formato no permitido (usa JPG, PNG o WebP)' : 'La imagen supera 5 MB';
      if (window.Swal) Swal.fire({icon:'warning',title:msg});
      else alert(msg);
      return;
    }
    img.src = URL.createObjectURL(f);
    img.onload = () => URL.revokeObjectURL(img.src);
    img.style.display = 'block';
    ph.style.display  = 'none';
  });
})();
</script>

<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>

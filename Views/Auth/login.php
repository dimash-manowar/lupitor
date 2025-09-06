<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5" style="max-width: 520px;">
    <h2 class="text-info mb-4">Iniciar sesión</h2>
    <form method="post" action="<?= BASE_URL ?>auth/loginPost">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
        <?php if (!empty($data['next'])): ?>
            <input type="hidden" name="next" value="<?= htmlspecialchars($data['next']) ?>">
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember">Recuérdame durante 30 días</label>
        </div>
        <button class="btn btn-primary w-100">Entrar</button>
        <p class="mt-3 text-center">
            <a href="<?= BASE_URL ?>auth/forgot">¿Olvidaste tu contraseña?</a>
        </p>
        <p class="mt-3 text-center">
            ¿No tienes cuenta? <a href="<?= BASE_URL ?>auth/registro">Regístrate</a>
        </p>
    </form>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>
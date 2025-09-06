<?php require_once BASE_PATH . "Views/Templates/header.php"; ?>


<section class="container my-5">
    <h2 class="text-center text-info mb-4"><?= $data['titulo'] ?></h2>


    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Escríbenos</h5>
                    <form id="formContacto" method="post" action="<?= BASE_URL ?>contacto/enviar">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje</label>
                            <textarea name="mensaje" class="form-control" rows="4" required></textarea>
                        </div>
                        <button class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Dónde estamos</h5>
                    <p>Berriozar, Casa de Cultura</p>
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.google.com/maps?q=Berriozar&output=embed" style="border:0;" allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php require_once BASE_PATH . "Views/Templates/footer.php"; ?>
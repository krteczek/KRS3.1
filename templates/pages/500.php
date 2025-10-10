<?php
// templates/pages/500.php
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center">
            <h1 class="display-1 text-danger">500</h1>
            <h2 class="mb-4">Chyba serveru</h2>
            <p class="lead"><?= htmlspecialchars($message ?? 'Došlo k neočekávané chybě na serveru.') ?></p>
            <p>Omlouváme se za nepříjemnosti. Naši technici již byli informováni.</p>
            <a href="<?= $baseUrl ?>" class="btn btn-primary mt-3">
                <i class="fas fa-home me-2"></i><?= $backLinkText ?? 'Zpět na úvodní stránku' ?>
            </a>
        </div>
    </div>
</div>
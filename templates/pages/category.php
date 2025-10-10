<?php
// templates/pages/category.php
?>
<div class="category-content">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 mb-4">Články v kategorii: <?= htmlspecialchars($category['name']) ?></h1>
            <?php if (!empty($category['description'])): ?>
                <p class="lead"><?= htmlspecialchars($category['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($articles)): ?>
        <div class="row">
            <?php foreach ($articles as $article): ?>
                <div class="col-12 mb-4">
                    <div class="card article-card">
                        <div class="card-body">
                            <h2 class="card-title h4">
                                <a href="<?= $baseUrl ?>article/<?= $article['slug'] ?>"
                                   class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h2>

                            <div class="card-meta text-muted small mb-2">
                                <span class="me-3">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?= $article['formatted_date'] ?? date('j. n. Y', strtotime($article['published_at'])) ?>
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-user me-1"></i>
                                    <?= htmlspecialchars($article['author_name']) ?>
                                </span>
                            </div>

                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="card-text lead">
                                    <?= htmlspecialchars($article['excerpt']) ?>
                                </p>
                            <?php endif; ?>

                            <a href="<?= $baseUrl ?>article/<?= $article['slug'] ?>"
                               class="btn btn-outline-primary">
                                Číst více <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= $noArticlesMessage ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- app/views/pages/home.php -->
<div class="hero">
    <h1><?= htmlspecialchars($welcomeMessage ?? 'Vítejte') ?></h1>
</div>

<div class="articles-grid">
    <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <div class="article-content">
                    <h3>
                        <a href="<?= $baseUrl ?>clanek/<?= htmlspecialchars($article['slug']) ?>">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h3>
                    <div class="article-meta">
                        <span><?= date('j. n. Y', strtotime($article['created_at'])) ?></span>
						<span class="author_name">Autor: <?= $article['author_name']; ?></span>
                    </div>
                    <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                    <a href="<?= $baseUrl ?>clanek/<?= htmlspecialchars($article['slug']) ?>" class="read-more">
                        <?= htmlspecialchars($readMoreText ?? 'Číst více') ?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <h3><?= htmlspecialchars($noArticlesMessage ?? 'Žádné články') ?></h3>
        </div>
    <?php endif; ?>
</div>
<section class="hero">
    <h2>Vítejte na našem webu</h2>
    <p>Nejnovější články a zprávy</p>
</section>

<section class="articles-grid">
    <?php if (empty($articles)): ?>
        <div class="empty-state">
            <h3>Zatím žádné články</h3>
            <p>Zkuste to prosím později.</p>
        </div>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <div class="article-content">
                    <h3><a href="<?= $baseUrl ?>/clanek/<?= $article['slug'] ?>">
                        <?= htmlspecialchars($article['title']) ?>
                    </a></h3>
                    <div class="article-meta">
                        <span class="author"><?= htmlspecialchars($article['author_name']) ?></span>
                        <span class="date"><?= date('j. n. Y', strtotime($article['published_at'])) ?></span>
                    </div>
                    <p class="excerpt"><?= htmlspecialchars($article['excerpt'] ?? '') ?></p>
                    <a href="<?= $baseUrl ?>/clanek/<?= $article['slug'] ?>" class="read-more">Číst více →</a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
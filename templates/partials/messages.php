<?php if (!empty($messages)): ?>
    <div class="messages">
        <?php foreach ($messages as $message): ?>
            <div class="alert alert-<?= $message['type'] ?>">
                <?= $message['text'] ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
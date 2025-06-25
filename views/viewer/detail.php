<div class="card">
    <div class="card-header">
        <h1 class="card-title"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></h6>
    </div>
    <div class="card-body">
        <?php
            // カテゴリ名を取得
            $category_name = '';
            foreach ($all_categories as $category) {
                if ($category['id'] === $work['category_id']) {
                    $category_name = $category['name'];
                    break;
                }
            }
        ?>
        <p><strong>カテゴリ:</strong> <?= htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if (!empty($work['comment'])): ?>
            <div>
                <strong>コメント:</strong>
                <div class="mt-2 p-3 bg-light border rounded" style="word-wrap: break-word;">
                    <?= $work['comment'] // データソース内の<br>を信頼してそのまま表示 ?>
                </div>
            </div>
        <?php endif; ?>

        <p class="mt-3"><strong>Copyright:</strong> © <?= htmlspecialchars($work['copyright'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>タイトルID:</strong> <?= htmlspecialchars($work['title_id'], ENT_QUOTES, 'UTF-8') ?></p>
        
    </div>
</div>

<div class="text-center my-4">
    <a href="index.php" class="btn btn-secondary">&laquo; トップページに戻る</a>
</div>
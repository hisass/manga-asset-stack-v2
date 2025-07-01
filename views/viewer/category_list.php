<?php
/**
 * views/viewer/category_list.php
 * カテゴリ別作品一覧ページのテンプレート（リスト表示形式）
 */
?>

<h1 class="border-bottom pb-2 mb-4">
    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
</h1>
<?php if (empty($works)): ?>
    <div class="alert alert-info" role="alert">
        このカテゴリに登録されている作品はありません。
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($works as $work): ?>
            <a href="index.php?page=detail&id=<?= urlencode($work['work_id']) ?>" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <?php if (!empty($work['thumbnail_url'])): ?>
                            <img src="<?= htmlspecialchars($work['thumbnail_url'], ENT_QUOTES, 'UTF-8') ?>" class="work-list-thumbnail" alt="<?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <div class="work-list-thumbnail bg-light d-flex align-items-center justify-content-center">
                                <small class="text-muted">NoImg</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                        <p class="mb-1 text-muted"><small><?= htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8') ?></small></p>
                    </div>

                    <div class="flex-shrink-0">
                        <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($work['asset_count'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
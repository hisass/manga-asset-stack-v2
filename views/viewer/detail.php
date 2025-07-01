<?php
/**
 * views/viewer/detail.php
 * 作品詳細ページのテンプレート
 * * @var array $work 作品データ
 * @var array $assets アセット画像のURLリスト
 * @var array $all_categories 全カテゴリリスト（ヘッダーで使用）
 */

$page_title = '作品詳細: ' . htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8');
require_once BASE_DIR_PATH . '/views/viewer/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="h2 border-bottom pb-3 mb-4"><?php echo htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            
            <div class="mb-4">
                <p class="lead">
                    <strong>作者:</strong> <?php echo htmlspecialchars($work['author'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p>
                    <strong>コメント:</strong><br>
                    <?php echo nl2br(htmlspecialchars($work['comment'], ENT_QUOTES, 'UTF-8')); ?>
                </p>
                <p class="text-muted">
                    <small>
                        <strong>公開日:</strong> <?php echo htmlspecialchars($work['open'], ENT_QUOTES, 'UTF-8'); ?> |
                        <strong>Copyright:</strong> <?php echo htmlspecialchars($work['copyright'], ENT_QUOTES, 'UTF-8'); ?>
                    </small>
                </p>
            </div>
            
            <h3 class="mt-5">この作品のアセット</h3>
            <hr>
            <?php if (empty($assets)): ?>
                <p>利用可能なアセットはありません。</p>
            <?php else: ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    <?php foreach ($assets as $asset_url): ?>
                        <div class="col">
                            <a href="<?php echo htmlspecialchars($asset_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" title="画像を開く" class="text-decoration-none">
                                <div class="card asset-card bg-dark text-white">
                                    <img src="<?php echo htmlspecialchars($asset_url, ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="アセット画像">
                                    <div class="card-img-overlay d-flex align-items-end p-2" style="background-color: rgba(0,0,0,0.3);">
                                        <p class="card-text asset-filename mb-0 small text-truncate">
                                            <?php echo htmlspecialchars(basename($asset_url), ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
    </div>
</div>

<?php
require_once BASE_DIR_PATH . '/views/viewer/layouts/footer.php';
?>
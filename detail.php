<?php
// 設定ファイルとデータマネージャーを読み込む
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/DataManager.php';

$dataManager = new DataManager();

// URLから作品IDを取得
$work_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$work_id) {
    die('作品IDが指定されていません。');
}

// 作品データを取得
$work = $dataManager->getWorkById($work_id);

if (!$work) {
    die('指定された作品が見つかりません。');
}

/**
 * アセットのフルパスとWeb用の相対パスを検索する関数
 * @param string $category_path 作品のカテゴリフォルダ名
 * @param string $filename アセットのファイル名
 * @return array|null パスの配列、見つからなければnull
 */
function find_asset_paths($category_path, $filename) {
    // v1のパスを確認
    $v1_full_path = ASSET_PATH_V1 . '/' . $category_path . '/' . $filename;
    if (file_exists($v1_full_path)) {
        return array(
            'full_path' => $v1_full_path,
            // Webで表示するための相対パスを調整
            'web_path' => '../dmpc-materials/contents/' . $category_path . '/' . $filename,
            'version' => 'v1'
        );
    }

    // v2のパスを確認
    $v2_full_path = ASSET_PATH_V2 . '/' . $category_path . '/' . $filename;
    if (file_exists($v2_full_path)) {
        return array(
            'full_path' => $v2_full_path,
            'web_path' => 'contents/' . $category_path . '/' . $filename,
            'version' => 'v2'
        );
    }

    return null; // どちらのパスにも見つからなかった
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($work['title']) ?> - 作品詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4"><?= htmlspecialchars($work['title']) ?></h1>
        
        <table class="table table-bordered work-info-table">
            <tbody>
                <tr>
                    <th scope="row">作品名（かな）</th>
                    <td><?= htmlspecialchars($work['title_ruby']) ?></td>
                </tr>
                <tr>
                    <th scope="row">著者名</th>
                    <td><?= htmlspecialchars($work['author']) ?> (<?= htmlspecialchars($work['author_ruby']) ?>)</td>
                </tr>
                <tr>
                    <th scope="row">権利表記</th>
                    <td><?= htmlspecialchars($work['copyright']) ?></td>
                </tr>
                <tr>
                    <th scope="row">コメント</th>
                    <td><?= nl2br(htmlspecialchars($work['comment'])) ?></td>
                </tr>
            </tbody>
        </table>

        <h2 class="mt-5 mb-4">関連アセット</h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($work['assets']) && is_array($work['assets'])): ?>
                <?php foreach ($work['assets'] as $asset): ?>
                    <?php
                    // アセットのパスを検索
                    $asset_paths = find_asset_paths($work['path'], $asset['filename']);
                    ?>
                    <div class="col">
                        <div class="card h-100 asset-card">
                            <?php if ($asset_paths): ?>
                                <div class="card-header bg-success text-white">
                                    ファイルあり (<?= $asset_paths['version'] ?>)
                                </div>
                                <img src="<?= htmlspecialchars($asset_paths['web_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($asset['filename']) ?>">
                            <?php else: ?>
                                <div class="card-header bg-danger text-white">
                                    ファイルが見つかりません
                                </div>
                                <div class="card-body text-center text-muted">
                                    <p class="mb-0">Path not found:</p>
                                    <small><?= htmlspecialchars($work['path'] . '/' . $asset['filename']) ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($asset['type']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($asset['comment']) ?></p>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted"><?= htmlspecialchars($asset['filename']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted">この作品に登録されているアセットはありません。</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-5">
            <a href="admin.php" class="btn btn-secondary">&laquo; 管理画面に戻る</a>
        </div>
    </div>
</body>
</html>
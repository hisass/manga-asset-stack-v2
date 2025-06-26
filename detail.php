<?php
// PHPのエラーを画面に表示する
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. 設定ファイルを読み込む
require_once __DIR__ . '/config.php';

// 2. DataManagerを読み込む
require_once BASE_DIR_PATH . '/models/DataManager.php';

// アセットパス定数が存在するかチェック
if (!defined('ASSET_PATH_V1') || !defined('ASSET_PATH_V2')) {
    die("致命的エラー: ASSET_PATH_V1 または ASSET_PATH_V2 が config.php で定義されていません。");
}

$dataManager = new DataManager();

$work_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$work_id) { die('作品IDが指定されていません。'); }

$work = $dataManager->getWorkById($work_id);
if (!$work) { die('指定された作品が見つかりません。'); }

/**
 * アセットのフルパスとWeb用の相対パスを検索する関数
 * 【デバッグ機能付き】
 */
function find_asset_paths($category_path, $filename) {
    global $debug_messages; // デバッグメッセージを保存するグローバル変数

    // v1のパスを確認
    $v1_full_path = ASSET_PATH_V1 . '/' . $category_path . '/' . $filename;
    $debug_messages[] = "v1チェック: " . htmlspecialchars($v1_full_path); // チェックするパスを記録
    if (file_exists($v1_full_path)) {
        $debug_messages[] = "<span style='color:green;'>→ v1で発見！</span>";
        return array(
            'web_path'  => '../dmpc-materials/contents/' . $category_path . '/' . $filename,
            'version'   => 'v1'
        );
    }

    // v2のパスを確認
    $v2_full_path = ASSET_PATH_V2 . '/' . $category_path . '/' . $filename;
    $debug_messages[] = "v2チェック: " . htmlspecialchars($v2_full_path); // チェックするパスを記録
    if (file_exists($v2_full_path)) {
        $debug_messages[] = "<span style='color:green;'>→ v2で発見！</span>";
        return array(
            'web_path'  => 'contents/' . $category_path . '/' . $filename,
            'version'   => 'v2'
        );
    }
    
    $debug_messages[] = "<span style='color:red;'>→ v1, v2共に見つかりませんでした。</span>";
    return null;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($work['title']) ?> - 作品詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4"><?= htmlspecialchars($work['title']) ?></h1>
        
        <h2 class="mt-5 mb-4">関連アセット</h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($work['assets']) && is_array($work['assets'])): ?>
                <?php 
                $debug_messages = array(); // デバッグメッセージ配列を初期化
                foreach ($work['assets'] as $asset): 
                ?>
                    <?php
                    $asset_paths = find_asset_paths($work['path'], $asset['filename']);
                    ?>
                    <div class="col">
                        <div class="card h-100 asset-card">
                            </div>
                    </div>
                <?php 
                endforeach; 
                ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted">この作品に登録されているアセットはありません。</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card bg-light mt-5">
            <div class="card-header">
                <strong>デバッグ情報: パスチェックの履歴</strong>
            </div>
            <div class="card-body">
                <?php if(!empty($debug_messages)): ?>
                    <?php foreach($debug_messages as $message): ?>
                        <p class="mb-1 font-monospace small"><?= $message ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="mb-0 text-muted">アセット情報がないため、パスチェックは実行されませんでした。</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-5">
            <a href="admin.php" class="btn btn-secondary">&laquo; 管理画面に戻る</a>
        </div>
    </div>
</body>
</html>
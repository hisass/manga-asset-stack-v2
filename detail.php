<?php
// detail.php (確認用)

// このファイルが正しく読み込まれているかを確認するために、処理をここで停止させます。
die("確認：最新のdetail.phpが読み込まれました。");

// --- 以下、本来のコードが続きます ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/models/ViewerModel.php';

$work_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$work_id) {
    header("Location: index.php");
    exit;
}

$dataManager = new DataManager();
$viewerModel = new ViewerModel($dataManager);

$work = $viewerModel->getWorkById($work_id);

if (!$work) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>指定された作品は見つかりませんでした。</p><a href='index.php'>トップに戻る</a>";
    exit;
}

$assets = $viewerModel->getAssetsForWork($work);

require_once BASE_DIR_PATH . '/views/viewer/detail.php';
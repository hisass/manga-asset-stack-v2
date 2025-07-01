<?php
// ZIPダウンロード処理スクリプト

// 1. 必要なファイルを読み込む
require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/models/ViewerModel.php';

$temp_dir = __DIR__ . '/temp';
if (!is_dir($temp_dir)) {
    die('Error: temp フォルダがプロジェクトのルートに存在しません。作成してください。');
}
if (!is_writable($temp_dir)) {
    die('Error: temp フォルダに書き込み権限がありません。サーバーの設定を確認してください。');
}

// 2. パラメータを取得し、存在しなければ終了
if (!isset($_GET['work_id']) || empty($_GET['work_id'])) {
    die('Error: 作品IDが指定されていません。');
}
$work_id = $_GET['work_id'];

// 3. 必要なデータを取得
$dataManager = new DataManager();
$viewerModel = new ViewerModel($dataManager);

$work = $viewerModel->getWorkById($work_id);
if (!$work) {
    die('Error: 指定された作品が見つかりません。');
}

$assets = $viewerModel->getAssetsForWork($work);
if (empty($assets)) {
    die('Error: この作品にはダウンロード可能なアセットがありません。');
}

// 4. ZipArchiveが使えるかチェック
if (!class_exists('ZipArchive')) {
    die('Error: サーバーでZipArchiveが有効になっていません。');
}

// 5. テキストファイルの内容を生成
$info_content  = "作品名: " . $work['title'] . "\r\n";
$info_content .= "著者名: " . $work['author'] . "\r\n";
$info_content .= "Copyright: " . $work['copyright'] . "\r\n";
$info_content .= "タイトルID: " . $work['title_id'] . "\r\n";
$info_content .= "--------------------\r\n";
$info_content .= "コメント:\r\n" . $work['comment'] . "\r\n";

// 6. ZIPファイルを生成
$zip = new ZipArchive();
$zip_filename = $temp_dir . '/' . $work['directory_name'] . '_' . time() . '.zip';

if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Error: ZIPファイルの作成に失敗しました。');
}

$zip->addFromString('info.txt', $info_content);

// ▼▼▼ この部分に、ファイルが読み込み可能かどうかのチェックを追加 ▼▼▼
foreach ($assets as $asset) {
    if (file_exists($asset['server_path']) && is_readable($asset['server_path'])) {
        $zip->addFile($asset['server_path'], $asset['filename']);
    }
}
// ▲▲▲ ここまでを修正 ▲▲▲

$close_result = $zip->close();
if ($close_result !== true) {
    // ZIP保存失敗の場合は、作成した一時ファイルを削除しておく
    unlink($zip_filename);
    die('Error: ZIPファイルの保存に失敗しました。アセットファイルの読み込み権限がない可能性があります。');
}

// 7. ダウンロードを実行
if (file_exists($zip_filename)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $work['directory_name'] . '.zip"');
    header('Content-Length: ' . filesize($zip_filename));
    header('Pragma: no-cache'); 
    header('Expires: 0');
    
    readfile($zip_filename);
    
    unlink($zip_filename);
    
    exit;
} else {
    die('Error: ZIPファイルのダウンロード準備に失敗しました。(file_exists failed)');
}
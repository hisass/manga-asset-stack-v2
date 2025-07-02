<?php
// ZIPダウンロード処理スクリプト (メモリ上で処理する最終版)

// 1. 必要なファイルを読み込む
require_once __DIR__ . '/config.php';
require_once BASE_DIR_PATH . '/models/DataManager.php';
require_once BASE_DIR_PATH . '/models/ViewerModel.php';

// 2. パラメータを取得
if (!isset($_GET['work_id']) || empty($_GET['work_id'])) {
    die('Error: 作品IDが指定されていません。');
}
$work_id = $_GET['work_id'];

// 3. データを取得
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

// 6. メモリ上でZIPファイルを生成
$zip = new ZipArchive();
$tmp_file = tempnam(sys_get_temp_dir(), 'zip');

if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
    die('Error: 一時領域でのZIPファイル作成に失敗しました。');
}

// テキストファイルを追加
$zip->addFromString('info.txt', $info_content);

// 画像アセットを追加
foreach ($assets as $asset) {
    if (file_exists($asset['server_path']) && is_readable($asset['server_path'])) {
        // v1のコードを参考に、Windowsでの文字化け対策を追加
        $fileNameInZip = mb_convert_encoding($asset['filename'], 'CP932', 'UTF-8');
        $zip->addFile($asset['server_path'], $fileNameInZip);
    }
}

$zip->close();

// 7. ダウンロードを実行
$zip_filename_for_user = $work['directory_name'] . '.zip';
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename_for_user . '"');
header('Content-Length: ' . filesize($tmp_file));
header('Pragma: no-cache'); 
header('Expires: 0');

readfile($tmp_file);
unlink($tmp_file);
exit;
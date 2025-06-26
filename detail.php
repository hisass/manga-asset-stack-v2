<?php
// PHPのエラーを画面に表示する最上位の設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>ファイル読み込み デバッグモード</h1>";
echo "<p>どのステップで処理が停止するかを確認します。</p>";
echo "<hr>";

// --- ステップ1: config.phpの読み込みテスト ---
echo "<h3>ステップ1: config.php の読み込み</h3>";
echo "<p>試行中...</p>";
require_once __DIR__ . '/config.php';
echo "<p style='color:green; font-weight:bold;'>成功: config.php の読み込みが完了しました。</p>";
echo "<hr>";

// --- ステップ2: config.phpで定義された定数の内容確認 ---
echo "<h3>ステップ2: config.phpで定義された定数の確認</h3>";
if (defined('BASE_DIR_PATH')) {
    echo "<p>OK: BASE_DIR_PATH は定義されています。</p>";
    echo "<p>値: <code>" . htmlspecialchars(BASE_DIR_PATH) . "</code></p>";
} else {
    die("<p style='color:red; font-weight:bold;'>致命的エラー: config.phpを読み込みましたが、BASE_DIR_PATHが定義されていません。</p>");
}
echo "<hr>";

// --- ステップ3: DataManager.php の読み込みテスト ---
echo "<h3>ステップ3: DataManager.php の読み込み</h3>";
$data_manager_path = BASE_DIR_PATH . '/DataManager.php';
echo "<p>次のパスからファイルを読み込もうとしています: <code>" . htmlspecialchars($data_manager_path) . "</code></p>";

if (!file_exists($data_manager_path)) {
     die("<p style='color:red; font-weight:bold;'>致命的エラー: 上記パスに DataManager.php が見つかりません。BASE_DIR_PATH の定義が間違っているか、ファイルがその場所に存在しません。</p>");
}

echo "<p>試行中...</p>";
require_once $data_manager_path;
echo "<p style='color:green; font-weight:bold;'>成功: DataManager.php の読み込みが完了しました。</p>";
echo "<hr>";


echo "<h2>すべてのテストが完了しました</h2>";
echo "<p style='color:blue; font-weight:bold;'>もしこのメッセージが表示されていれば、ファイルの読み込みはすべて成功しています。</p>";

exit; // テストなので、ここで処理を終了

?>
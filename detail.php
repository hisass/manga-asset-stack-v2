<?php
// PHPのエラーを画面に表示する最上位の設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>ディレクトリ内容 確認モード</h1>";
echo "<p>プロジェクトのルートディレクトリに、PHPが認識しているファイルの一覧を表示します。</p>";
echo "<hr>";

// config.phpを読み込む
require_once __DIR__ . '/config.php';

echo "<h3>調査対象ディレクトリ</h3>";
if (defined('BASE_DIR_PATH')) {
    echo "<p><code>" . htmlspecialchars(BASE_DIR_PATH) . "</code></p>";
    
    echo "<h3>ファイル一覧</h3>";
    
    // ディレクトリをスキャンする
    $files = scandir(BASE_DIR_PATH);
    
    if ($files === false) {
        die("<p style='color:red; font-weight:bold;'>エラー: ディレクトリを読み込めませんでした。パーミッションの問題の可能性があります。</p>");
    }
    
    // 取得したファイル名をリスト表示する
    echo "<ul>";
    foreach ($files as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    echo "</ul>";

} else {
    die("<p style='color:red; font-weight:bold;'>致命的エラー: config.phpを読み込みましたが、BASE_DIR_PATHが定義されていません。</p>");
}

echo "<hr>";
echo "<h2>確認してください</h2>";
echo "<p>上記の一覧の中に、<strong>DataManager.php</strong> という名前のファイルが、大文字・小文字まで含めて完全に一致していますか？</p>";
echo "<p>もし <strong>datamanager.php</strong> や <strong>DataManger.php</strong> のように少しでも綴りが違う場合は、それがエラーの直接の原因です。</p>";
echo "<p>その場合は、実際のファイル名に合わせて、require_onceの行を修正する必要があります。</p>";

exit;

?>
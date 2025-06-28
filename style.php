<?php
// PHPでCSSを配信するためのプロキシスクリプト

// 1. HTTPヘッダーでContent-Typeを「text/css」かつ「UTF-8」であると明示する
header('Content-Type: text/css; charset=UTF-8');

// 2. 実際のCSSファイルのパスを指定する
// このファイルは2種類あるため、どちらを読み込むか判定する
$admin_css_path = __DIR__ . '/public/css/style.css';
$viewer_css_path = __DIR__ . '/assets/css/style.css'; // こちらが管理画面用

// 3. 管理画面用のCSSファイルの中身を読み込んで、そのまま出力（echo）する
if (file_exists($viewer_css_path)) {
    echo file_get_contents($viewer_css_path);
}

// 注意：このファイルにはPHPの閉じタグ ?> は不要です
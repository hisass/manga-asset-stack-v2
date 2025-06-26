<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【JSON文法エラー自動修正機能付き】
 */

// このコンバータがあるディレクトリのパス
define('CURRENT_DIR', dirname(__FILE__));

$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';

// 1. 旧データを読み込む
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . $old_data_path);
}
$old_json_string = file_get_contents($old_data_path);

// 2. 文字列全体の文字コードを Shift_JIS から UTF-8 に変換する
$utf8_json_string = mb_convert_encoding($old_json_string, 'UTF-8', 'SJIS-win');


// 3.【最重要修正】JSONをデコードする前に、よくある文法エラーを自動的に修正（クリーニング）する
// JavaScript形式のコメント (// ... や /* ... */) を削除
$cleaned_json_string = preg_replace('!/\*.*?\*/!s', '', $utf8_json_string);
$cleaned_json_string = preg_replace('!//.*!', '', $cleaned_json_string);
// 配列やオブジェクトの最後の要素にある余分なカンマを削除
$cleaned_json_string = preg_replace('/,\s*([\]\}])/', '$1', $cleaned_json_string);


// 4. クリーニングしたUTF-8文字列をデコードする
$old_data = json_decode($cleaned_json_string, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // ここでエラーが出た場合は、手動で修正が必要な、より稀な文法エラーが残っている可能性がある
    $error_message = 'Error: sozai.jsonのJSONデコードに失敗しました。自動クリーニング後も、JSONの形式が正しくありません。';
    
    // PHP 5.5以降で使える詳細なエラー表示
    if (function_exists('json_last_error_msg')) {
        $error_message .= "\n詳細: " . json_last_error_msg();
    }
    
    die($error_message);
}

// 5. 新しいデータ構造を作成
$new_data = array(
    'categories' => array(),
    'works' => array()
);

// カテゴリデータを変換
if (isset($old_data['category']) && is_array($old_data['category'])) {
    foreach ($old_data['category'] as $old_cat) {
        $new_data['categories'][] = array(
            'id' => isset($old_cat['id']) ? $old_cat['id'] : '',
            'name' => isset($old_cat['name']) ? $old_cat['name'] : '',
            'alias' => isset($old_cat['alias']) ? $old_cat['alias'] : '',
            'directory_name' => isset($old_cat['directory_name']) ? $old_cat['directory_name'] : '',
            'title_count' => isset($old_cat['title_count']) ? (int)$old_cat['title_count'] : 0,
        );
    }
}

// 作品データを変換
if (isset($old_data['data']) && is_array($old_data['data'])) {
    foreach ($old_data['data'] as $old_work) {
        $new_data['works'][] = array(
            'work_id' => isset($old_work['work_id']) ? $old_work['work_id'] : '',
            'title' => isset($old_work['title']) ? $old_work['title'] : '',
            'title_ruby' => isset($old_work['title_ruby']) ? $old_work['title_ruby'] : '',
            'author' => isset($old_work['author']) ? $old_work['author'] : '',
            'author_ruby' => isset($old_work['author_ruby']) ? $old_work['author_ruby'] : '',
            'category_id' => isset($old_work['category_id']) ? $old_work['category_id'] : '',
            'comment' => isset($old_work['comment']) ? $old_work['comment'] : '',
            'title_id' => isset($old_work['title_id']) ? $old_work['title_id'] : '',
            'copyright' => isset($old_work['copyright']) ? $old_work['copyright'] : '',
            'open' => isset($old_work['open']) ? $old_work['open'] : '',
            'path' => isset($old_work['path']) ? $old_work['path'] : '',
            'assets' => array()
        );
    }
}

// 6. 新しいJSONファイルとして保存
$new_json_string = json_encode($new_data);

if ($new_json_string === false) {
    die("Error: 新データ(sozai_v2.json)のJSONエンコードに失敗しました。");
}

$result = file_put_contents($new_data_path, $new_json_string);

if ($result === false) {
    echo "Error: sozai_v2.json の書き込みに失敗しました。\n";
} else {
    echo "Success: sozai_v2.json の生成が完了しました。\n";
    echo "Total categories: " . count($new_data['categories']) . "\n";
    echo "Total works: " . count($new_data['works']) . "\n";
}

?>
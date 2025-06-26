<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【特殊なファイル構造と文字コードに両対応した最終版】
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('CURRENT_DIR', __DIR__);
$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';

// 1. 旧データをテキストとして読み込む
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . $old_data_path);
}
$raw_string = file_get_contents($old_data_path);

// 2. 文字列全体の文字コードを Shift_JIS から UTF-8 に変換する
$utf8_string = mb_convert_encoding($raw_string, 'UTF-8', 'SJIS-win');

// 3. 正規表現を使い、"data"と"category"のブロックを文字列として抽出
// "data"の抽出
preg_match('/"data":\s*(\[.*?\])/s', $utf8_string, $data_matches);
// "category"の抽出
preg_match('/"category":\s*(\[.*?\])/s', $utf8_string, $category_matches);

if (empty($data_matches[1]) || empty($category_matches[1])) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックを見つけられませんでした。");
}

$data_json_string = $data_matches[1];
$category_json_string = $category_matches[1];

// 4. 抽出した各ブロックを個別にデコードする
$old_data_array = json_decode($data_json_string, true);
if ($old_data_array === null) {
    die("Error: 'data'ブロックのJSONデコードに失敗しました。詳細: " . json_last_error_msg());
}

$old_category_array = json_decode($category_json_string, true);
if ($old_category_array === null) {
    die("Error: 'category'ブロックのJSONデコードに失敗しました。詳細: " . json_last_error_msg());
}


// 5. 新しいデータ構造を組み立てる
$new_data = array(
    'categories' => array(),
    'works' => array()
);

// カテゴリデータを変換
$category_map = array(); // 旧IDと新IDの対応表
$category_header = array_flip($old_category_array[0]); // ヘッダー行をキーに変換
foreach (array_slice($old_category_array, 1) as $old_cat) {
    $cat_name = isset($old_cat[$category_header['category']]) ? $old_cat[$category_header['category']] : '';
    $new_id = isset($old_cat[$category_header['category_id']]) ? 'cat_' . str_pad($old_cat[$category_header['category_id']], 3, '0', STR_PAD_LEFT) : 'cat_'.uniqid();
    
    // カテゴリIDと名前の対応を記録
    $category_map[$cat_name] = $new_id;

    $new_data['categories'][] = array(
        'id'             => $new_id,
        'name'           => $cat_name,
        'alias'          => isset($old_cat[$category_header['alias']]) ? $old_cat[$category_header['alias']] : '',
        'directory_name' => '', // 旧データにないため空
        'title_count'    => isset($old_cat[$category_header['title_count']]) ? (int)$old_cat[$category_header['title_count']] : 0,
    );
}


// 作品データを変換
$work_header = array_flip($old_data_array[0]); // ヘッダー行をキーに変換
foreach (array_slice($old_data_array, 1) as $old_work) {
    $category_name_from_work = isset($old_work[$work_header['category']]) ? $old_work[$work_header['category']] : '';
    $new_category_id = isset($category_map[$category_name_from_work]) ? $category_map[$category_name_from_work] : '';

    $work_id = !empty($old_work[$work_header['title_id']]) ? (string)$old_work[$work_header['title_id']] : 'work_' . uniqid();

    $new_data['works'][] = array(
        'work_id'     => $work_id,
        'title'       => isset($old_work[$work_header['title']]) ? $old_work[$work_header['title']] : '',
        'title_ruby'  => isset($old_work[$work_header['title_ruby']]) ? $old_work[$work_header['title_ruby']] : '',
        'author'      => isset($old_work[$work_header['author']]) ? $old_work[$work_header['author']] : '',
        'author_ruby' => isset($old_work[$work_header['author_ruby']]) ? $old_work[$work_header['author_ruby']] : '',
        'category_id' => $new_category_id,
        'comment'     => isset($old_work[$work_header['comment']]) ? $old_work[$work_header['comment']] : '',
        'title_id'    => $work_id,
        'copyright'   => isset($old_work[$work_header['copyright']]) ? $old_work[$work_header['copyright']] : '',
        'open'        => isset($old_work[$work_header['open']]) ? date('Y-m-d', strtotime($old_work[$work_header['open']])) : '',
        'path'        => isset($old_work[$work_header['path']]) ? str_replace('contents/', '', $old_work[$work_header['path']]) : '',
        'assets'      => array()
    );
}


// 6. 新しいJSONファイルとして保存 (PHP 5.3互換)
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
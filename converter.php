<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【特殊な二次元配列構造 / 文字コード / PHP5.3互換 すべてに対応した最終版】
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('CURRENT_DIR', __DIR__);
// dataフォルダがconverter.phpと同じ階層にあると想定
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
preg_match('/"data":\s*(\[.*?\])/s', $utf8_string, $data_matches);
preg_match('/"category":\s*(\[.*?\])/s', $utf8_string, $category_matches);

if (empty($data_matches[1]) || empty($category_matches[1])) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックを見つけられませんでした。");
}

$data_json_string = $data_matches[1];
$category_json_string = $category_matches[1];

// 4. 抽出した各ブロックを個別にデコードする
$old_data_array = json_decode($data_json_string, true);
if ($old_data_array === null) {
    die("Error: 'data'ブロックのJSONデコードに失敗しました。code: " . json_last_error());
}

$old_category_array = json_decode($category_json_string, true);
if ($old_category_array === null) {
    die("Error: 'category'ブロックのJSONデコードに失敗しました。code: " . json_last_error());
}


// 5. 新しいデータ構造を組み立てる
$new_data = array(
    'categories' => array(),
    'works' => array()
);

// カテゴリデータを変換
$category_map = array(); // 旧カテゴリ名と新IDの対応表
$category_header = array_flip($old_category_array[0]);
foreach (array_slice($old_category_array, 1) as $old_cat) {
    $cat_name = isset($old_cat[$category_header['alias']]) ? $old_cat[$category_header['alias']] : '';
    // カテゴリIDを生成 (元のsozai.jsonにはIDがないため、名前から生成)
    $new_id = 'cat_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cat_name));
    
    $category_map[$cat_name] = $new_id;

    $new_data['categories'][] = array(
        'id'             => $new_id,
        'name'           => $cat_name,
        'alias'          => $cat_name,
        'directory_name' => '', 
        'title_count'    => isset($old_cat[$category_header['title_count']]) ? (int)$old_cat[$category_header['title_count']] : 0,
    );
}


// 作品データを変換
$work_header = array_flip($old_data_array[0]); // ["title" => 0, "title_ruby" => 1, ...]
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
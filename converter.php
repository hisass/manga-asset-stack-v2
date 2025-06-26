<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【特殊なファイル構造 / 不正な内部文字 / 文字コード / PHP5.3互換 すべてに対応した最終版】
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

/**
 * JSON文字列から配列を安全に抽出する関数
 * json_decodeが失敗するような不正な文字列に対応
 */
function safe_json_decode_array($json_string) {
    // 前後の不要な文字や空白を除去
    $trimmed = trim($json_string);
    // 先頭の '[' と末尾の ']' を削除
    $inner = substr($trimmed, 1, -1);

    // 配列の各要素（"..."または数値）を正規表現で抽出
    preg_match_all('/"((?:[^"]|\\")*)"|(-?\d+\.?\d*)/', $inner, $matches);

    // preg_match_allは$matches[0]に全体一致、$matches[1]と$matches[2]に個別キャプチャを入れる
    $results = array();
    foreach ($matches[0] as $i => $match) {
        if ($matches[2][$i] !== '') {
            // 数値の場合
            $results[] = is_int($matches[2][$i] + 0) ? (int)$matches[2][$i] : (float)$matches[2][$i];
        } else {
            // 文字列の場合 (エスケープされた\"を"に戻す)
            $results[] = str_replace('\"', '"', $matches[1][$i]);
        }
    }
    return $results;
}


// 4. 【最重要修正】各ブロックを安全な方法で解析する
$data_array_of_strings = preg_split('/(?<=]),\s*(?=\[)/', substr(trim($data_matches[1]), 1, -1));
$category_array_of_strings = preg_split('/(?<=]),\s*(?=\[)/', substr(trim($category_matches[1]), 1, -1));

$old_data_array = array();
foreach($data_array_of_strings as $row_string) {
    $old_data_array[] = safe_json_decode_array($row_string);
}

$old_category_array = array();
foreach($category_array_of_strings as $row_string) {
    $old_category_array[] = safe_json_decode_array($row_string);
}


// 5. 新しいデータ構造を組み立てる
$new_data = array(
    'categories' => array(),
    'works' => array()
);

$category_map = array();
$category_header = array_flip($old_category_array[0]);
foreach (array_slice($old_category_array, 1) as $old_cat) {
    $cat_name = isset($old_cat[$category_header['alias']]) ? $old_cat[$category_header['alias']] : '';
    // カテゴリIDを一意に生成
    $new_id = 'cat_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cat_name)) . '_' . (isset($old_cat[0]) ? $old_cat[0] : uniqid());
    
    $category_map[$cat_name] = $new_id;

    $new_data['categories'][] = array(
        'id'             => $new_id,
        'name'           => $cat_name,
        'alias'          => $cat_name,
        'directory_name' => '',
        'title_count'    => isset($old_cat[$category_header['title_count']]) ? (int)$old_cat[$category_header['title_count']] : 0,
    );
}


$work_header = array_flip($old_data_array[0]);
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
<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【PHP 5.3互換 最終版】
 */

// メモリ上限を一時的に512MBに引き上げ
ini_set('memory_limit', '512M'); 
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');

define('CURRENT_DIR', __DIR__);
$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';
$log_path = CURRENT_DIR . '/logs/converter.log';

file_put_contents($log_path, "");
function write_log($message) {
    global $log_path;
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $log_path);
}

// 1. 旧データファイルを一括で読み込み、JSONデコードする
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . htmlspecialchars($old_data_path));
}
$old_json_string = file_get_contents($old_data_path);
if ($old_json_string === false) {
    die("Error: 旧データファイルの読み込みに失敗しました。");
}
$old_data = json_decode($old_json_string, true);
if (function_exists('json_last_error') && json_last_error() !== JSON_ERROR_NONE) {
    die("Error: JSONデータの解析に失敗しました。エラーコード: " . json_last_error());
}
unset($old_json_string);

// 2. カテゴリデータを新フォーマットに変換
$new_categories = array(); // 【PHP 5.3互換対応】 array()構文を使用
$category_name_to_id_map = array();
$skipped_categories_count = 0;

if (!empty($old_data['category'])) {
    $category_header = array_shift($old_data['category']);
    $category_header_count = count($category_header);

    foreach ($old_data['category'] as $index => $old_cat_row) {
        if (count($old_cat_row) !== $category_header_count) {
            write_log("Warning: カテゴリデータの列数が不正です (行: " . ($index + 2) . ")。スキップします。");
            $skipped_categories_count++;
            continue;
        }
        $cat_data = array_combine($category_header, $old_cat_row);
        $cat_name = trim($cat_data['category']);
        if (empty($cat_name)) {
            $skipped_categories_count++;
            continue;
        }
        $new_id = 'cat_' . preg_replace('/[^a-z0-9_]+/i', '', strtolower(str_replace(' ', '_', $cat_name)));
        $counter = 2;
        $original_new_id = $new_id;
        while(isset($new_categories[$new_id])) {
            $new_id = $original_new_id . '_' . $counter++;
        }
        if (!isset($category_name_to_id_map[$cat_name])) {
            $category_name_to_id_map[$cat_name] = $new_id;
        }
        $alias = trim($cat_data['alias']);
        if (!empty($alias) && !isset($category_name_to_id_map[$alias])) {
             $category_name_to_id_map[$alias] = $new_id;
        }
        $new_categories[$new_id] = array( // 【PHP 5.3互換対応】
            'id' => $new_id, 'name' => $cat_name, 'alias' => $alias,
            'directory_name' => '', 'title_count' => (int)$cat_data['title_count']
        );
    }
}

// 3. 作品データを新フォーマットに変換
$new_works = array(); //【PHP 5.3互換対応】
$skipped_works_count = 0;

if (!empty($old_data['data'])) {
    $work_header = array_shift($old_data['data']);
    $work_header_count = count($work_header);

    foreach ($old_data['data'] as $index => $old_work_row) {
        if (count($old_work_row) !== $work_header_count) {
            write_log("Warning: 作品データの列数が不正です (行: " . ($index + 2) . ", Title: " . (isset($old_work_row[0]) ? $old_work_row[0] : 'N/A') . ")。スキップします。");
            $skipped_works_count++;
            continue;
        }
        $work_data = array_combine($work_header, $old_work_row);
        $work_id = 'work_' . uniqid(rand(), true);
        $category_name_from_work = trim($work_data['category']);
        $new_category_id = isset($category_name_to_id_map[$category_name_from_work]) ? $category_name_to_id_map[$category_name_from_work] : '';
        $open_date = '';
        if (!empty($work_data['open'])) {
             $timestamp = strtotime($work_data['open']);
             $open_date = ($timestamp !== false) ? date('Y-m-d', $timestamp) : '';
        }
        $new_works[$work_id] = array( //【PHP 5.3互換対応】
            'work_id'        => $work_id,
            'title'          => (string)trim($work_data['title']),
            'title_ruby'     => (string)trim($work_data['title_ruby']),
            'author'         => (string)trim($work_data['author']),
            'author_ruby'    => (string)trim($work_data['author_ruby']),
            'category_id'    => $new_category_id,
            'comment'        => str_replace(array('</br>','<br>'), "\n", trim($work_data['comment'])),
            'title_id'       => (string)trim($work_data['title_id']),
            'directory_name' => str_replace('contents/', '', trim($work_data['path'])),
            'copyright'      => (string)trim($work_data['copyright']),
            'open'           => $open_date,
            'assets'         => array() //【PHP 5.3互換対応】
        );
    }
}
unset($old_data);

// 4. 最終的なデータ構造を組み立て、JSONファイルとして保存
$new_data_structure = array('categories' => $new_categories, 'works' => $new_works); //【PHP 5.3互換対応】

// 【PHP 5.3互換対応】json_encodeのオプションが使えないため、Unicodeエスケープを自前で解除
$json_string = json_encode($new_data_structure);
$unescaped_json_string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/',
    function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    },
    $json_string
);
// パスのスラッシュ「\/」のエスケープを解除
$unescaped_json_string = str_replace('\\/', '/', $unescaped_json_string);


if ($unescaped_json_string === false) {
    die("Error: JSONのエンコードに失敗しました。");
}
$result = file_put_contents($new_data_path, $unescaped_json_string);
if ($result === false) {
    die("Error: sozai_v2.json の書き込みに失敗しました。");
}

// 5. 結果をブラウザに出力
header('Content-Type: text/plain; charset=utf-8');
echo "Success: sozai_v2.json の生成が完了しました。(ファイルサイズ: " . strlen($unescaped_json_string) . " bytes)\n";
echo "---------------------\n";
echo "Total categories processed: " . count($new_data_structure['categories']) . " (skipped: " . $skipped_categories_count . ")\n";
echo "Total works processed: " . count($new_data_structure['works']) . " (skipped: " . $skipped_works_count . ")\n";
if($skipped_categories_count > 0 || $skipped_works_count > 0) {
    echo "Warning: スキップされたデータがあります。詳細は " . htmlspecialchars($log_path) . " を確認してください。\n";
}
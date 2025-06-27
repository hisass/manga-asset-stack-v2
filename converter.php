<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【データ構造の最適化、不整合、文字化け、パフォーマンス、PHP5.3互換の全てに対応した最終確定版】
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

define('CURRENT_DIR', __DIR__);
$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';

// 1. 旧データファイルをUTF-8として読み込む (文字コード変換は行わない)
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . htmlspecialchars($old_data_path));
}
$raw_utf8_string = file_get_contents($old_data_path);
if (empty($raw_utf8_string)) {
    die("Error: 旧データファイルが空です。");
}

// 2. 正規表現のマッチング用に、改行などを削除
$clean_utf8_string = str_replace(array("\r\n", "\r", "\t"), '', $raw_utf8_string);

// 3. 正規表現を使い、"data"と"category"のブロックを文字列として抽出
if (!preg_match('/"data":(\[.*\]),"category"/u', $clean_utf8_string, $data_matches) ||
    !preg_match('/"category":(\[.*\]),"news"/u', $clean_utf8_string, $category_matches)) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックを特定できませんでした。");
}

$data_block_str = $data_matches[1];
$category_block_str = $category_matches[1];

/**
 * JSON配列風の文字列を解析してPHP配列に変換する関数
 */
function parse_json_like_array($block_string) {
    $result_list = array();
    $inner_content = substr(trim($block_string), 1, -1);
    $rows_str_array = preg_split('/(?<=]),\s*(?=\[)/u', $inner_content);

    foreach ($rows_str_array as $row_str) {
        $row_items = str_getcsv(substr(trim($row_str), 1, -1), ',', '"');
        $converted_values = array();
        foreach ($row_items as $value) {
            $trimmed_val = trim($value);
            if (is_numeric($trimmed_val) && strpos($trimmed_val, "0") !== 0) {
                 $converted_values[] = strpos($trimmed_val, '.') === false ? (int)$trimmed_val : (float)$trimmed_val;
            } else {
                $converted_values[] = str_replace('\"', '"', $value);
            }
        }
        $result_list[] = $converted_values;
    }
    return $result_list;
}

$old_data_array = parse_json_like_array($data_block_str);
$old_category_array = parse_json_like_array($category_block_str);

// 4. 新しいデータ構造を組み立てる
// 【重要】パフォーマンス向上のため、worksとcategoriesをIDをキーにした連想配列に変更
$new_data = array(
    'categories' => array(),
    'works' => array()
);
$category_map = array();

if (!empty($old_category_array) && count($old_category_array) > 1) {
    $category_header_raw = array_shift($old_category_array);
    $category_header = array_flip($category_header_raw);

    foreach ($old_category_array as $old_cat) {
        if(count($old_cat) !== count($category_header_raw)) continue;
        
        $cat_name = isset($category_header['category']) ? $old_cat[$category_header['category']] : '';
        if (empty($cat_name)) continue;

        $alias = isset($category_header['alias']) ? $old_cat[$category_header['alias']] : $cat_name;
        $title_count = isset($category_header['title_count']) ? (int)$old_cat[$category_header['title_count']] : 0;
        
        $new_id = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name));
        $counter = 2;
        while(isset($new_data['categories'][$new_id])) {
            $new_id = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name)) . '_' . $counter++;
        }
        
        $category_map[$cat_name] = $new_id;
        $new_data['categories'][$new_id] = array( // IDをキーにする
            'id'             => $new_id,
            'name'           => $cat_name,
            'alias'          => $alias,
            'directory_name' => '',
            'title_count'    => $title_count
        );
    }
}

if (!empty($old_data_array) && count($old_data_array) > 1) {
    $work_header_raw = array_shift($old_data_array);
    $work_header = array_flip($work_header_raw);

    foreach ($old_data_array as $old_work) {
        if(count($old_work) !== count($work_header_raw)) continue;
        
        $get_value = function($key) use ($old_work, $work_header) {
            return (isset($work_header[$key]) && isset($old_work[$work_header[$key]])) ? $old_work[$work_header[$key]] : '';
        };

        $original_title_id = $get_value('title_id');
        $work_id = !empty($original_title_id) ? (string)$original_title_id : 'work_' . uniqid();
        $category_name_from_work = $get_value('category');
        $new_category_id = isset($category_map[$category_name_from_work]) ? $category_map[$category_name_from_work] : '';
        
        $open_date = '';
        if ($get_value('open')) {
             $timestamp = strtotime($get_value('open'));
             $open_date = ($timestamp !== false) ? date('Y-m-d', $timestamp) : '';
        }

        $new_data['works'][$work_id] = array( // work_idをキーにする
            'work_id'     => $work_id,
            'title'       => $get_value('title'),
            'title_ruby'  => $get_value('title_ruby'),
            'author'      => $get_value('author'),
            'author_ruby' => $get_value('author_ruby'),
            'category_id' => $new_category_id,
            'comment'     => $get_value('comment'),
            'title_id'    => $original_title_id, // 旧データの値をそのまま格納
            'copyright'   => $get_value('copyright'),
            'open'        => $open_date,
            'path'        => str_replace('contents/', '', $get_value('path')),
            'assets'      => array()
        );
    }
}

// 5. 新しいJSONファイルとして保存 (【重要】改行・空白なし)
$json_string = json_encode($new_data);

// PHP 5.3ではJSON_UNESCAPED_UNICODEが使えないため、手動でデコード
$unescaped_json_string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}, $json_string);

$result = file_put_contents($new_data_path, $unescaped_json_string);

if ($result === false) {
    echo "Error: sozai_v2.json の書き込みに失敗しました。\n";
} else {
    echo "Success: sozai_v2.json の生成が完了しました。(ファイルサイズ: " . strlen($unescaped_json_string) . " bytes)\n";
    echo "Total categories: " . count($new_data['categories']) . "\n";
    echo "Total works: " . count($new_data['works']) . "\n";
}
?>
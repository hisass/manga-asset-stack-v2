<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【sozai.jsonの特殊構造、データ欠落、仕様不備、パフォーマンスの全てに対応した最終確定版】
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

define('CURRENT_DIR', __DIR__);
$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';

// 1. 旧データファイルを1行ずつの配列として読み込む
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . htmlspecialchars($old_data_path));
}
$lines = file($old_data_path, FILE_IGNORE_NEW_LINES); // 改行は無視して読み込む
if ($lines === false) {
    die("Error: 旧データファイルが読み込めません。");
}

$raw_utf8_string = implode('', $lines); // 配列を再度一つの文字列に結合

// 2. データブロックを抽出
function extract_data_block($key, $string) {
    $start_str = '"' . $key . '":[';
    $start_pos = strpos($string, $start_str);
    if ($start_pos === false) return null;
    $block_start = $start_pos + strlen($start_str);
    $open_brackets = 1;
    $len = strlen($string);
    for ($i = $block_start; $i < $len; $i++) {
        $char = $string[$i];
        if ($char == '[') {
            $open_brackets++;
        } elseif ($char == ']') {
            $open_brackets--;
            if ($open_brackets == 0) {
                return substr($string, $block_start, $i - $block_start);
            }
        }
    }
    return null;
}

$data_block_str = extract_data_block('data', $raw_utf8_string);
$category_block_str = extract_data_block('category', $raw_utf8_string);

if ($data_block_str === null || $category_block_str === null) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックの抽出に失敗しました。");
}

/**
 * JSON配列風の文字列を安全に解析する関数
 */
function parse_json_like_array($block_string) {
    $result_list = array();
    preg_match_all('/\[(.*?)\]/s', $block_string, $matches);
    
    foreach ($matches[1] as $row_content) {
        $values = str_getcsv($row_content, ',', '"');
        $converted_values = array();
        foreach ($values as $value) {
            $converted_values[] = str_replace('\"', '"', $value);
        }
        $result_list[] = $converted_values;
    }
    return $result_list;
}

$old_data_array = parse_json_like_array($data_block_str);
$old_category_array = parse_json_like_array($category_block_str);

// 3. 新しいデータ構造を組み立てる
$new_data = array('categories' => array(), 'works' => array());
$category_map = array();

if (!empty($old_category_array)) {
    $category_header_raw = array_shift($old_category_array);
    $category_header = array_flip($category_header_raw);

    foreach ($old_category_array as $old_cat) {
        if(count($old_cat) !== count($category_header_raw)) continue;
        $get_value = function($key) use ($old_cat, $category_header) {
            return (isset($category_header[$key]) && isset($old_cat[$category_header[$key]])) ? trim($old_cat[$category_header[$key]]) : '';
        };
        $cat_name = $get_value('category') ?: $get_value('alias');
        if (empty($cat_name)) continue;
        $new_id = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name));
        $counter = 2;
        while(isset($new_data['categories'][$new_id])) {
            $new_id = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name)) . '_' . $counter++;
        }
        $category_map[$cat_name] = $new_id;
        $new_data['categories'][$new_id] = array(
            'id' => $new_id, 'name' => $cat_name, 'alias' => $get_value('alias') ?: $cat_name,
            'directory_name' => '', 'title_count' => (int)$get_value('title_count')
        );
    }
}

if (!empty($old_data_array)) {
    $work_header_raw = array_shift($old_data_array);
    $work_header = array_flip($work_header_raw);

    foreach ($old_data_array as $old_work) {
        if(count($old_work) !== count($work_header_raw)) continue;
        $get_value = function($key) use ($old_work, $work_header) {
            return (isset($work_header[$key]) && isset($old_work[$work_header[$key]])) ? trim($old_work[$work_header[$key]]) : '';
        };

        $original_title_id = $get_value('title_id');
        $work_id = 'work_' . uniqid(rand(), true);
        $category_name_from_work = $get_value('category');
        $new_category_id = isset($category_map[$category_name_from_work]) ? $category_map[$category_name_from_work] : '';
        $open_date = '';
        if ($get_value('open')) {
             $timestamp = strtotime($get_value('open'));
             $open_date = ($timestamp !== false) ? date('Y-m-d', $timestamp) : '';
        }
        $directory_name = str_replace('contents/', '', $get_value('path'));

        $new_data['works'][$work_id] = array(
            'work_id' => $work_id, 'title' => $get_value('title'), 'title_ruby' => $get_value('title_ruby'),
            'author' => $get_value('author'), 'author_ruby' => $get_value('author_ruby'),
            'category_id' => $new_category_id, 'comment' => $get_value('comment'),
            'title_id' => $original_title_id, 'directory_name' => $directory_name,
            'copyright' => $get_value('copyright'), 'open' => $open_date, 'assets' => array()
        );
    }
}

// 4. 新しいJSONファイルとして保存 (改行・空白なしの最小形式)
$json_string = json_encode($new_data);
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
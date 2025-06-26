<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【sozai.jsonの構造的な問題、文字化け、PHP5.3互換の全てに対応した最終確定版】
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

//【重要】マルチバイト文字のエンコーディングを設定
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

// --- 設定項目 ---
define('CURRENT_DIR', __DIR__);
$old_data_path = CURRENT_DIR . '/data/sozai.json';
$new_data_path = CURRENT_DIR . '/data/sozai_v2.json';
// --- 設定項目ここまで ---


// 1. 旧データファイルをテキストとして読み込む
if (!file_exists($old_data_path)) {
    die("Error: 旧データファイルが見つかりません: " . htmlspecialchars($old_data_path));
}
$raw_string = file_get_contents($old_data_path);
if (empty($raw_string)) {
    die("Error: 旧データファイルが空です。");
}

// 2. 文字コードをShift_JISからUTF-8に変換
$utf8_string = mb_convert_encoding($raw_string, 'UTF-8', 'SJIS-win');


/**
 * データの塊（"key":[...]）を安全に抽出する関数
 */
function extract_data_block($key, $string) {
    // 開始位置を特定
    $start_str = '"' . $key . '":';
    $start_pos = strpos($string, $start_str);
    if ($start_pos === false) return null;
    
    // データブロックの開始 '[' を探す
    $block_start = strpos($string, '[', $start_pos);
    if ($block_start === false) return null;

    // 対応する閉じ括弧 ']' を探すためのカウンタ
    $open_brackets = 0;
    $len = strlen($string);
    for ($i = $block_start; $i < $len; $i++) {
        if ($string[$i] == '[') {
            $open_brackets++;
        } elseif ($string[$i] == ']') {
            $open_brackets--;
            if ($open_brackets == 0) {
                // 対応する閉じ括弧が見つかった
                return substr($string, $block_start, $i - $block_start + 1);
            }
        }
    }
    return null; // 対応する閉じ括弧が見つからなかった
}

// 4. データブロックを文字列として抽出
$data_block_str = extract_data_block('data', $utf8_string);
$category_block_str = extract_data_block('category', $utf8_string);

if ($data_block_str === null || $category_block_str === null) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックの抽出に失敗しました。");
}

/**
 * JSONの配列形式の文字列を、1行ずつ安全にPHPの配列に変換する関数
 */
function parse_json_like_array($block_string) {
    $result_list = array();
    $inner_content = substr(trim($block_string), 1, -1);
    
    // "],[" という区切り文字で各行の配列文字列に分割
    $rows_str_array = preg_split('/(?<=]),\s*(?=\[)/u', $inner_content);

    foreach ($rows_str_array as $row_str) {
        $row_items = array();
        $row_content = substr(trim($row_str), 1, -1);
        
        // CSVとして行をパース
        $row_items = str_getcsv($row_content, ',', '"');

        // 数値は数値型に変換
        foreach ($row_items as $key => $value) {
            if (is_numeric($value)) {
                $row_items[$key] = strpos($value, '.') === false ? (int)$value : (float)$value;
            }
        }
        $result_list[] = $row_items;
    }
    return $result_list;
}


// 5. テキスト解析関数を使ってデータを配列に変換
$old_data_array = parse_json_like_array($data_block_str);
$old_category_array = parse_json_like_array($category_block_str);

// 6. 新しいデータ構造を組み立てる
$new_data = array(
    'categories' => array(),
    'works' => array()
);
$category_map = array();
if (!empty($old_category_array) && count($old_category_array) > 1) {
    $category_header_raw = array_shift($old_category_array); // ヘッダー行を取得して削除
    $category_header = array_flip($category_header_raw);

    foreach ($old_category_array as $old_cat) {
        // ヘッダーとカラム数が一致しない行はスキップ
        if(count($old_cat) != count($category_header_raw)) continue;

        $cat_name_key = isset($category_header['category']) ? 'category' : '';
        $alias_key = isset($category_header['alias']) ? 'alias' : '';
        $count_key = isset($category_header['title_count']) ? 'title_count' : '';

        if(empty($cat_name_key) || !isset($old_cat[$category_header[$cat_name_key]])) continue;
        
        $cat_name = trim($old_cat[$category_header[$cat_name_key]]);
        if (empty($cat_name)) continue;

        $new_id_base = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name));
        $new_id = $new_id_base;
        $counter = 2;
        while (in_array($new_id, array_map(function($c) { return $c['id']; }, $new_data['categories']))) {
            $new_id = $new_id_base . '_' . $counter++;
        }

        $category_map[$cat_name] = $new_id;
        $new_data['categories'][] = array(
            'id'             => $new_id,
            'name'           => $cat_name,
            'alias'          => (!empty($alias_key) && isset($old_cat[$category_header[$alias_key]])) ? $old_cat[$category_header[$alias_key]] : $cat_name,
            'directory_name' => '',
            'title_count'    => (!empty($count_key) && isset($old_cat[$category_header[$count_key]])) ? (int)$old_cat[$category_header[$count_key]] : 0
        );
    }
}

if (!empty($old_data_array) && count($old_data_array) > 1) {
    $work_header_raw = array_shift($old_data_array); // ヘッダー行を取得して削除
    $work_header = array_flip($work_header_raw);

    foreach ($old_data_array as $old_work) {
         // ヘッダーとカラム数が一致しない行はスキップ
        if(count($old_work) != count($work_header_raw)) continue;

        $category_name_from_work = isset($work_header['category']) && isset($old_work[$work_header['category']]) ? trim($old_work[$work_header['category']]) : '';
        $new_category_id = isset($category_map[$category_name_from_work]) ? $category_map[$category_name_from_work] : '';
        
        $work_id_val = isset($work_header['title_id']) && isset($old_work[$work_header['title_id']]) ? $old_work[$work_header['title_id']] : '';
        $work_id = !empty($work_id_val) ? (string)$work_id_val : 'work_' . uniqid();

        $open_date = '';
        if (isset($work_header['open']) && isset($old_work[$work_header['open']]) && !empty($old_work[$work_header['open']])) {
             $timestamp = strtotime($old_work[$work_header['open']]);
             $open_date = ($timestamp !== false) ? date('Y-m-d', $timestamp) : '';
        }

        $new_data['works'][] = array(
            'work_id'     => $work_id,
            'title'       => isset($work_header['title']) && isset($old_work[$work_header['title']]) ? $old_work[$work_header['title']] : '',
            'title_ruby'  => isset($work_header['title_ruby']) && isset($old_work[$work_header['title_ruby']]) ? $old_work[$work_header['title_ruby']] : '',
            'author'      => isset($work_header['author']) && isset($old_work[$work_header['author']]) ? $old_work[$work_header['author']] : '',
            'author_ruby' => isset($work_header['author_ruby']) && isset($old_work[$work_header['author_ruby']]) ? $old_work[$work_header['author_ruby']] : '',
            'category_id' => $new_category_id,
            'comment'     => isset($work_header['comment']) && isset($old_work[$work_header['comment']]) ? $old_work[$work_header['comment']] : '',
            'title_id'    => $work_id,
            'copyright'   => isset($work_header['copyright']) && isset($old_work[$work_header['copyright']]) ? $old_work[$work_header['copyright']] : '',
            'open'        => $open_date,
            'path'        => isset($work_header['path']) && isset($old_work[$work_header['path']]) ? str_replace('contents/', '', $old_work[$work_header['path']]) : '',
            'assets'      => array()
        );
    }
}

// 7. 新しいJSONファイルとして保存 (PHP 5.3 互換)
$json_string = json_encode($new_data);
if ($json_string === false) {
    die("Error: 新データ(sozai_v2.json)のJSONエンコードに失敗しました。");
}
$unescaped_json_string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}, $json_string);

// 手動でJSONを整形
$formatted_json = '';
$level = 0;
$in_string = false;
$len = strlen($unescaped_json_string);
for ($i = 0; $i < $len; $i++) {
    $char = $unescaped_json_string[$i];
    if ($char == '"' && ($i > 0 ? $unescaped_json_string[$i-1] != '\\' : true)) $in_string = !$in_string;
    if (!$in_string) {
        switch ($char) {
            case '{': case '[':
                $level++;
                $formatted_json .= $char . "\n" . str_repeat("    ", $level);
                break;
            case '}': case ']':
                $level--;
                $formatted_json .= "\n" . str_repeat("    ", $level) . $char;
                break;
            case ',':
                $formatted_json .= $char . "\n" . str_repeat("    ", $level);
                break;
            case ':': $formatted_json .= $char . " "; break;
            default: $formatted_json .= $char; break;
        }
    } else {
        $formatted_json .= $char;
    }
}

$result = file_put_contents($new_data_path, $formatted_json);

if ($result === false) {
    echo "Error: sozai_v2.json の書き込みに失敗しました。\n";
} else {
    echo "Success: sozai_v2.json の生成が完了しました。\n";
    echo "Total categories: " . count($new_data['categories']) . "\n";
    echo "Total works: " . count($new_data['works']) . "\n";
}
?>
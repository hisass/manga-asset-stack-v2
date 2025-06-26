<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【PHP 5.3環境に完全対応した最終修正版】
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

// 3. BOM(Byte Order Mark)や前後の空白、改行を除去
$clean_utf8_string = trim(str_replace(array("\n", "\r", "\t"), '', $utf8_string));

// 4. 正規表現を使い、"data"と"category"のブロックを文字列として抽出
//【修正点】データブロックの終わりを次のキー("category", "news")で判断し、ブロック全体を正確に取得します。
if (!preg_match('/"data":(\[.*\]),"category"/u', $clean_utf8_string, $data_matches) ||
    !preg_match('/"category":(\[.*\]),"news"/u', $clean_utf8_string, $category_matches)) {
    die("Error: sozai.jsonの中から'data'または'category'のブロックを見つけられませんでした。");
}

$data_block_str = $data_matches[1];
$category_block_str = $category_matches[1];

/**
 * JSONの配列形式の文字列を、1行ずつ安全にPHPの配列に変換する関数
 */
function parse_json_like_array($block_string) {
    $result_list = array();
    $inner_content = substr(trim($block_string), 1, -1);
    $rows_str_array = preg_split('/(?<=]),\s*(?=\[)/u', $inner_content);

    foreach ($rows_str_array as $row_str) {
        $row_items = array();
        $row_content = substr(trim($row_str), 1, -1);
        
        preg_match_all('/"((?:[^"]|\\")*?)"|([^,]+)/u', $row_content, $values);
        
        foreach ($values[0] as $i => $value) {
            $value_trimmed = trim($value);
            if (isset($values[1][$i]) && $values[1][$i] !== '') {
                $row_items[] = str_replace('\"', '"', $values[1][$i]);
            } else {
                if (is_numeric($value_trimmed)) {
                    $row_items[] = strpos($value_trimmed, '.') === false ? (int)$value_trimmed : (float)$value_trimmed;
                } else {
                    $row_items[] = $value_trimmed;
                }
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

// カテゴリデータを変換
$category_map = array();
if (!empty($old_category_array) && count($old_category_array) > 1) {
    $category_header = array_flip($old_category_array[0]);
    foreach (array_slice($old_category_array, 1) as $old_cat) {
        if (!isset($old_cat[$category_header['category']])) continue;

        $cat_name = $old_cat[$category_header['category']];
        $new_id = 'cat_' . preg_replace('/[^a-z0-9]/i', '', strtolower($cat_name));
        $counter = 2;
        $original_new_id = $new_id;
        while (in_array($new_id, array_map(function($c) { return $c['id']; }, $new_data['categories']))) {
            $new_id = $original_new_id . '_' . $counter++;
        }
        
        $category_map[$cat_name] = $new_id;

        $new_data['categories'][] = array(
            'id'             => $new_id,
            'name'           => $cat_name,
            'alias'          => isset($old_cat[$category_header['alias']]) ? $old_cat[$category_header['alias']] : $cat_name,
            'directory_name' => '',
            'title_count'    => isset($old_cat[$category_header['title_count']]) ? (int)$old_cat[$category_header['title_count']] : 0
        );
    }
}

// 作品データを変換
if (!empty($old_data_array) && count($old_data_array) > 1) {
    $work_header = array_flip($old_data_array[0]);
    foreach (array_slice($old_data_array, 1) as $old_work) {
        $category_name_from_work = isset($old_work[$work_header['category']]) ? $old_work[$work_header['category']] : '';
        $new_category_id = isset($category_map[$category_name_from_work]) ? $category_map[$category_name_from_work] : '';
        
        $work_id = !empty($old_work[$work_header['title_id']]) ? (string)$old_work[$work_header['title_id']] : 'work_' . uniqid();

        $open_date = '';
        if (isset($old_work[$work_header['open']]) && !empty($old_work[$work_header['open']])) {
             $timestamp = strtotime($old_work[$work_header['open']]);
             $open_date = ($timestamp !== false) ? date('Y-m-d', $timestamp) : '';
        }

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
            'open'        => $open_date,
            'path'        => isset($old_work[$work_header['path']]) ? str_replace('contents/', '', $old_work[$work_header['path']]) : '',
            'assets'      => array()
        );
    }
}

// 7. 新しいJSONファイルとして保存 (PHP 5.3 のためのフォールバック)
$json_string = json_encode($new_data);
if ($json_string === false) {
    die("Error: 新データ(sozai_v2.json)のJSONエンコードに失敗しました。");
}

// 日本語が \uXXXX のようにエスケープされるのをデコード
$unescaped_json_string = preg_replace_callback(
    '/\\\\u([0-9a-fA-F]{4})/',
    function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    },
    $json_string
);

// JSONを見やすくフォーマットする (JSON_PRETTY_PRINTの代替)
$formatted_json = '';
$level = 0;
$in_string = false;
$len = strlen($unescaped_json_string);

for ($i = 0; $i < $len; $i++) {
    $char = $unescaped_json_string[$i];
    if ($char == '"' && $unescaped_json_string[$i-1] != '\\') {
        $in_string = !$in_string;
    }

    if (!$in_string) {
        switch ($char) {
            case '{':
            case '[':
                $level++;
                $formatted_json .= $char . "\n" . str_repeat("    ", $level);
                break;
            case '}':
            case ']':
                $level--;
                $formatted_json .= "\n" . str_repeat("    ", $level) . $char;
                break;
            case ',':
                $formatted_json .= $char . "\n" . str_repeat("    ", $level);
                break;
            case ':':
                $formatted_json .= $char . " ";
                break;
            default:
                $formatted_json .= $char;
                break;
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
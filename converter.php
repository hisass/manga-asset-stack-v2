<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【特殊JSON構造・文字コード・文法エラー対応 最終版】
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

// 3.【最重要修正】正規表現を使い、"data", "category", "news" の各ブロックを個別に抽出する
preg_match('/"data":\s*(\[.*?\])/s', $utf8_json_string, $data_matches);
preg_match('/"category":\s*(\[.*?\])/s', $utf8_json_string, $category_matches);
preg_match('/"news":\s*(\[.*?\])/s', $utf8_json_string, $news_matches);

$data_json = isset($data_matches[1]) ? $data_matches[1] : '[]';
$category_json = isset($category_matches[1]) ? $category_matches[1] : '[]';
$news_json = isset($news_matches[1]) ? $news_matches[1] : '[]';

// 4. 抽出した各ブロックを個別にデコードする
$old_data_array = json_decode($data_json, true);
$old_category_array = json_decode($category_json, true);

if ($old_data_array === null || $old_category_array === null) {
    die("Error: JSONの一部分のデコードに失敗しました。ファイルが著しく破損している可能性があります。");
}

// 5. 新しいデータ構造を作成
$new_data = array(
    'categories' => array(),
    'works' => array()
);

// カテゴリデータを変換
// ヘッダー行を特定するためのキーを準備
$cat_header = array_flip($old_category_array[0]); // ["category","open",...] -> ["category" => 0, "open" => 1, ...]
foreach (array_slice($old_category_array, 1) as $old_cat) {
    $new_data['categories'][] = array(
        'id' => isset($old_cat[$cat_header['category']]) ? 'cat_' . $old_cat[$cat_header['category']] : '',
        'name' => isset($old_cat[$cat_header['alias']]) ? $old_cat[$cat_header['alias']] : '',
        'alias' => isset($old_cat[$cat_header['alias']]) ? $old_cat[$cat_header['alias']] : '',
        'directory_name' => '',
        'title_count' => isset($old_cat[$cat_header['title_count']]) ? (int)$old_cat[$cat_header['title_count']] : 0,
    );
}

// 作品データを変換
// ヘッダー行を特定するためのキーを準備
$work_header = array_flip($old_data_array[0]); // ["title","title_ruby",...] -> ["title" => 0, "title_ruby" => 1, ...]
foreach (array_slice($old_data_array, 1) as $old_work) {
    
    // カテゴリIDをcategoriesのIDと合わせる
    $category_id_value = isset($old_work[$work_header['category']]) ? $old_work[$work_header['category']] : '';
    $new_category_id = '';
    foreach($new_data['categories'] as $cat) {
        if ($cat['name'] === $category_id_value) {
            $new_category_id = $cat['id'];
            break;
        }
    }

    $work_id_value = isset($old_work[$work_header['title_id']]) ? trim($old_work[$work_header['title_id']]) : '';
    if(empty($work_id_value)){
        // title_idがない場合は、適当なユニークIDを生成（例）
        $work_id_value = 'work_' . uniqid();
    }
    
    $new_data['works'][] = array(
        'work_id'     => $work_id_value,
        'title'       => isset($old_work[$work_header['title']]) ? $old_work[$work_header['title']] : '',
        'title_ruby'  => isset($old_work[$work_header['title_ruby']]) ? $old_work[$work_header['title_ruby']] : '',
        'author'      => isset($old_work[$work_header['author']]) ? $old_work[$work_header['author']] : '',
        'author_ruby' => isset($old_work[$work_header['author_ruby']]) ? $old_work[$work_header['author_ruby']] : '',
        'category_id' => $new_category_id,
        'comment'     => isset($old_work[$work_header['comment']]) ? $old_work[$work_header['comment']] : '',
        'title_id'    => $work_id_value,
        'copyright'   => isset($old_work[$work_header['copyright']]) ? $old_work[$work_header['copyright']] : '',
        'open'        => isset($old_work[$work_header['open']]) ? date('Y-m-d', strtotime($old_work[$work_header['open']])) : '',
        'path'        => isset($old_work[$work_header['path']]) ? str_replace('contents/','', $old_work[$work_header['path']]) : '',
        'assets'      => array()
    );
}

// 6. 新しいJSONファイルとして保存
$new_json_string = json_encode($new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
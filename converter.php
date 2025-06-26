<?php
/**
 * 旧データ(sozai.json)を読み込み、新フォーマット(sozai_v2.json)に変換して保存する
 * 【特殊なJSON構造と文字コードに両対応した最終版】
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


// 3.【最重要修正】特殊なJSON構造を、単一の有効なJSONオブジェクトに修正する
// "},{" のような、オブジェクトを区切ってしまっている不正なカンマを、
// 正しいカンマに置換する
$merged_json_string = str_replace('},{"', ',"', $utf8_json_string);


// 4. クリーニングしたUTF-8文字列をデコードする
$old_data = json_decode($merged_json_string, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // ここでエラーが出る場合は、手動での修正が必要
    $error_message = 'Error: sozai.jsonのJSONデコードに失敗しました。';
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
            'id' => isset($old_cat['category_id']) ? $old_cat['category_id'] : '', // 元のキー名に合わせる
            'name' => isset($old_cat['category_name']) ? $old_cat['category_name'] : '',
            'alias' => isset($old_cat['category_alias']) ? $old_cat['category_alias'] : '',
            'directory_name' => '', // 旧データに存在しないキー
            'title_count' => 0,      // 旧データに存在しないキー
        );
    }
}

// 作品データを変換
if (isset($old_data['data']) && is_array($old_data['data'])) {
    // 最初の行はヘッダーなので除去
    $work_rows = array_slice($old_data['data'], 1);

    foreach ($work_rows as $old_work) {
        $new_data['works'][] = array(
            'work_id'     => isset($old_work[9]) ? $old_work[9] : '', // title_id を work_id に
            'title'       => isset($old_work[0]) ? $old_work[0] : '',
            'title_ruby'  => isset($old_work[1]) ? $old_work[1] : '',
            'author'      => isset($old_work[2]) ? $old_work[2] : '',
            'author_ruby' => isset($old_work[3]) ? $old_work[3] : '',
            'category_id' => isset($old_work[6]) ? $old_work[6] : '', // categoryをcategory_idに
            'comment'     => isset($old_work[8]) ? $old_work[8] : '',
            'title_id'    => isset($old_work[10]) ? $old_work[10] : '', // detailをtitle_idに
            'copyright'   => isset($old_work[4]) ? $old_work[4] : '',
            'open'        => isset($old_work[5]) ? $old_work[5] : '',
            'path'        => isset($old_work[7]) ? $old_work[7] : '',
            'assets'      => array()
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
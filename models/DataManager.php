<?php

class DataManager {
    private $data_file_path;
    private $data = array(); // 全てのJSONデータを保持するプロパティ

    /**
     * コンストラクタ
     * ファイルパスを設定し、既存のデータを読み込む
     */
    public function __construct() {
        // 新しいデータファイルのパスを定義
        // config.phpが読み込まれている前提
        $this->data_file_path = BASE_DIR_PATH . '/data/sozai_v2.json';
        $this->loadData();
    }

    /**
     * JSONファイルを読み込み、クラスのプロパティにセットする
     * @return bool 成功した場合はtrue, 失敗した場合はfalse
     */
    private function loadData() {
        if (!file_exists($this->data_file_path)) {
            // ファイルが存在しない場合は、空の基本構造で初期化
            $this->data = array('categories' => array(), 'works' => array());
            return true;
        }

        $json_string = file_get_contents($this->data_file_path);
        if (empty($json_string)) {
            $this->data = array('categories' => array(), 'works' => array());
            return true;
        }

        $decoded_data = json_decode($json_string, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->data = $decoded_data;
            return true;
        } else {
            // JSONデコードに失敗した場合は、データを空にしておく
            $this->data = array('categories' => array(), 'works' => array());
            return false;
        }
    }

    /**
     * 現在のデータをJSONファイルに上書き保存する（ファイルロック付き）
     * @return bool 成功した場合はtrue, 失敗した場合はfalse
     */
    public function saveData() {
        // PHP 5.3ではJSON整形オプションが使えないため、シンプルなエンコードを行う
        $json_string = json_encode($this->data);

        // LOCK_EXは、他のプロセスが同時に書き込むのを防ぐためのファイルロック
        $result = file_put_contents($this->data_file_path, $json_string, LOCK_EX);

        return $result !== false;
    }
    
    // --- データ取得（Getter）関数 ---

    public function getCategories() {
        return isset($this->data['categories']) ? $this->data['categories'] : array();
    }
    
    public function getWorks() {
        return isset($this->data['works']) ? $this->data['works'] : array();
    }

    // 今後、ここに編集・追加・削除などの関数を追加していきます。
    /*
    public function getWorkById($work_id) { ... }
    public function updateWork($work_id, $new_work_data) { ... }
    */

    /**
     * 指定されたIDの作品データを更新する
     * @param string $work_id 更新対象の作品ID
     * @param array $new_data フォームからPOSTされた新しいデータ
     * @return bool 成功した場合はtrue, 失敗した場合はfalse
     */
    public function updateWork($work_id, $new_data) {
        $work_found_and_updated = false;
        
        // works配列をループし、該当する作品を探して内容を更新
        foreach ($this->data['works'] as $index => $work) {
            if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                
                // フォームから送られてきた値で各項目を上書き
                $this->data['works'][$index]['title'] = isset($new_data['title']) ? $new_data['title'] : '';
                $this->data['works'][$index]['title_ruby'] = isset($new_data['title_ruby']) ? $new_data['title_ruby'] : '';
                $this->data['works'][$index]['author'] = isset($new_data['author']) ? $new_data['author'] : '';
                $this->data['works'][$index]['author_ruby'] = isset($new_data['author_ruby']) ? $new_data['author_ruby'] : '';
                $this->data['works'][$index]['category_id'] = isset($new_data['category_id']) ? $new_data['category_id'] : '';
                $this->data['works'][$index]['comment'] = isset($new_data['comment']) ? $new_data['comment'] : '';
                // 他のキーも同様に追加可能

                $work_found_and_updated = true;
                break; // 対象を見つけたらループを抜ける
            }
        }

        // 作品が見つかり、更新された場合のみ、ファイル全体を保存する
        if ($work_found_and_updated) {
            return $this->saveData();
        }

        return false; // 作品が見つからなかった場合
    }

    /**
     * 指定されたwork_idを持つ作品を1件取得する
     * @param string $work_id
     * @return array|null 作品データ、見つからなければnull
     */
    public function getWorkById($work_id) {
        if (isset($this->data['works'])) {
            foreach ($this->data['works'] as $work) {
                if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                    return $work;
                }
            }
        }
        return null; // 見つからなかった場合
    }

    /**
     * 新しい作品データを追加する
     * @param array $new_data フォームからPOSTされた新しいデータ
     * @return bool 成功した場合はtrue, 失敗した場合はfalse
     */
    public function addWork($new_data) {
        $work_id = isset($new_data['work_id']) ? trim($new_data['work_id']) : '';

        // 作品IDが空、または既に存在する場合はエラーとしてfalseを返す
        if (empty($work_id) || $this->getWorkById($work_id) !== null) {
            return false; 
        }

        // 保存する新しい作品のデータを作成
        $new_work_entry = array(
            'work_id'     => $work_id,
            'title'       => isset($new_data['title']) ? $new_data['title'] : '',
            'title_ruby'  => isset($new_data['title_ruby']) ? $new_data['title_ruby'] : '',
            'author'      => isset($new_data['author']) ? $new_data['author'] : '',
            'author_ruby' => isset($new_data['author_ruby']) ? $new_data['author_ruby'] : '',
            'category_id' => isset($new_data['category_id']) ? $new_data['category_id'] : '',
            'comment'     => isset($new_data['comment']) ? $new_data['comment'] : '',
            // デフォルト値や空値を設定しておくキー
            'title_id'    => '',
            'copyright'   => '',
            'open'        => date('Y-m-d'), // とりあえず今日の日付
            'path'        => '' // パスは後から編集することを想定
        );

        // works配列の末尾に新しい作品を追加
        $this->data['works'][] = $new_work_entry;

        // 変更をファイルに保存する
        return $this->saveData();
    }

}
<?php
/**
 * データ管理クラス (ベースファイル + 差分ファイル方式)
 */
class DataManager {
    private $data_path;
    private $delta_path;

    private $baseData = array();
    private $deltaData = array();
    private $data = array();

    public function __construct() {
        $this->data_path = BASE_DIR_PATH . '/data/sozai_v2.json';
        $this->delta_path = BASE_DIR_PATH . '/data/sozai_v2_delta.json';
        $this->loadAndMergeData();
    }

    private function loadAndMergeData() {
        if (file_exists($this->data_path)) {
            $json_string = file_get_contents($this->data_path);
            // BOM付きUTF-8ファイルに対応
            if (substr($json_string, 0, 3) === "\xEF\xBB\xBF") {
                $json_string = substr($json_string, 3);
            }
            $this->baseData = json_decode($json_string, true);
        } else {
            $this->baseData = array('categories' => array(), 'works' => array());
        }

        if (file_exists($this->delta_path)) {
            $json_string = file_get_contents($this->delta_path);
            if (substr($json_string, 0, 3) === "\xEF\xBB\xBF") {
                $json_string = substr($json_string, 3);
            }
            $this->deltaData = json_decode($json_string, true);
        } else {
            $this->deltaData = array(
                'works' => array('added' => array(), 'updated' => array(), 'deleted' => array()),
                'categories' => array('added' => array(), 'updated' => array(), 'deleted' => array())
            );
        }

        $this->data = $this->merge($this->baseData, $this->deltaData);
    }
    
    private function merge($base, $delta) {
        // --- 作品データのマージ ---
        $mergedWorks = isset($base['works']) ? $base['works'] : array();
        foreach ($mergedWorks as $work_id => &$work) {
            $work['source'] = 'base';
        }
        unset($work);

        if (!empty($delta['works']['deleted'])) {
            $deleted_ids = array_flip($delta['works']['deleted']);
            $mergedWorks = array_diff_key($mergedWorks, $deleted_ids);
        }
        
        if (!empty($delta['works']['updated'])) {
            foreach ($delta['works']['updated'] as $work_id => $update_data) {
                if (isset($mergedWorks[$work_id])) {
                    $mergedWorks[$work_id] = array_merge($mergedWorks[$work_id], $update_data);
                    $mergedWorks[$work_id]['source'] = 'updated';
                }
            }
        }
        
        if (!empty($delta['works']['added'])) {
             foreach ($delta['works']['added'] as $work_id => $added_work) {
                 $added_work['source'] = 'added';
                 $mergedWorks[$work_id] = $added_work;
             }
        }
        
        // --- カテゴリデータのマージ ---
        $mergedCategories = isset($base['categories']) ? $base['categories'] : array();
        foreach ($mergedCategories as $cat_id => &$cat) {
            $cat['source'] = 'base';
        }
        unset($cat);

        if (!empty($delta['categories']['deleted'])) {
            $deleted_ids = array_flip($delta['categories']['deleted']);
            $mergedCategories = array_diff_key($mergedCategories, $deleted_ids);
        }
        if (!empty($delta['categories']['updated'])) {
            foreach ($delta['categories']['updated'] as $cat_id => $update_data) {
                if (isset($mergedCategories[$cat_id])) {
                    $mergedCategories[$cat_id] = array_merge($mergedCategories[$cat_id], $update_data);
                    $mergedCategories[$cat_id]['source'] = 'updated';
                }
            }
        }
        if (!empty($delta['categories']['added'])) {
            foreach ($delta['categories']['added'] as $cat_id => $added_cat) {
                 $added_cat['source'] = 'added';
                 $mergedCategories[$cat_id] = $added_cat;
             }
        }

        return array('categories' => $mergedCategories, 'works' => $mergedWorks);
    }
    
    private function saveDeltaData() {
        $json_string = json_encode($this->deltaData);
        $unescaped_json_string = preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            },
            $json_string
        );
        // ファイルの先頭にUTF-8のBOM(目印)を追加して保存する
        return file_put_contents($this->delta_path, "\xEF\xBB\xBF" . $unescaped_json_string) !== false;
    }

    public function getCategories() { return $this->data['categories']; }

    // ▼▼▼ このメソッドを修正 ▼▼▼
    public function getCategoryById($category_id) {
        // キーでの直接参照をやめ、全件ループでIDが一致するものを探す、より確実な方法に変更
        foreach ($this->data['categories'] as $cat_data) {
            if (isset($cat_data['id']) && $cat_data['id'] === $category_id) {
                return $cat_data;
            }
        }
        return null; // 見つからなかった場合
    }
    // ▲▲▲ ここまでを修正 ▲▲▲

    public function getWorkById($work_id) { return isset($this->data['works'][$work_id]) ? $this->data['works'][$work_id] : null; }
    
    public function getWorks($filter_category = null, $search_keyword = null, $sort_key = 'open', $sort_order = 'desc') {
        $works = $this->data['works'];
        if ($filter_category) {
            $works = array_filter($works, function($work) use ($filter_category) { return isset($work['category_id']) && $work['category_id'] === $filter_category; });
        }
        if ($search_keyword) {
            $works = array_filter($works, function($work) use ($search_keyword) {
                return (isset($work['title']) && stripos($work['title'], $search_keyword) !== false) ||
                       (isset($work['author']) && stripos($work['author'], $search_keyword) !== false) ||
                       (isset($work['comment']) && stripos($work['comment'], $search_keyword) !== false);
            });
        }
        if ($sort_key) {
            usort($works, function($a, $b) use ($sort_key, $sort_order) {
                $val_a = isset($a[$sort_key]) ? $a[$sort_key] : '';
                $val_b = isset($b[$sort_key]) ? $b[$sort_key] : '';
                if ($val_a == $val_b) return 0;
                $result = ($val_a < $val_b) ? -1 : 1;
                return ($sort_order === 'desc') ? -$result : $result;
            });
        }
        return $works;
    }
    public function addWork($postData) {
        $work_id = 'work_' . uniqid(rand(), true);
        $newWork = array( 'work_id' => $work_id, 'title' => isset($postData['title']) ? trim($postData['title']) : '', 'title_ruby' => isset($postData['title_ruby']) ? trim($postData['title_ruby']) : '', 'author' => isset($postData['author']) ? trim($postData['author']) : '', 'author_ruby' => isset($postData['author_ruby']) ? trim($postData['author_ruby']) : '', 'category_id' => isset($postData['category_id']) ? $postData['category_id'] : '', 'comment' => isset($postData['comment']) ? trim($postData['comment']) : '', 'title_id' => isset($postData['title_id']) ? trim($postData['title_id']) : '', 'directory_name' => isset($postData['directory_name']) ? trim($postData['directory_name']) : '', 'copyright' => isset($postData['copyright']) ? trim($postData['copyright']) : '', 'open' => isset($postData['open']) && $postData['open'] ? trim($postData['open']) : date('Y-m-d'), 'assets' => array() );
        $this->deltaData['works']['added'][$work_id] = $newWork;
        return $this->saveDeltaData();
    }
    public function updateWork($work_id, $postData) {
        if (isset($this->deltaData['works']['added'][$work_id])) {
            $this->deltaData['works']['added'][$work_id] = array_merge($this->deltaData['works']['added'][$work_id], $postData);
        } else { 
            if (!isset($this->deltaData['works']['updated'][$work_id])) { $this->deltaData['works']['updated'][$work_id] = array(); }
            $this->deltaData['works']['updated'][$work_id] = array_merge($this->deltaData['works']['updated'][$work_id], $postData);
        }
        return $this->saveDeltaData();
    }
    public function deleteWork($work_id) {
        if (isset($this->deltaData['works']['added'][$work_id])) { unset($this->deltaData['works']['added'][$work_id]); }
        if (isset($this->deltaData['works']['updated'][$work_id])) { unset($this->deltaData['works']['updated'][$work_id]); }
        if (isset($this->baseData['works'][$work_id])) { if (!in_array($work_id, $this->deltaData['works']['deleted'])) { $this->deltaData['works']['deleted'][] = $work_id; } }
        return $this->saveDeltaData();
    }
    public function addCategory($postData) {
        $cat_id = 'cat_' . preg_replace('/[^a-z0-9_]+/i', '', strtolower(str_replace(' ', '_', $postData['name']))) . '_' . time();
        $newCategory = array( 'id' => $cat_id, 'name' => isset($postData['name']) ? trim($postData['name']) : '', 'alias' => isset($postData['alias']) ? trim($postData['alias']) : '', 'directory_name' => isset($postData['directory_name']) ? trim($postData['directory_name']) : '', 'title_count' => isset($postData['title_count']) ? (int)$postData['title_count'] : 0, );
        $this->deltaData['categories']['added'][$cat_id] = $newCategory;
        return $this->saveDeltaData();
    }
    public function updateCategory($category_id, $postData) {
        if (isset($this->deltaData['categories']['added'][$category_id])) {
            $this->deltaData['categories']['added'][$category_id] = array_merge($this->deltaData['categories']['added'][$category_id], $postData);
        } else {
            if (!isset($this->deltaData['categories']['updated'][$category_id])) { $this->deltaData['categories']['updated'][$category_id] = array(); }
            $this->deltaData['categories']['updated'][$category_id] = array_merge($this->deltaData['categories']['updated'][$category_id], $postData);
        }
        return $this->saveDeltaData();
    }
}
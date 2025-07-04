<?php
/**
 * データ管理クラス (ベースファイル + 差分ファイル方式)
 */
class DataManager {
    private $data_path;
    private $delta_path;
    private $order_path;

    private $baseData = array();
    private $deltaData = array();
    private $data = array();

    public function __construct() {
        $this->data_path = BASE_DIR_PATH . '/data/sozai_v2.json';
        $this->delta_path = BASE_DIR_PATH . '/data/sozai_v2_delta.json';
        $this->order_path = BASE_DIR_PATH . '/data/categories_order.json';
        $this->loadAndMergeData();
    }

    private function loadAndMergeData() {
        if (file_exists($this->data_path)) {
            $json_string = file_get_contents($this->data_path);
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
        $this->sortCategories();
    }
    
    private function merge($base, $delta) {
        $mergedWorks = isset($base['works']) ? $base['works'] : array();
        if (!empty($delta['works']['deleted'])) {
            $mergedWorks = array_diff_key($mergedWorks, array_flip($delta['works']['deleted']));
        }
        if (!empty($delta['works']['updated'])) {
            $mergedWorks = array_replace_recursive($mergedWorks, $delta['works']['updated']);
        }
        if (!empty($delta['works']['added'])) {
            $mergedWorks = array_merge($mergedWorks, $delta['works']['added']);
        }

        // ▼▼▼ ここからが追加部分 ▼▼▼
        // 全ての作品に新着判定フラグを追加
        $one_week_ago = new DateTime('-7 days');
        foreach ($mergedWorks as &$work) {
            $work['is_new'] = false;
            if (!empty($work['open']) && ($work_date = DateTime::createFromFormat('Y-m-d', $work['open'])) !== false) {
                if ($work_date >= $one_week_ago) {
                    $work['is_new'] = true;
                }
            }
        }
        unset($work);
        // ▲▲▲ ここまでを追加 ▲▲▲

        $mergedCategories = isset($base['categories']) ? $base['categories'] : array();
        if (!empty($delta['categories']['deleted'])) {
            $mergedCategories = array_diff_key($mergedCategories, array_flip($delta['categories']['deleted']));
        }
        if (!empty($delta['categories']['updated'])) {
            $mergedCategories = array_replace_recursive($mergedCategories, $delta['categories']['updated']);
        }
        if (!empty($delta['categories']['added'])) {
            $mergedCategories = array_merge($mergedCategories, $delta['categories']['added']);
        }
        
        foreach ($mergedWorks as $id => &$item) {
            if (isset($delta['works']['added'][$id])) $item['source'] = 'added';
            elseif (isset($delta['works']['updated'][$id])) $item['source'] = 'updated';
            else $item['source'] = 'base';
        }
        unset($item);
        foreach ($mergedCategories as $id => &$item) {
            if (isset($delta['categories']['added'][$id])) $item['source'] = 'added';
            elseif (isset($delta['categories']['updated'][$id])) $item['source'] = 'updated';
            else $item['source'] = 'base';
        }
        unset($item);

        return array('categories' => $mergedCategories, 'works' => $mergedWorks);
    }
    
    private function sortCategories() {
        $categories = $this->data['categories'];
        if (empty($categories)) return;
        $ordered_ids = array();
        if (file_exists($this->order_path)) {
            $ordered_ids = json_decode(file_get_contents($this->order_path), true);
        }
        $sorted_categories = array();
        $remaining_categories = $categories;
        foreach ($ordered_ids as $cat_id) {
            if (isset($categories[$cat_id])) {
                $sorted_categories[$cat_id] = $categories[$cat_id];
                unset($remaining_categories[$cat_id]);
            }
        }
        $sorted_categories = array_merge($sorted_categories, $remaining_categories);
        $new_order = array_keys($sorted_categories);
        file_put_contents($this->order_path, json_encode($new_order));
        $this->data['categories'] = $sorted_categories;
    }
    
    private function saveDeltaData() {
        $json_string = json_encode($this->deltaData);
        $unescaped_json_string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $json_string);
        return file_put_contents($this->delta_path, "\xEF\xBB\xBF" . $unescaped_json_string) !== false;
    }

    public function getCategories() { return $this->data['categories']; }
    public function getCategoryById($category_id) { return isset($this->data['categories'][$category_id]) ? $this->data['categories'][$category_id] : null; }
    public function getWorkById($work_id) { return isset($this->data['works'][$work_id]) ? $this->data['works'][$work_id] : null; }
    
    public function getWorks($filter_category = null, $search_keyword = null, $sort_option = null) {
        $works = array_values($this->data['works']);
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
        if ($sort_option) {
            $sort_parts = explode('_', $sort_option);
            $sort_key = isset($sort_parts[0]) ? $sort_parts[0] : 'open';
            $sort_order = isset($sort_parts[1]) ? $sort_parts[1] : 'desc';
            $allowed_keys = array('open', 'title', 'author');
            if (!in_array($sort_key, $allowed_keys)) {
                $sort_key = 'open';
            }
            usort($works, function($a, $b) use ($sort_key, $sort_order) {
                $val_a = isset($a[$sort_key]) ? $a[$sort_key] : '';
                $val_b = isset($b[$sort_key]) ? $b[$sort_key] : '';
                if ($val_a == $val_b) return 0;
                if ($sort_key === 'title' || $sort_key === 'author') {
                     $result = strnatcasecmp($val_a, $val_b);
                } else {
                     $result = ($val_a < $val_b) ? -1 : 1;
                }
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
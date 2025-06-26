<?php

class DataManager {
    private $data_file_path;
    private $data = array();

    public function __construct() {
        $this->data_file_path = BASE_DIR_PATH . '/data/sozai_v2.json';
        $this->loadData();
    }

    private function loadData() {
        if (!file_exists($this->data_file_path)) {
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
        } else {
            $this->data = array('categories' => array(), 'works' => array());
            return false;
        }
        return true;
    }

    public function saveData() {
        $json_string = json_encode($this->data);
        return file_put_contents($this->data_file_path, $json_string, LOCK_EX) !== false;
    }

    public function getCategories() {
        return isset($this->data['categories']) ? $this->data['categories'] : array();
    }

    /**
     * 作品リストを、フィルタリング、ソート、ページネーションを適用して取得する
     * @param string|null $category_id 絞り込むカテゴリのID
     * @param string|null $search_keyword 検索キーワード
     * @param string $sort_key 並び替えのキー
     * @param string $sort_order 昇順(asc)か降順(desc)か
     * @return array 絞り込まれた作品のリスト
     */
    public function getWorks($category_id = null, $search_keyword = null, $sort_key = 'open', $sort_order = 'desc') {
        $works = isset($this->data['works']) ? $this->data['works'] : array();

        // 1. カテゴリによるフィルタリング
        if ($category_id !== null && $category_id !== '') {
            $works = array_filter($works, function($work) use ($category_id) {
                return isset($work['category_id']) && $work['category_id'] === $category_id;
            });
        }
        
        // 2. キーワードによるフィルタリング
        if ($search_keyword !== null && $search_keyword !== '') {
            $works = array_filter($works, function($work) use ($search_keyword) {
                // タイトル、作品ID、著者名に含まれているかチェック（大文字小文字を区別しない）
                $title = isset($work['title']) ? $work['title'] : '';
                $work_id = isset($work['work_id']) ? $work['work_id'] : '';
                $author = isset($work['author']) ? $work['author'] : '';
                return (
                    stripos($title, $search_keyword) !== false ||
                    stripos($work_id, $search_keyword) !== false ||
                    stripos($author, $search_keyword) !== false
                );
            });
        }
        
        // 3. ソート処理
        usort($works, $this->buildSorter($sort_key, $sort_order));

        return $works;
    }
    
    private function buildSorter($key, $order) {
        return function ($a, $b) use ($key, $order) {
            $val_a = isset($a[$key]) ? $a[$key] : '';
            $val_b = isset($b[$key]) ? $b[$key] : '';

            if ($key === 'open') {
                $val_a = strtotime($val_a);
                $val_b = strtotime($val_b);
            }

            if ($val_a == $val_b) return 0;

            if ($order === 'desc') {
                return ($val_a > $val_b) ? -1 : 1;
            } else {
                return ($val_a < $val_b) ? -1 : 1;
            }
        };
    }

    public function getWorkById($work_id) {
        if (isset($this->data['works'])) {
            foreach ($this->data['works'] as $work) {
                if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                    return $work;
                }
            }
        }
        return null;
    }

    // ... (addWork, updateWork, deleteWorkなどの他のメソッドは変更なし) ...
    public function updateWork($work_id, $new_data) {
        $work_found_and_updated = false;
        
        foreach ($this->data['works'] as $index => $work) {
            if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                
                $this->data['works'][$index]['title'] = isset($new_data['title']) ? $new_data['title'] : '';
                $this->data['works'][$index]['title_ruby'] = isset($new_data['title_ruby']) ? $new_data['title_ruby'] : '';
                $this->data['works'][$index]['author'] = isset($new_data['author']) ? $new_data['author'] : '';
                $this->data['works'][$index]['author_ruby'] = isset($new_data['author_ruby']) ? $new_data['author_ruby'] : '';
                $this->data['works'][$index]['category_id'] = isset($new_data['category_id']) ? $new_data['category_id'] : '';
                $this->data['works'][$index]['comment'] = isset($new_data['comment']) ? $new_data['comment'] : '';

                $work_found_and_updated = true;
                break;
            }
        }

        if ($work_found_and_updated) {
            return $this->saveData();
        }

        return false;
    }

    public function addWork($new_data) {
        $work_id = isset($new_data['work_id']) ? trim($new_data['work_id']) : '';
        if (empty($work_id) || $this->getWorkById($work_id) !== null) {
            return false; 
        }
        $new_work_entry = array(
            'work_id'     => $work_id,
            'title'       => isset($new_data['title']) ? $new_data['title'] : '',
            'title_ruby'  => isset($new_data['title_ruby']) ? $new_data['title_ruby'] : '',
            'author'      => isset($new_data['author']) ? $new_data['author'] : '',
            'author_ruby' => isset($new_data['author_ruby']) ? $new_data['author_ruby'] : '',
            'category_id' => isset($new_data['category_id']) ? $new_data['category_id'] : '',
            'comment'     => isset($new_data['comment']) ? $new_data['comment'] : '',
            'title_id'    => '',
            'copyright'   => '',
            'open'        => date('Y-m-d'),
            'path'        => ''
        );
        $this->data['works'][] = $new_work_entry;
        return $this->saveData();
    }

    public function deleteWork($work_id) {
        $work_found = false;
        $works = $this->getWorks();
        foreach ($works as $index => $work) {
            if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                array_splice($this->data['works'], $index, 1);
                $work_found = true;
                break;
            }
        }
        if ($work_found) {
            return $this->saveData();
        }
        return false;
    }

    public function getCategoryById($category_id) {
        if (isset($this->data['categories'])) {
            foreach ($this->data['categories'] as $category) {
                if (isset($category['id']) && $category['id'] === $category_id) {
                    return $category;
                }
            }
        }
        return null; // 見つからなかった場合
    }

    public function addCategory($new_data) {
        $new_id = 'cat_' . str_pad(count($this->getCategories()) + 1, 3, '0', STR_PAD_LEFT);
        $new_category_entry = array(
            'id' => $new_id,
            'name' => isset($new_data['name']) ? $new_data['name'] : '',
            'alias' => isset($new_data['alias']) ? $new_data['alias'] : '',
            'directory_name' => isset($new_data['directory_name']) ? $new_data['directory_name'] : '',
            'title_count' => isset($new_data['title_count']) ? (int)$new_data['title_count'] : 0,
        );
        $this->data['categories'][] = $new_category_entry;
        return $this->saveData();
    }

    public function updateCategory($category_id, $new_data) {
        $category_found_and_updated = false;
        foreach ($this->data['categories'] as $index => $category) {
            if (isset($category['id']) && $category['id'] === $category_id) {
                $this->data['categories'][$index]['name'] = isset($new_data['name']) ? $new_data['name'] : '';
                $this->data['categories'][$index]['alias'] = isset($new_data['alias']) ? $new_data['alias'] : '';
                $this->data['categories'][$index]['directory_name'] = isset($new_data['directory_name']) ? $new_data['directory_name'] : '';
                $this->data['categories'][$index]['title_count'] = isset($new_data['title_count']) ? (int)$new_data['title_count'] : 0;
                $category_found_and_updated = true;
                break;
            }
        }
        if ($category_found_and_updated) {
            return $this->saveData();
        }
        return false;
    }
}
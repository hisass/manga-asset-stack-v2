<?php
// controllers/WorkController.php

require_once BASE_DIR_PATH . '/models/ViewerModel.php';

class WorkController {
    private $viewerModel;
    private $globalData = array();

    public function __construct(DataManager $dataManager) {
        $this->viewerModel = new ViewerModel($dataManager);
        $all_categories_from_model = $this->viewerModel->getCategories();
        $filtered_categories = array();
        foreach ($all_categories_from_model as $category) {
            if (isset($category['title_count']) && $category['title_count'] > 0) {
                $filtered_categories[$category['id']] = $category;
            }
        }
        $new_button_data = array('id' => 'new', 'name' => '<span class="badge bg-danger">NEW</span>', 'alias' => '', 'url' => 'index.php?page=new');
        $this->globalData['all_categories'] = array('new' => $new_button_data) + $filtered_categories;
    }

    public function home() {
        $data['title'] = 'トップページ';
        $works_by_category = array();
        $categories_for_home = array_filter($this->globalData['all_categories'], function($cat) { return $cat['id'] !== 'new'; });
        foreach ($categories_for_home as $category) {
            $works = $this->viewerModel->getWorksByCategoryId($category['id']); 
            $limited_works = array_slice($works, 0, (int)$category['title_count']);
            foreach ($limited_works as &$work) {
                $assets = $this->viewerModel->getAssetsForWork($work);
                $work['thumbnail_url'] = !empty($assets) ? $assets[0]['url'] : null; 
            }
            unset($work);
            $works_by_category[$category['id']] = $limited_works;
        }
        $data['works_by_category'] = $works_by_category;
        $this->loadView('home', $data);
    }

    // ▼▼▼ このメソッドを修正 ▼▼▼
    public function detail($work_id) {
        if (!$work_id) { $this->showNotFound(); return; }
        $work = $this->viewerModel->getWorkById($work_id);
        if (!$work) { $this->showNotFound(); return; }

        $category_name = null;
        if (!empty($work['category_id'])) {
            $category = $this->viewerModel->getCategoryById($work['category_id']);
            if ($category) {
                $category_name = $category['name'];
            }
        }
        
        // 最終更新日時を取得するロジックを追加
        $last_updated = null;
        if (!empty($work['directory_name'])) {
            // v1, v2両方のパスをチェック
            $path_v1 = ASSET_PATH_V1 . '/' . $work['directory_name'];
            $path_v2 = ASSET_PATH_V2 . '/' . $work['directory_name'];
            if (is_dir($path_v1)) {
                $last_updated = date("Y-m-d H:i:s", filemtime($path_v1));
            } elseif (is_dir($path_v2)) {
                $last_updated = date("Y-m-d H:i:s", filemtime($path_v2));
            }
        }
        
        $data['last_updated'] = $last_updated;
        $data['category_name'] = $category_name;
        $data['title'] = '作品詳細: ' . htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8');
        $data['work'] = $work;
        $data['assets'] = $this->viewerModel->getAssetsForWork($work);
        
        $this->loadView('detail', $data);
    }
    // ▲▲▲ ここまでを修正 ▲▲▲

    public function categoryPage($category_id) {
        if (!$category_id) { $this->showNotFound(); return; }
        $category = $this->viewerModel->getCategoryById($category_id);
        if (!$category) { $this->showNotFound(); return; }
        $sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'open_desc';
        $data['title'] = $category['name'];
        $data['page_specific_category'] = $category;
        $data['current_sort'] = $sort_option;
        $data['category_id'] = $category_id;
        $works = $this->viewerModel->getWorksByCategoryId($category_id, $sort_option);
        foreach ($works as &$work) {
            $assets = $this->viewerModel->getAssetsForWork($work);
            $work['thumbnail_url'] = !empty($assets) ? $assets[0]['url'] : null; 
            $work['asset_count'] = count($assets);
        }
        unset($work);
        $data['works'] = $works;
        $this->loadView('category_list', $data);
    }

    public function authorPage($author_name) {
        if (!$author_name) {
            $this->showNotFound();
            return;
        }
        $data['title'] = '著者: ' . htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8');
        $data['author_name'] = $author_name;
        $all_works = $this->viewerModel->getWorksByCategoryId(null, null);
        $filtered_works = array();
        foreach($all_works as $work) {
            if (isset($work['author']) && $work['author'] === $author_name) {
                $filtered_works[] = $work;
            }
        }
        foreach ($filtered_works as &$work) {
            $assets = $this->viewerModel->getAssetsForWork($work);
            $work['thumbnail_url'] = !empty($assets) ? $assets[0]['url'] : null; 
            $work['asset_count'] = count($assets);
        }
        unset($work);
        $data['works'] = $filtered_works;
        $this->loadView('author_list', $data);
    }

    public function newArrivals() {
        $data['title'] = '新着作品';
        $data['page_specific_category'] = array('name' => '新着作品', 'id' => 'new');
        $all_works = $this->viewerModel->getWorksByCategoryId(null, 'open_desc');
        $new_works = array();
        $one_week_ago = new DateTime('-7 days');
        foreach ($all_works as $work) {
            if (!empty($work['open']) && ($work_date = DateTime::createFromFormat('Y-m-d', $work['open'])) !== false) {
                if ($work_date >= $one_week_ago) {
                    $new_works[] = $work;
                }
            }
        }
        foreach ($new_works as &$work) {
            $assets = $this->viewerModel->getAssetsForWork($work);
            $work['thumbnail_url'] = !empty($assets) ? $assets[0]['url'] : null; 
            $work['asset_count'] = count($assets);
        }
        unset($work);
        $data['works'] = $new_works;
        $data['current_sort'] = 'open_desc';
        $data['category_id'] = 'new';
        $this->loadView('category_list', $data);
    }
    
    private function showNotFound() {
        header("HTTP/1.0 404 Not Found");
        $data['title'] = 'ページが見つかりません';
        $this->loadView('not_found', $data);
        exit;
    }

    private function loadView($viewName, $data = array()) {
        $viewData = array_merge($this->globalData, $data);
        extract($viewData, EXTR_SKIP);
        $baseDir = BASE_DIR_PATH . '/views/viewer/';
        header('Content-Type: text/html; charset=utf-8');
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
}
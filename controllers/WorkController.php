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
                $filtered_categories[] = $category;
            }
        }
        $new_button_data = array('id' => 'new', 'name' => '<span class="badge bg-danger">NEW</span>', 'alias' => '', 'url' => 'index.php?page=new');
        array_unshift($filtered_categories, $new_button_data);
        $this->globalData['all_categories'] = $filtered_categories;
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
                $work['thumbnail_url'] = !empty($assets) ? $assets[0] : null; 
            }
            unset($work);
            $works_by_category[$category['id']] = $limited_works;
        }
        $data['works_by_category'] = $works_by_category;
        $this->loadView('home', $data);
    }

    public function detail($work_id) {
        if (!$work_id) { $this->showNotFound(); return; }
        $work = $this->viewerModel->getWorkById($work_id);
        if (!$work) { $this->showNotFound(); return; }
        $data['title'] = '作品詳細: ' . htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8');
        $data['work'] = $work;
        $data['assets'] = $this->viewerModel->getAssetsForWork($work);
        $this->loadView('detail', $data);
    }

    public function categoryPage($category_id) {
        if (!$category_id) {
            $this->showNotFound();
            return;
        }
        $category = $this->viewerModel->getCategoryById($category_id);
        if (!$category) {
            $this->showNotFound();
            return;
        }
        
        $sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'open_desc';

        // ▼▼▼ ここを修正 ▼▼▼
        $data['title'] = $category['name']; // ページのタイトルはカテゴリ名
        $data['page_specific_category'] = $category; // ビューには別の変数名で渡す
        $data['current_sort'] = $sort_option;
        $data['category_id'] = $category_id;
        // ▲▲▲ ここまでを修正 ▲▲▲

        $works = $this->viewerModel->getWorksByCategoryId($category_id, $sort_option);
        foreach ($works as &$work) {
            $assets = $this->viewerModel->getAssetsForWork($work);
            $work['thumbnail_url'] = !empty($assets) ? $assets[0] : null; 
            $work['asset_count'] = count($assets);
        }
        unset($work);
        
        $data['works'] = $works;
        $this->loadView('category_list', $data);
    }

    public function newArrivals() {
        $data['title'] = '新着作品';
        // ▼▼▼ ここを修正 ▼▼▼
        $data['page_specific_category'] = array('name' => '新着作品', 'id' => 'new'); // 変数名を統一
        // ▲▲▲ ここまでを修正 ▲▲▲
        $data['works'] = array(); 
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
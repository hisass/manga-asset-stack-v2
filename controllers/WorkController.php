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

        $new_button_data = array(
            'id' => 'new',
            'name' => '<span class="badge bg-danger">NEW</span>',
            'alias' => '',
            'url' => 'index.php?page=new'
        );

        array_unshift($filtered_categories, $new_button_data);

        $this->globalData['all_categories'] = $filtered_categories;
    }

    public function home() {
        $data['title'] = 'トップページ';
        $works_by_category = array();

        $categories_for_home = array_filter($this->globalData['all_categories'], function($cat) {
            return $cat['id'] !== 'new';
        });

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
        if (!$work_id) {
            $this->showNotFound();
            return;
        }

        $work = $this->viewerModel->getWorkById($work_id);
        if (!$work) {
            $this->showNotFound();
            return;
        }

        $data['title'] = '作品詳細: ' . htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8');
        $data['work'] = $work;
        $data['assets'] = $this->viewerModel->getAssetsForWork($work);
        
        $this->loadView('detail', $data);
    }

    // ▼▼▼ このメソッドを全面的に修正 ▼▼▼
    public function categoryPage($category_id) {
        if (!$category_id) {
            $this->showNotFound();
            return;
        }

        // 全てのカテゴリ情報を取得
        $all_categories = $this->viewerModel->getCategories();

        // ループして、URLのIDと一致するカテゴリ情報を探す（最も確実な方法）
        $current_category = null;
        foreach ($all_categories as $cat) {
            if (isset($cat['id']) && $cat['id'] === $category_id) {
                $current_category = $cat;
                break;
            }
        }
        
        if ($current_category === null) {
            $this->showNotFound();
            return;
        }
        
        // 見つかった正しいカテゴリ情報をビューに渡す
        $data['title'] = $current_category['name'];
        $data['category'] = $current_category;

        // 作品一覧の取得（この処理は元から正常でした）
        $works = $this->viewerModel->getWorksByCategoryId($category_id);

        foreach ($works as &$work) {
            $assets = $this->viewerModel->getAssetsForWork($work);
            $work['thumbnail_url'] = !empty($assets) ? $assets[0] : null; 
            $work['asset_count'] = count($assets);
        }
        unset($work);
        
        $data['works'] = $works;

        $this->loadView('category_list', $data);
    }
    // ▲▲▲ ここまでを修正 ▲▲▲

    public function newArrivals() {
        $data['title'] = '新着作品';
        $data['category'] = array('name' => '新着作品');
        $data['works'] = array(); 
        
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
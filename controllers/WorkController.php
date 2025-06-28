<?php
require_once BASE_DIR_PATH . '/models/ViewerModel.php';

class WorkController {
    private $viewerModel;
    private $globalData = array();

    public function __construct(DataManager $dataManager) {
        $this->viewerModel = new ViewerModel($dataManager);
        // 全ページ共通で使うカテゴリ一覧を準備
        $this->globalData['all_categories'] = $this->viewerModel->getCategories();
    }

    public function home() {
        $data['title'] = 'トップページ';
        $works_by_category = array();

        // カテゴリごとに作品を取得
        foreach ($this->globalData['all_categories'] as $category) {
            // title_count > 0 のカテゴリのみ表示
            if (isset($category['title_count']) && $category['title_count'] > 0) {
                $works = $this->viewerModel->getWorksByCategoryId($category['id']);
                // 表示件数で絞り込み
                $works_by_category[$category['id']] = array_slice($works, 0, (int)$category['title_count']);
            }
        }
        $data['works_by_category'] = $works_by_category;
        
        $this->loadView('home', $data);
    }

    public function detail($work_id) {
        if (!$work_id) {
            header("Location: index.php");
            exit;
        }

        $work = $this->viewerModel->getWorkById($work_id);
        if (!$work) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1><p>指定された作品は見つかりませんでした。</p><a href='index.php'>トップに戻る</a>";
            exit;
        }

        $data['title'] = '作品詳細: ' . htmlspecialchars($work['title'], ENT_QUOTES, 'UTF-8');
        $data['work'] = $work;
        // 画像一覧取得やダウンロード機能は、今後ここに追加していきます
        $data['images'] = array(); 
        
        $this->loadView('detail', $data);
    }

    private function loadView($viewName, $data = array()) {
        $viewData = array_merge($this->globalData, $data);
        extract($viewData, EXTR_SKIP);
        
        $baseDir = BASE_DIR_PATH . '/views/viewer/';
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
}

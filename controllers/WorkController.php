<?php
// controllers/WorkController.php

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

            var_dump($this->globalData['all_categories']);
            exit;

        $this->loadView('home', $data);
    }

    /**
     * ★★★ ここを修正しました ★★★
     */
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
        
        // 正しい場所でアセットリストを取得し、ビューに渡す
        $data['assets'] = $this->viewerModel->getAssetsForWork($work);
        
        $this->loadView('detail', $data);
    }

    private function loadView($viewName, $data = array()) {
        // 全ページ共通のデータをビュー用のデータにマージ
        $viewData = array_merge($this->globalData, $data);
        extract($viewData, EXTR_SKIP);
        
        $baseDir = BASE_DIR_PATH . '/views/viewer/';
        // ヘッダーを読み込む前に、文字コードを宣言する
        header('Content-Type: text/html; charset=utf-8');
        require_once $baseDir . "layouts/header.php";
        require_once $baseDir . "{$viewName}.php";
        require_once $baseDir . "layouts/footer.php";
    }
}
<?php
/**
 * ユーザー向け画面のロジックを制御するコントローラ
 */
class WorkController
{
    private $viewerModel;
    private $categories;

    public function __construct(DataManager $dataManager)
    {
        require_once BASE_DIR_PATH . '/models/ViewerModel.php';
        $this->viewerModel = new ViewerModel($dataManager);
        $this->categories = $this->viewerModel->getCategories();
    }

    /**
     * トップページ
     */
    public function home()
    {
        $home_data = $this->viewerModel->getHomeData();
        $page_title = "トップページ";
        require_once BASE_DIR_PATH . '/views/viewer/home.php';
    }

    /**
     * 作品詳細ページ
     */
    public function detail($work_id)
    {
        if (!$work_id) {
            die('作品IDが指定されていません。');
        }
        $work = $this->viewerModel->getWorkById($work_id);
        if (!$work) {
            die('指定された作品が見つかりません。');
        }
        $assets = $this->viewerModel->getAssetsForWork($work);
        $page_title = $work['title'];
        require_once BASE_DIR_PATH . '/views/viewer/detail.php';
    }

    /**
     * カテゴリ別一覧ページ
     */
    public function categoryPage($category_id)
    {
        if (!$category_id) {
            die('カテゴリIDが指定されていません。');
        }
        $category = $this->viewerModel->getCategoryById($category_id);
        if (!$category) {
            die('指定されたカテゴリが見つかりません。');
        }
        $works = $this->viewerModel->getWorksByCategoryId($category_id);
        $page_title = $category['name'];
        require_once BASE_DIR_PATH . '/views/viewer/category_list.php';
    }

    /**
     * 著者別一覧ページ
     */
    public function authorPage($author_name)
    {
        if (!$author_name) {
            die('著者名が指定されていません。');
        }
        $works = $this->viewerModel->getWorksByAuthorName($author_name);
        $page_title = $author_name . ' の作品一覧';
        require_once BASE_DIR_PATH . '/views/viewer/author_list.php';
    }
}
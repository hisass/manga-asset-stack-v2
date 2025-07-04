<?php
/**
 * ユーザー向け画面（ビューワー）のためのデータを提供するモデル
 */
class ViewerModel
{
    private $dataManager;
    private $all_works;
    private $all_categories;

    public function __construct(DataManager $dataManager)
    {
        $this->dataManager = $dataManager;
        $this->all_works = $this->dataManager->getAllWorks();
        $this->all_categories = $this->dataManager->getAllCategories();
    }

    /**
     * トップページ用のデータを取得
     */
    public function getHomeData()
    {
        $categories_order = $this->dataManager->getCategoryOrder();
        $sorted_categories = array();
        foreach ($categories_order as $cat_id) {
            if (isset($this->all_categories[$cat_id])) {
                $sorted_categories[$cat_id] = $this->all_categories[$cat_id];
            }
        }
        foreach ($this->all_categories as $cat_id => $cat_data) {
            if (!isset($sorted_categories[$cat_id])) {
                $sorted_categories[$cat_id] = $cat_data;
            }
        }

        $home_data = array();
        foreach ($sorted_categories as $cat_id => $category) {
            $works_in_cat = $this->getWorksByCategoryId($cat_id);
            // open日で降順ソート
            uasort($works_in_cat, function($a, $b) {
                return strtotime($b['open']) - strtotime($a['open']);
            });
            
            $limit = isset($category['title_count']) ? (int)$category['title_count'] : 5;
            $home_data[$cat_id] = array(
                'category' => $category,
                'works' => array_slice($works_in_cat, 0, $limit, true)
            );
        }
        return $home_data;
    }

    /**
     * 指定されたカテゴリIDの作品リストを取得
     */
    public function getWorksByCategoryId($category_id)
    {
        $works_in_category = array();
        foreach ($this->all_works as $work_id => $work) {
            if (isset($work['category_id']) && $work['category_id'] === $category_id) {
                $works_in_category[$work_id] = $work;
            }
        }
        return $works_in_category;
    }

    /**
     * 指定された著者名の作品リストを取得
     */
    public function getWorksByAuthorName($author_name)
    {
        $works_by_author = array();
        foreach ($this->all_works as $work_id => $work) {
            if (isset($work['author']) && $work['author'] === $author_name) {
                $works_by_author[$work_id] = $work;
            }
        }
        return $works_by_author;
    }


    /**
     * 作品IDから単一の作品情報を取得
     */
    public function getWorkById($work_id)
    {
        return isset($this->all_works[$work_id]) ? $this->all_works[$work_id] : null;
    }
    
    /**
     * カテゴリIDから単一のカテゴリ情報を取得
     */
    public function getCategoryById($category_id)
    {
        return isset($this->all_categories[$category_id]) ? $this->all_categories[$category_id] : null;
    }

    /**
     * 全てのカテゴリ情報を取得
     */
    public function getAllCategories()
    {
        return $this->all_categories;
    }

    /**
     * 全ての著者名リストを取得（重複なし）
     */
    public function getAuthorList()
    {
        $authors = array();
        foreach($this->all_works as $work){
            if(!empty($work['author'])){
                $authors[] = $work['author'];
            }
        }
        return array_unique($authors);
    }


    /**
     * 作品に紐づくアセットの情報を取得する
     */
    public function getAssetsForWork($work)
    {
        $assets = array();
        if (empty($work['directory_name'])) {
            return $assets;
        }

        // v2のアセットパス
        $v2_dir = ASSET_PATH_V2 . '/' . $work['directory_name'];
        // v1のアセットパス
        $v1_dir = ASSET_PATH_V1 . '/' . $work['directory_name'];

        $asset_dir = is_dir($v2_dir) ? $v2_dir : (is_dir($v1_dir) ? $v1_dir : null);

        if ($asset_dir === null) {
            return $assets;
        }

        if (isset($work['assets']) && is_array($work['assets'])) {
            foreach ($work['assets'] as $asset_info) {
                $file_path = $asset_dir . '/' . $asset_info['filename'];
                if(file_exists($file_path)){
                    $assets[] = array(
                        'filename' => $asset_info['filename'],
                        'server_path' => $file_path,
                        'size_px' => isset($asset_info['size']) ? getimagesize($file_path)[0] . 'x' . getimagesize($file_path)[1] : 'N/A',
                        'created' => isset($asset_info['created']) ? $asset_info['created'] : date('Y-m-d H:i:s', filectime($file_path))
                    );
                }
            }
        }

        return $assets;
    }
}
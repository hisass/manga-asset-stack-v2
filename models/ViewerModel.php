<?php
class ViewerModel {
    private $dataManager;

    public function __construct(DataManager $dataManager) {
        $this->dataManager = $dataManager;
    }

    public function getCategories() {
        // ここでは表示/非表示のロジックは入れず、全て返す
        return $this->dataManager->getCategories();
    }

    public function getWorkById($work_id) {
        $all_works = $this->dataManager->getWorks();
        foreach ($all_works as $work) {
            if (isset($work['work_id']) && $work['work_id'] === $work_id) {
                return $work;
            }
        }
        return null; // 見つからなかった場合
    }
    
    public function getWorksByCategoryId($category_id) {
        $all_works = $this->dataManager->getWorks();
        $filtered_works = array();

        foreach ($all_works as $work) {
            if (isset($work['category_id']) && $work['category_id'] === $category_id) {
                $filtered_works[] = $work;
            }
        }
        return $filtered_works;
    }

    // 今後、検索機能などもここに追加していきます
}
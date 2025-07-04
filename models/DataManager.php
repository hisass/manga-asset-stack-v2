<?php
/**
 * データを管理するモデル
 * sozai_v2.json (ベース) と sozai_v2_delta.json (差分) を扱う
 */
class DataManager
{
    private $base_path;
    private $delta_path;
    private $category_order_path;
    private $data;

    public function __construct()
    {
        $this->base_path = BASE_DIR_PATH . '/data/sozai_v2.json';
        $this->delta_path = BASE_DIR_PATH . '/data/sozai_v2_delta.json';
        $this->category_order_path = BASE_DIR_PATH . '/data/categories_order.json';
        $this->loadData();
    }

    private function loadData()
    {
        // ベースデータの読み込み
        $base_json = file_get_contents($this->base_path);
        $this->data = json_decode($base_json, true);
        if (!$this->data) {
            $this->data = array('works' => array(), 'categories' => array());
        }

        // 差分データの読み込みとマージ
        if (file_exists($this->delta_path)) {
            $delta_json = file_get_contents($this->delta_path);
            $delta_data = json_decode($delta_json, true);

            if ($delta_data) {
                // 追加/更新
                if (isset($delta_data['upserted']['works'])) {
                    foreach ($delta_data['upserted']['works'] as $work_id => $work) {
                        $this->data['works'][$work_id] = $work;
                    }
                }
                if (isset($delta_data['upserted']['categories'])) {
                    foreach ($delta_data['upserted']['categories'] as $cat_id => $cat) {
                        $this->data['categories'][$cat_id] = $cat;
                    }
                }
                // 削除
                if (isset($delta_data['deleted']['works'])) {
                    foreach ($delta_data['deleted']['works'] as $work_id) {
                        if (isset($this->data['works'][$work_id])) {
                            unset($this->data['works'][$work_id]);
                        }
                    }
                }
            }
        }
    }

    private function saveData($delta_data)
    {
        // 現在の差分データを読み込む
        $current_delta = array(
            'upserted' => array('works' => array(), 'categories' => array()),
            'deleted' => array('works' => array(), 'categories' => array())
        );
        if (file_exists($this->delta_path)) {
            $current_delta_json = file_get_contents($this->delta_path);
            $decoded_delta = json_decode($current_delta_json, true);
            if ($decoded_delta) {
                $current_delta = $decoded_delta;
            }
        }
        
        // 新しい差分をマージ
        if (isset($delta_data['upserted'])) {
            $current_delta['upserted']['works'] = array_merge(
                isset($current_delta['upserted']['works']) ? $current_delta['upserted']['works'] : array(),
                isset($delta_data['upserted']['works']) ? $delta_data['upserted']['works'] : array()
            );
            $current_delta['upserted']['categories'] = array_merge(
                 isset($current_delta['upserted']['categories']) ? $current_delta['upserted']['categories'] : array(),
                isset($delta_data['upserted']['categories']) ? $delta_data['upserted']['categories'] : array()
            );
        }
        if (isset($delta_data['deleted'])) {
             $current_delta['deleted']['works'] = array_merge(
                isset($current_delta['deleted']['works']) ? $current_delta['deleted']['works'] : array(),
                isset($delta_data['deleted']['works']) ? $delta_data['deleted']['works'] : array()
            );
        }

        // JSON_PRETTY_PRINT はPHP 5.4+なので使わない
        $json_string = json_encode($current_delta);
        return file_put_contents($this->delta_path, $json_string);
    }
    
    public function getWorkById($work_id)
    {
        return isset($this->data['works'][$work_id]) ? $this->data['works'][$work_id] : null;
    }

    public function getCategoryById($cat_id)
    {
        return isset($this->data['categories'][$cat_id]) ? $this->data['categories'][$cat_id] : null;
    }

    public function addWork($workData)
    {
        $delta = array(
            'upserted' => array('works' => array($workData['work_id'] => $workData))
        );
        if ($this->saveData($delta)) {
            $this->data['works'][$workData['work_id']] = $workData; // メモリ上のデータも更新
            return $workData['work_id'];
        }
        return false;
    }
    
    public function saveWork($workData)
    {
        $delta = array(
            'upserted' => array('works' => array($workData['work_id'] => $workData))
        );
        return $this->saveData($delta);
    }

    public function deleteWork($work_id)
    {
        $delta = array(
            'deleted' => array('works' => array($work_id))
        );
        return $this->saveData($delta);
    }

    public function addCategory($categoryData)
    {
        $delta = array(
            'upserted' => array('categories' => array($categoryData['id'] => $categoryData))
        );
        // カテゴリ順の末尾にも追加
        $order = $this->getCategoryOrder();
        $order[] = $categoryData['id'];
        $this->saveCategoryOrder($order);

        return $this->saveData($delta);
    }

    public function saveCategory($categoryData)
    {
        $delta = array(
            'upserted' => array('categories' => array($categoryData['id'] => $categoryData))
        );
        return $this->saveData($delta);
    }
    
    private function saveCategoryOrder($order)
    {
        $json_string = json_encode(array_values($order));
        return file_put_contents($this->category_order_path, $json_string);
    }

    public function moveCategoryOrder($id, $direction)
    {
        $order = $this->getCategoryOrder();
        $index = array_search($id, $order);

        if ($index !== false) {
            $out = array_splice($order, $index, 1);
            if ($direction === 'up' && $index > 0) {
                array_splice($order, $index - 1, 0, $out);
            } elseif ($direction === 'down' && $index < count($order)) {
                array_splice($order, $index + 1, 0, $out);
            } else {
                 array_splice($order, $index, 0, $out); // 元の位置に戻す
            }
            $this->saveCategoryOrder($order);
        }
    }
}
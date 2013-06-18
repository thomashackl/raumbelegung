<?php
/**
 * resourcestree for Raumbelegung
 */
class resources_tree extends sqlTree {

    protected $SQL = "SELECT * FROM `%s` LEFT JOIN `resources_rooms_order` USING (resource_id) WHERE `%s` = ? ORDER BY -priority DESC";

    public function setData($data) {
        parent::setData($data);
        $this->text = utf8_encode($this->data['name'] . " " . $this->data['description']);
        $this->checked = $this->data['checked'] != 0;
    }

    public static function clearRoomOrder() {
        $db = DBManager::get();
        $sql = "TRUNCATE TABLE resources_rooms_order";
        $db->exec($sql);
    }

    public function loadFromJson($json) {
        $this->id = $json->id;
        $this->checked = $json->checked;
        if ($json->children) {
            foreach ($json->children as $child) {
                if ($this->isChecked($child)) {
                    $newchild = $this->addNewChild();
                    $newchild->key = $this->newkey;
                    $newchild->newkey = $this->newkey + 1;
                    $this->newkey = $newchild->loadFromJson($child);
                }
            }
        }
        $db = DBManager::get();
        $sql = "INSERT INTO `resources_rooms_order` (`resource_id` ,`priority`, checked) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->id, $this->key? : 0, $this->checked));
        return $this->newkey;
    }

    public function filterByChecked() {
        foreach ($this->children as $key => $child) {
            if (!$child->isChecked()) {
                unset($this->children[$key]);
            }
        }
    }

    public function isChecked($json) {
        if ($json->checked) {
            return true;
        }
        if ($json->children) {
            foreach ($json->children as $child) {
                $checked = $checked || $this->isChecked($child);
            }
        }
        return $checked;
    }

}

?>

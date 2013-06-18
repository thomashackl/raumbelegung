<?php
require_once 'app/controllers/studip_controller.php';

class IndexController extends StudipController {

    /**
     * Diese Methode wird bei jedem Pfad aufgerufen
     */
    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);

        // Lade das standard StudIP Layout
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));

        // Lade 'date' aus dem Request.
        $this->date = Request::get('date');
    }

    /*
     * Listaction Aufruf. Läd nur die Belegung. Toll oder?
     */

    public function list_action() {
        $this->loadBelegung();
    }

    /*
     * Beim Table wird auch nur die Belegung geladen. Absolut krasser Code
     */
    public function table_action() {
        $this->loadBelegung();
    }

    /*
     * Wenn wir die Settings Seite aufrufen, passiert jetzt schon etwas mehr
     */
    public function settings_action() {
        
        /* 
         * Wenn das Formular auf der Settingsseite abgeschickt haben, dann
         * existiert ein Request. (Best comment ever)
         */
        if ($request = Request::get('update')) {
            
            //schei? encoding
            $json = json_decode(iconv("ISO-8859-1", "UTF-8", $request));
            
            // Lege einen Resourcenbaum an der uns den die Einstellungen updated
            $tree = new resources_tree();
            $tree->clearRoomOrder();
            $tree->loadFromJson($json);
        }
        
        // Lade den Resourcenbaum
        $db = DBManager::get();
        $settingstree = new resources_tree();
        $settingstree->setID('0');
        $settingstree->loadChildrenRecursiveFromSQL($db, "resources_objects", "resource_id");

        // Gib der View den Baum als JSON Object in der Variable $data
        $this->data = json_encode($settingstree);
    }

    /*
     * Schöner Codestil. 3 Zeilen doppelten Code vermieden. Woohoo
     */
    private function loadBelegung() {
        
        // Wenn wir kein Datum haben nimm einfach den heutigen Tag
        $day = $this->date ? : "today";
        
        // Leg eine Belegung an
        $belegung = new Belegung($day);
        
        /*
         * Das hier ist jetzt etwas interessanter. Indem wir den Rootknoten des
         * Belegungsbaums an $this->room weitergeben kann die View dieses Objekt
         * per $room erreichen.
         */
        $this->room = $belegung->root;
    }
}
?>

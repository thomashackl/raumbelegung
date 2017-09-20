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
        if (Request::submitted('save')) {
            
            // Alle Einträge resetten
            $deactivateAll = DBManager::get()->prepare("UPDATE resources_rooms_order SET checked = 0, priority = 99999 WHERE user_id = ?");
            $deactivateAll->execute(array($GLOBALS['user']->id));
            
            // Update request
            $update = DBManager::get()->prepare("UPDATE resources_rooms_order SET checked = ?, priority = ? WHERE resource_id = ? AND user_id = ?");
            
            foreach (Request::getArray('resources') as $resource) {
                $update->execute(array(1, ++$i, $resource, $GLOBALS['user']->id));
            } 
        }
        
        // Lade die ausgewählte Raumbelegung
        $this->resources = RoomUsageResourceObject::getAll();
        
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

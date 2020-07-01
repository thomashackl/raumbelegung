<?php
require_once 'app/controllers/studip_controller.php';

class IndexController extends StudipController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);

        // Lade das standard StudIP Layout
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));

        // Lade 'date' aus dem Request.
        $this->date = Request::get('date');

        PageLayout::addStylesheet($this->dispatcher->plugin->getPluginURL() . "/assets/style.css");
        PageLayout::addScript($this->dispatcher->plugin->getPluginURL() . "/assets/raumbelegung.js");
    }

    /**
     * Load list view with all resource assignments
     */
    public function list_action() {
        Navigation::activateItem('/calendar/raumbelegung/list');
        PageLayout::setTitle($this->dispatcher->plugin->getDisplayName() . ' - ' .
            dgettext('roomplanplugin', 'Tagesansicht (Liste)'));
        $this->loadBelegung();
    }

    /**
     * Load table view with all resource assignments
     */
    public function table_action() {
        Navigation::activateItem('/calendar/raumbelegung/table');
        PageLayout::setTitle($this->dispatcher->plugin->getDisplayName() . ' - ' .
            dgettext('roomplanplugin', 'Tagesansicht (Tabelle)'));
        $this->loadBelegung();
    }

    /**
     * Wenn wir die Settings Seite aufrufen, passiert jetzt schon etwas mehr
     */
    public function settings_action() {
        Navigation::activateItem('/calendar/raumbelegung/settings');

        // Lade die ausgewählte Raumbelegung
        $this->resources = Resource::findBySQL("`parent_id` = ''");
    }

    /**
     * Save custom resources order and checked status
     */
    public function save_settings_action()
    {
        // Alle Einträge resetten
        $deactivateAll = DBManager::get()->prepare("UPDATE resources_rooms_order SET checked = 0, priority = 99999 WHERE user_id = ?");
        $deactivateAll->execute(array($GLOBALS['user']->id));

        // Update request
        $update = DBManager::get()->prepare("UPDATE resources_rooms_order SET checked = ?, priority = ? WHERE resource_id = ? AND user_id = ?");

        $success = true;
        $i = 0;
        foreach (Request::getArray('resources') as $resource => $checked) {
            $success = $success && $update->execute(array($checked, ++$i, $resource, $GLOBALS['user']->id));
        }

        if ($success) {
            PageLayout::postSuccess(dgettext('roomplanplugin', 'Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('roomplanplugin', 'Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('index/settings');
    }

    /**
     * Load assignments for rooms on the given day.
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

<?php
/**
 * Raumbelegung - Plugin zur Anzeige aller Raumbelegungen an einem Tag
 *
 * Das Raumbelegungsplugin zeigt alle Termine geornet nach Raum und Zeit in
 * einer Liste oder einer Tabelle an. Root verfügt über die
 * Einstellungsmöglichkeit, Raume und deren Oberkategorien auszublenden, bzw
 * diese zu ordnen.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once(__DIR__.'/AssignmentsExportCronjob.php');

class Raumbelegung extends StudipPlugin implements SystemPlugin {
    /*
     *  Ein Systemplugin wird auf JEDER Seite geladen (Konstruiert) deshalb
     * erzeugen wir hier den Navigationspunkt
     */

    function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__.'/models');

        // Localization
        bindtextdomain('roomplanplugin', __DIR__.'/locale');

        // Lade den Navigationsabschnitt "tools"
        $navigation = Navigation::getItem('/calendar');

        // Observe resource assignment changes for writing additional info to database.
        NotificationCenter::addObserver($this, 'assignSaveInfo', 'ResourcesAssignDidCreate');
        NotificationCenter::addObserver($this, 'assignSaveInfo', 'ResourcesAssignDidUpdate');

        // Hook into resources assignment view and insert the text field for further information.
        if (strpos(Request::path(), 'resources.php') !== false &&
                (Request::option('edit_assign_object') || Request::option('change_object_schedules') || Request::option('quick_view') == 'edit_object_assign')) {
            $info = ResourceAssignInfo::find(
                Request::option('edit_assign_object') ?: Request::option('change_object_schedules'));
            if (!$info) {
                $info = new ResourceAssignInfo();
            }
            PageLayout::addBodyElements(
                '<div id="assign-info" style="display:none">'.
                '<label>'.
                dgettext('roomplanplugin', 'Zusätzliche Informationen').'<br>'.
                '<textarea name="assign_info" cols="75" rows="4" class="add_toolbar" style="width: 600px">'.htmlReady($info->info).'</textarea>'.
                '</label>'.
                '</div>');
            // Load correct js depending on mode.
            if (Studip\ENV == 'development') {
                $js = $this->getPluginURL() . '/assets/assign-info.js';
            } else {
                $js = $this->getPluginURL() . '/assets/assign-info.min.js';
            }
            PageLayout::addScript($js);
        }

        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, [], 'index/list'));

        $navigation->addSubNavigation('list',
            new Navigation(dgettext('roomplanplugin', 'Tagesansicht (Liste)'),
                PluginEngine::getURL($this, [], 'index/list')));
        $navigation->addSubNavigation('table',
            new Navigation(dgettext('roomplanplugin', 'Tagesansicht (Tabelle)'),
                PluginEngine::getURL($this, [], 'index/table')));
        $navigation->addSubNavigation('semester',
            new Navigation(dgettext('roomplanplugin', 'Semesteransicht'),
                PluginEngine::getURL($this, [], 'semester')));
        $navigation->addSubNavigation('export',
            new Navigation(dgettext('roomplanplugin', 'Export der Raumbelegungen'),
                PluginEngine::getURL($this, [], 'export')));
        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation->addSubNavigation('openingtimes',
                new Navigation(dgettext('roomplanplugin', 'Gebäudeöffnungszeiten'),
                    PluginEngine::getURL($this, [], 'openingtimes/times')));
        }
        $navigation->addSubNavigation('settings',
            new Navigation(dgettext('roomplanplugin', 'Einstellungen'),
                PluginEngine::getURL($this, [], 'index/settings')));

        Navigation::addItem('/calendar/raumbelegung', $navigation);

        // Füge nun dem Head die benötigten Styles und Scripts hinzu
        PageLayout::addStylesheet($this->getPluginURL() . "/assets/style.css");
        PageLayout::addScript($this->getPluginURL() . "/assets/raumbelegung.js");
        StudipAutoLoader::addAutoloadPath($this->getPluginPath() . '/models');
    }

    /**
     * Wird das Plugin tatsächlich aufgerufen, so landen wir in der perform
     * Methode
     *
     * @param string Die restliche Pfadangabe
     */
    public function perform($unconsumed_path)
    {
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'index'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    /**
     * Store additional info given for a resource assignment.
     *
     * @param $event The triggered event (ResourcesAssignDidCreate or ResourcesAssignDidUpdate)
     * @param $assign_id ID of the changed assignment
     * @param $data additional data like affected course or resource ID.
     */
    public function assignSaveInfo($event, $assign_id, $data) {
        $i = ResourceAssignInfo::find($assign_id);
        if (!$i) {
            $i = new ResourceAssignInfo();
            $i->assign_id = $assign_id;
            $i->user_id = $GLOBALS['user']->id;
        }
        $i->info = trim(Request::get('assign_info'));
        $i->store();
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        AssignmentsExportCronjob::register()->schedulePeriodic(41, 0)->activate();
    }

    public static function onDisable($pluginId) {
        AssignmentsExportCronjob::unregister();
        parent::onDisable($pluginId);
    }

    public function getDisplayName()
    {
        return dgettext('roomplanplugin', 'Raumbelegung');
    }

}

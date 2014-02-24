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
class Raumbelegung extends StudipPlugin implements SystemPlugin {

    /*
     *  Ein Systemplugin wird auf JEDER Seite geladen (Konstruiert) deshalb
     * erzeugen wir hier den Navigationspunkt
     */
    function __construct() {
        parent::__construct();

        // Lade den Navigationsabschnitt "tools"
        $navigation = Navigation::getItem('/tools');
        
        // Erstelle einen neuen Navigationspunkt
        $roomplaner_navi = new Navigation(dgettext('roomplanplugin', 'Raumbelegung'), PluginEngine::getUrl('raumbelegung/index/list'));
        
        // Binde disen Punkt unter "tools" ein
        $navigation->addSubNavigation('raumbelegung', $roomplaner_navi);
    }

    /**
     * Wird das Plugin tatsächlich aufgerufen, so landen wir in der perform
     * Methode
     * 
     * @param string Die restliche Pfadangabe
     */
    function perform($unconsumed_path) {
        
        // Erstelle Unternavigation
        $navigation = Navigation::getItem('/tools/raumbelegung');
        $listview = new Navigation(dgettext('roomplanplugin', 'Anzeige (Liste)'), PluginEngine::getUrl('raumbelegung/index/list', array("date" => Request::get('date'))));
        $tableview = new Navigation(dgettext('roomplanplugin', 'Anzeige (Tabelle)'), PluginEngine::getUrl('raumbelegung/index/table', array("date" => Request::get('date'))));
        $navigation->addSubNavigation('listview', $listview);
        $navigation->addSubNavigation('tableview', $tableview);

        // Für root erstelle auch den Navipunkt 'Einstellungen'
        if ($GLOBALS['perm']->have_perm('root')) {
            $navi_settings = new Navigation(dgettext('roomplanplugin', 'Einstellungen'), PluginEngine::getUrl('raumbelegung/index/settings'));
            $navigation->addSubNavigation('settings', $navi_settings);
        }

        // Füge nun dem Head die benötigten Styles und Scripts hinzu
        PageLayout::addStylesheet($this->getPluginURL() . "/js/jquery-easyui-1.3.2/themes/default/easyui.css");
        PageLayout::addStylesheet($this->getPluginURL() . "/js/jquery-easyui-1.3.2/themes/icon.css");
        PageLayout::addScript($this->getPluginURL() . "/js/jquery-easyui-1.3.2/jquery.easyui.min.js");

        // Baue jetzt einen autoloader für alle models (ja ich bin faul)
        $GLOBALS['autoloader_path'] = $this->getPluginPath() . '/trails/models/';
        spl_autoload_register(function ($class) {
                    include_once $GLOBALS['autoloader_path'] . $class . '.php';
                });
                
        /*
         * Jetzt brauchen wir nur noch einen Trailsdispatcher der die restliche
         * Arbeit für uns erledigt. An dieser Stelle springt also die Plugin-
         * verarbeitung weiter in den Trailsordner
         */
        $trails_root = $this->getPluginPath() . "/trails";
        $dispatcher = new Trails_Dispatcher($trails_root, PluginEngine::getUrl('raumbelegung/index'), 'index');
        $dispatcher->dispatch($unconsumed_path);
    }

}

?>

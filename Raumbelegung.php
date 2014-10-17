<?php

/**
 * Raumbelegung - Plugin zur Anzeige aller Raumbelegungen an einem Tag
 *
 * Das Raumbelegungsplugin zeigt alle Termine geornet nach Raum und Zeit in
 * einer Liste oder einer Tabelle an. Root verf�gt �ber die 
 * Einstellungsm�glichkeit, Raume und deren Oberkategorien auszublenden, bzw
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
        $navigation = Navigation::getItem('/calendar');

        // Erstelle einen neuen Navigationspunkt
        $roomplaner_navi = new AutoNavigation(_('Raumbelegung'), PluginEngine::getUrl('raumbelegung/index/list'));

        // Binde disen Punkt unter "tools" ein
        $navigation->addSubNavigation('raumbelegung', $roomplaner_navi);
    }

    /**
     * Wird das Plugin tats�chlich aufgerufen, so landen wir in der perform
     * Methode
     * 
     * @param string Die restliche Pfadangabe
     */
    function perform($unconsumed_path) {
        // Erstelle Unternavigation
        $navigation = AutoNavigation::getItem('/calendar/raumbelegung');
        $listview = new AutoNavigation(_('Tagesansicht (Liste)'), PluginEngine::getUrl('raumbelegung/index/list', array("date" => Request::get('date'))));
        $tableview = new AutoNavigation(_('Tagesansicht (Tabelle)'), PluginEngine::getUrl('raumbelegung/index/table', array("date" => Request::get('date'))));
        $navigation->addSubNavigation('listview', $listview);
        $navigation->addSubNavigation('tableview', $tableview);

        // F�ge Navigation f�r die Wochenansicht an
        $navigation->addSubNavigation('semesterview', new AutoNavigation(_('Semesteransicht'), PluginEngine::getUrl('raumbelegung/semester/index')));

        // F�r root erstelle auch den Navipunkt 'Einstellungen'
        $navi_settings = new AutoNavigation(_('Einstellungen'), PluginEngine::getUrl('raumbelegung/index/settings'));
        $navigation->addSubNavigation('settings', $navi_settings);

        // F�ge nun dem Head die ben�tigten Styles und Scripts hinzu
        PageLayout::addStylesheet($this->getPluginURL() . "/assets/style.css");
        PageLayout::addScript($this->getPluginURL() . "/assets/raumbelegung.js");
        // Baue jetzt einen autoloader f�r alle models (ja ich bin faul)
        $GLOBALS['autoloader_path'] = $this->getPluginPath() . '/trails/models/';
        spl_autoload_register(function ($class) {
            include_once $GLOBALS['autoloader_path'] . $class . '.php';
        });

        /*
         * Jetzt brauchen wir nur noch einen Trailsdispatcher der die restliche
         * Arbeit f�r uns erledigt. An dieser Stelle springt also die Plugin-
         * verarbeitung weiter in den Trailsordner
         */
        $trails_root = $this->getPluginPath() . "/trails";
        $dispatcher = new Trails_Dispatcher($trails_root, PluginEngine::getUrl('raumbelegung/index'), 'index');
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

}

?>

<?php

require_once 'app/controllers/studip_controller.php';

class WeekController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
    }

    public function index_action() {
        PageLayout::addScript($this->plugin->getPluginURL() . "/js/week.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL() . "/styles/intelecroomplan.css");
        PageLayout::addStylesheet($this->plugin->getPluginURL() . "/styles/intelecroomprint.css", array('media' => 'print'));
        
        // Fetch requested date
        $this->date = Request::get('date');
        
        // Fetch the top layer rooms
        $this->buildings = current(RoomUsageResourceCategory::findByName('Gebäude'))->objects;
        
        // Get the requested rooms
        if (Request::get('date') && Request::get('building')) {
            
            // At first check if we got a building
            $find = $this->buildings->find(Request::get('building'));
            if ($find) {
                $this->request = $find->children;
            } else {
                
                // If we got nothing we just have a room requested
                $this->request = array(new RoomUsageResourceObject(Request::get('building')));
            }
        }
        
        // Initialise to prevent foreach fails
        $this->timetables = array();
        
        if ($this->request) {
            foreach ($this->request as $request) {
                $this->timetables[] = new IntelecBelegungsplan(Request::get('date'), $request);
            }
        }
    }

}

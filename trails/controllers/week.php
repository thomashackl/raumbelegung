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
        
        // Fetch requested date
        $this->date = Request::get('date');
    }

}

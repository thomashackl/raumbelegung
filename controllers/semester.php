<?php

require_once 'app/controllers/studip_controller.php';

class SemesterController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        $this->flash = Trails_Flash::instance();
    }

    public function index_action() {
        
        if (Request::submitted('print')) {
            $this->flash['semester'] = Request::option('semester');
            $this->flash['building'] = Request::option('building');
            $this->flash['participants'] = Request::int('participants', 0);
            $this->flash['start'] = Request::int('start', 0);
            $this->flash['end'] = Request::int('end', 0);
            $this->flash['lecture_only'] = Request::int('lecture_only', 0);
            $this->flash['empty_rooms'] = Request::int('empty_roome', 0);
            $this->redirect($this->url_for('semester/print'));
        }

        // Fetch requested date
        $this->date = Request::get('date');

        // Fetch the top layer rooms
        $this->buildings = current(RoomUsageResourceCategory::findByName('Gebäude'))->objects;
        
        // Filter the selected rooms from settings
        $this->buildings = SimpleORMapCollection::createFromArray($this->filter($this->buildings) ? : array());

        // Fetch all semester and the selected
        $this->semesters = Semester::getAll();
        $selectedSemester = SimpleORMapCollection::createFromArray($this->semesters)->find(Request::get('semester'));

        // Get the requested rooms
        if ($selectedSemester && Request::get('building')) {

            // At first check if we got a building
            $find = $this->buildings->find(Request::get('building'));
            if ($find) {
                $this->request = $find->filteredChildren;
            } else {

                // If we got nothing we just have a room requested
                $this->request = array(new RoomUsageResourceObject(Request::get('building')));
            }
        }

        // Initialise to prevent foreach fails
        $this->timetables = array();

        if ($this->request) {
            foreach ($this->request as $request) {
                $timetable = new ZIMSemesterBelegungsplan($request);

                // Check if we need to display participants
                if (Request::get('participants')) {
                    $timetable->participants = true;
                }

                if (Request::get('start') && Request::get('end')) {
                    $timetable->loadFromTimespan(strtotime(Request::get('start')), strtotime(Request::get('end')));
                } else {
                    $timetable->loadFromSemester($selectedSemester, Request::get('lecture_only'));
                }
                if (Request::get('empty_rooms') || !$timetable->isEmpty()) {
                    $this->timetables[] = $timetable;
                }
            }
        }
        
        // Find chosen semester
        $this->chosenSemester = Request::get('semester') ? : Semester::findCurrent()->id;
    }

    public function print_action()
    {
        $factory = new Flexi_TemplateFactory(__DIR__ . '/../views');
        $tpl = $factory->open('semester/index');
        $tpl->set_attribute('semesters', Semester::getAll());
        $tpl->set_attribute('choseSemester', $this->flash['semester']);

        // Get the requested rooms
        $selectedSemester = SimpleCollection::createFromArray(Semester::getAll())->find($this->flash['semester']);
        if ($selectedSemester && $this->flash['building']) {

            // At first check if we got a building
            $find = $this->buildings->find($this->flash['building']);
            if ($find) {
                $request = $find->filteredChildren;
            } else {

                // If we got nothing we just have a room requested
                $request = array(new RoomUsageResourceObject($this->flash['building']));
            }
        }
        $tpl->set_attribute('request', $request);

        // Initialise to prevent foreach fails
        $timetables = array();

        if ($request) {
            foreach ($request as $request) {
                $timetable = new ZIMSemesterBelegungsplan($request);

                // Check if we need to display participants
                if ($this->flash['participants']) {
                    $timetable->participants = true;
                }

                if ($this->flash['start'] && $this->flash['end']) {
                    $timetable->loadFromTimespan(strtotime($this->flash['start']), strtotime($this->flash['end']));
                } else {
                    $timetable->loadFromSemester($selectedSemester, $this->flash['lecture_only']);
                }
                if ($this->flash['empty_rooms'] || !$timetable->isEmpty()) {
                    $timetables[] = $timetable;
                }
            }
        }
        $tpl->set_attribute('timetables', $timetables);

        $this->render_text($tpl->render());
    }

    public function filter($rooms) {
        // Fill cache
        if (!$this->rooms) {
            $this->rooms = array();
            $stmt = DBManager::get()->prepare("SELECT resource_id FROM resources_rooms_order WHERE user_id = ? ORDER BY priority");
            $stmt->execute(array($GLOBALS['user']->id));
            while ($next = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->rooms[$next['resource_id']] = false;
            }
        }
        
        // Produce instance of rooms
        $roomcache = $this->rooms;
        
        // Now sort and filter rooms 
        foreach ($rooms as $room) {
            if (key_exists($room->resource_id, $roomcache)) {
                $roomcache[$room->resource_id] = $room;
            }
        }
        
        // Build result
        foreach ($roomcache as $room) {
            if ($room) {
                $result[] = $room;
            }
        }
        return $result;
    }

    // customized #url_for for plugins
    public function url_for($to = '') {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    }
}

<?php

require_once 'app/controllers/studip_controller.php';

class SemesterController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action() {
        
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
}
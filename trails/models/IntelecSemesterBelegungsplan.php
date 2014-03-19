<?php

class IntelecSemesterBelegungsplan {

    const SLOTSIZE = 30;
    public $semester;
    public $object;

    public function __construct(Semester $semester, RoomUsageResourceObject $object) {
        
        $this->semester = $semester;
        $this->object = $object;
        
        $this->getHeadline();

        // Get headline attributes
        $this->headline = $object->name . ($object->description ? (' (' . $object->description . ')') : '');
        $this->adress = $object->parent->getProperty('Adresse');
        $this->places = $object->getProperty('Sitzplätze') . ($object->getProperty('Sitzplätze Ergänzung') ? '(' . $object->getProperty('Sitzplätze Ergänzung') . ')' : '');
        $this->area = $object->getProperty('Fläche');
        $this->timespan = self::timeformat($start) . ' - ' . self::timeformat($end);
        $this->timestamp = self::timeformat(time());
        
        // Get all single assignments
        $this->getSingleAssignement($object, $semester);

        // Now get content
        for ($time = 8; $time < 24; $time++) {

            // Clear the slot
            $slot = array();

            // First column should contain an enumeration
            $slot[] = array('title' => $time);

            // Now get actuall stuff
            for ($day = 0; $day < 5; $day++) {

                // Calculate startstamp
                $startstamp = $start + $day * 3600 * 24 + $time * 3600;

                //$assignment = self::getAssignement($object, $start, $day, $time);

                if ($assignment) {
                    // Check how long the assignment runs
                    $runtime = ($assignment['end'] - $assignment['begin']) / 3600;

                    // Set jumper
                    $jump[$day] += $runtime - 1;

                    $slot[] = array('content' => array(
                            "name" => mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;"),
                            "dozenten" => $assignment['dozenten'],
                            "teilnehmer" => $assignment['teilnehmer'],
                            "size" => self::SLOTSIZE * $runtime,
                            "margin" => ($assignment['begin'] - $startstamp) / 3600 * self::SLOTSIZE
                    ));
                } else {
                    // If no assignement push an empty slot
                    $slot[] = array('title' => '');
                }
            }

            // Push another timeslot
            $slot[] = array('title' => ctype_digit((string) $time) ? $time : '');

            // Special case at first saturday
            if ($time == 8) {

                $assignments = self::getWeekendAssignements($object, $start);
                $slot[] = array('weekend' => $assignments ? : array());
            }
            $this->hour[] = $slot;
        }
    }

    private static function timeformat($stamp) {
        return strftime('%a. %d.%m.%y', $stamp);
    }

    /**
     * Fetches roomasignments for a specified day and time
     */
    private function getAssignement($object, $semester) {

        $sql = "SELECT * , COALESCE(s.Name, user_free_name) as realname FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    WHERE o.resource_id = ? 
                    AND a.begin >= ? and a.begin < ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($object->id, $semester->beginn, $semester->ende));
        
        // if we have some non cyclic dates initialize the array
        if ($stmt->rowCount()) {
            $this->dayassigns = array_fill(0, 7, array());
        }

        // Parse day and time
        while ($assign = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->dayassigns[date( "w", $assign['begin'])][date( "H", $assign['begin'])][$assign['Seminar_id']][] = $assign;
            /*$assign->dow = date( "w", $assign->begin);
            $assign->starthour = date( "i", $assign->begin);
            $assign->duration = $assign->end - $assign->begin;
            $result[] = $assign;*/
        }
        var_dump($this->dayassigns);
        //return $result;
    }

}

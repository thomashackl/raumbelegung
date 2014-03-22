<?php

class IntelecSemesterBelegungsplan {

    const SLOTSIZE = 30;

    public $semester;
    public $object;
    public $hour = array();

    public function __construct(Semester $semester, RoomUsageResourceObject $object, $vorlesungsbeginn = false) {

        $this->semester = $semester;
        $this->object = $object;

        // Set dates
        if ($vorlesungsbeginn) {
            $this->start = $semester->vorles_beginn;
            $this->end = $semester->vorles_ende;
        } else {
            $this->start = $semester->beginn;
            $this->end = $semester->ende;
        }

        $this->getHeadline($this->object, $this->start, $this->end);

        // Get all single assignments
        $this->getAssignement($object, $semester);

        // Fetch priorities
        //$this->getPriorities();
        // Sort priorities
        //$thi
    }

    private static function timeformat($stamp) {
        return strftime('%a. %d.%m.%y', $stamp);
    }

    /**
     * Fetches roomasignments for a specified day and time
     */
    private function getAssignement($object) {

        $sql = "SELECT * , COALESCE(s.Name, user_free_name) as realname FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    WHERE o.resource_id = :id 
                    AND 
                    ((a.begin >= :start AND  a.begin <= :end)
                    OR
                    (a.begin <= :end AND a.repeat_end >= :start))";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->bindParam(':start', $this->start);
        $stmt->bindParam(':end', $this->end);
        $stmt->bindParam(':id', $object->id);
        $stmt->execute();

        $this->parseAssigns($stmt->fetchAll(PDO::FETCH_ASSOC));

        // Parse day and time
        /* while ($assign = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $this->dayassigns[date("w", $assign['begin'])][date("H", $assign['begin'])][$assign['Seminar_id']][] = $assign;
          $assign->dow = date( "w", $assign->begin);
          $assign->starthour = date( "i", $assign->begin);
          $assign->duration = $assign->end - $assign->begin;
          $result[] = $assign;
          } */
    }

    private function parseAssigns($assigns) {
        var_dump($assigns);
        die;
    }

    /**
     * Get headline elements
     * @param type $object the object
     * @param type $start start
     * @param type $end end
     */
    private function getHeadline($object, $start, $end) {
        $this->headline = $object->name . ($object->description ? (' (' . $object->description . ')') : '');
        $this->adress = $object->parent->getProperty('Adresse');
        $this->places = $object->getProperty('Sitzplätze') . ($object->getProperty('Sitzplätze Ergänzung') ? '(' . $object->getProperty('Sitzplätze Ergänzung') . ')' : '');
        $this->area = $object->getProperty('Fläche');
        $this->timespan = self::timeformat($start) . ' - ' . self::timeformat($end);
        $this->timestamp = self::timeformat(time());
    }

    private function old() {
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
                            //"name" => mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;"),
                            "name" => $assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'],
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
            /* if ($time == 8) {

              $assignments = self::getWeekendAssignements($object, $start);
              $slot[] = array('weekend' => $assignments ? : array());
              } */
            $this->hour[] = $slot;
        }
    }

}

<?php

class IntelecBelegungsplan {

    public function __construct($date, RoomUsageResourceObject $object) {

        // Calculate timestamps
        $start = strtotime('last monday', strtotime($date));
        $end = $start + 7 * 24 * 60 * 60 - 1;

        // Get headline attributes
        $this->headline = $object->name . ($object->description ? (' (' . $object->description . ')') : '');
        $this->adress = $object->parent->getProperty('Adresse');
        $this->places = $object->getProperty('Sitzplätze') . ($object->getProperty('Sitzplätze Ergänzung') ? '(' . $object->getProperty('Sitzplätze Ergänzung') . ')' : '');
        $this->area = $object->getProperty('Fläche');
        $this->timespan = self::timeformat($start) . ' - ' . self::timeformat($end);
        $this->timestamp = self::timeformat(time());
        
        $jump = array();

        // Now get content
        for ($time = 8; $time <= 23; $time++) {

            // Clear the slot
            $slot = array();
            
            // First column should contain an enumeration
            $slot[] = array('title' => $time);

            // Now get actuall stuff
            for ($day = 0; $day < 5; $day++) {

                // Check if jump is required
                if ($jump[$day]) {
                    $jump[$day]--;
                    continue;
                }

                $assignment = self::getAssignement($object, $start, $day, $time);

                if ($assignment) {
                    // Check how long the assignment runs
                    $runtime = ($assignment['end'] - $assignment['begin']) / 3600;

                    // Set jumper
                    $jump[$day] += $runtime - 1;

                    $slot[] = array('content' => array(
                            "timeslots" => $runtime,
                            "name" => mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;"),
                            "dozenten" => $assignment['dozenten'],
                            "teilnehmer" => $assignment['teilnehmer']
                    ));
                } else {
                    // If no assignement push an empty slot
                    $slot[] = array('title' => '');
                }
                
            }
            
            // Push another timeslot
            $slot[] = array('title' => $time);

            // Special case at first saturday
            /* if ($time == 8) {

              $assignments = self::getWeekendAssignements($object, $start, $day, $time);
              $html .= '<td rowspan="16">samstag</td>';
              } */
            $this->hour[] = $slot;
            
        }
    }


    private static function timeformat($stamp) {
        return strftime('%a. %d.%m.%y', $stamp);
    }

    /**
     * Fetches roomasignments for a specified day and time
     */
    private static function getAssignement($object, $start, $day, $hour) {
        $startstamp = $start + $day * 3600 * 24 + $hour * 3600;
        $endstamp = $startstamp + 3599;

        $sql = "SELECT *, COALESCE(s.Name, user_free_name) as realname FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    WHERE o.resource_id = ? 
                    AND a.begin >= ? and a.begin < ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($object->id, $startstamp, $endstamp));

        // find result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Only calculate dozenten and users if we actually got an assignment
        if ($result) {

            // Fetch dozenten
            $stmt = DBManager::get()->prepare("SELECT Nachname FROM seminar_user JOIN auth_user_md5 USING (user_id) WHERE seminar_id = ? AND status = 'dozent'");
            $stmt->execute(array($result['Seminar_id']));

            $dozenten = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['dozenten'] = join(', ', $dozenten);

            // Fetch teilnehmer
            $stmt = DBManager::get()->prepare("SELECT COUNT(*) FROM seminar_user WHERE seminar_id = ? AND (status = 'autor' OR status = 'user')");
            $stmt->execute(array($result['Seminar_id']));
            $result['teilnehmer'] = $stmt->fetch(PDO::FETCH_COLUMN);
        }
        return $result;
    }

    /**
     * Fetches roomasignments for the weekend
     */
    private static function getWeekendAssignement($object, $start) {
        $startstamp = $start + $day * 3600 * 24 + $hour * 3600;
        $endstamp = $startstamp + 3599;

        $sql = "SELECT *, COALESCE(s.Name, user_free_name) as realname FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    WHERE o.resource_id = ? 
                    AND a.begin >= ? and a.begin < ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($object->id, $startstamp, $endstamp));

        // find result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Only calculate dozenten and users if we actually got an assignment
        if ($result) {

            // Fetch dozenten
            $stmt = DBManager::get()->prepare("SELECT Nachname FROM seminar_user JOIN auth_user_md5 USING (user_id) WHERE seminar_id = ? AND status = 'dozent'");
            $stmt->execute(array($result['Seminar_id']));

            $dozenten = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['dozenten'] = join(', ', $dozenten);

            // Fetch teilnehmer
            $stmt = DBManager::get()->prepare("SELECT COUNT(*) FROM seminar_user WHERE seminar_id = ? AND (status = 'autor' OR status = 'user')");
            $stmt->execute(array($result['Seminar_id']));
            $result['teilnehmer'] = $stmt->fetch(PDO::FETCH_COLUMN);
        }
        return $result;
    }

}

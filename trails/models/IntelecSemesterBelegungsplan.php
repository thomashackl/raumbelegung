<?php

class IntelecSemesterBelegungsplan {

    const SLOTSIZE = 30;
    const STARTTIME = 8;
    const ENDTIME = 23;

    public $semester;
    public $object;
    public $hour = array();
    public $takenSlot = array();

    public function __construct(Semester $semester, RoomUsageResourceObject $object, $vorlesungsbeginn = false) {

        $this->semester = $semester;
        $this->object = $object;

        $this->fillHours();

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

        $this->parseCyclicAssigns($stmt->fetchAll(PDO::FETCH_ASSOC));

        // Parse day and time
        /* while ($assign = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $this->dayassigns[date("w", $assign['begin'])][date("H", $assign['begin'])][$assign['Seminar_id']][] = $assign;
          $assign->dow = date( "w", $assign->begin);
          $assign->starthour = date( "i", $assign->begin);
          $assign->duration = $assign->end - $assign->begin;
          $result[] = $assign;
          } */
    }

    private function parseCyclicAssigns($assigns) {
        $geilheit = array();
        foreach ($assigns as $assign) {
            if ($assign['metadate_id']) {
                if (strftime('%u', $assign['begin']) > 5) {
                    $this->weekendDate($assign);
                } else {
                    $geilheit[$assign['metadate_id']] ++;
                    $map[$assign['metadate_id']] = $assign;
                }
            }
        }
        arsort($geilheit);
        foreach ($geilheit as $key => $assign) {
            // Get the object from the map
            $assignment = $map[$key];

            // Calculate runtime
            $assignment['runtime'] = ($assignment['end'] - $assignment['begin']) / 3600;

            $slots = $this->getSlots($assignment);
            if (!$this->slotsTaken($this->getSlots($assignment))) {
                $this->takeSlots($slots);
                $this->loadDozentenAndTeilnehmer($assignment);
                // build 
                $this->hour[date('G', $assignment['begin'])][strftime('%u', $assignment['begin'])] = array(
                    'content' => array(
                        //"name" => mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;"),
                        "name" => $assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'],
                        "dozenten" => $assignment['dozenten'],
                        "teilnehmer" => $assignment['teilnehmer'],
                        "size" => self::SLOTSIZE * $assignment['runtime'],
                        "margin" => ltrim(date('i', $assignment['begin']), '0') / 60 * self::SLOTSIZE));
            } else {
                $this->addUngeilerAssign($assignment);
            }
        }
    }

    private function addUngeilerAssign($assign) {
        $this->dayassigns[strftime('%u', $assign['begin'])][] = $assign['realname'];
    }

    private function getSlots($assign) {
        $day = strftime('%u', $assign['begin']);
        for ($i = date('G', $assign['begin']); $i < date('G', $assign['begin']) + $assign['runtime']; $i++) {
            $result[] = $i . "," . $day;
        }
        return $result;
    }

    private function slotsTaken($slots) {
        foreach ($slots as $slot) {
            if (array_key_exists($slot, $this->takenSlot)) {
                return true;
            }
        }
        return false;
    }

    private function takeSlots($slots) {
        foreach ($slots as $slot) {
            $this->takenSlot[$slot] = 1;
        }
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

    private function fillHours() {
        for ($time = self::STARTTIME; $time <= self::ENDTIME; $time++) {
            $this->hour[$time] = array($time, '', '', '', '', '', $time);
        }
        $this->hour[self::STARTTIME][] = array('weekend' => array());
    }

    private function weekendDate($asset) {
        $name = strftime('%a. %d.%m., %H', $asset['begin'])
                . '-'
                . strftime('%H', $asset['end'])
                . ', ';
        $name .= $this->getDozent($asset);
        $this->hour[self::STARTTIME][7]['weekend'][] = $name;
    }

    private function getDozent($asset) {
        $stmt = DBManager::get()->prepare("SELECT Nachname FROM seminar_user JOIN auth_user_md5 USING (user_id) WHERE seminar_id = ? AND status = 'dozent' LIMIT 1");
        $stmt->execute(array($asset['Seminar_id']));
        $dozent = $stmt->fetch(PDO::FETCH_COLUMN);
        return $dozent;
    }

    private function loadDozentenAndTeilnehmer(&$asset) {
        if ($asset['Seminar_id']) {
            $stmt = DBManager::get()->prepare("SELECT Nachname FROM seminar_user JOIN auth_user_md5 USING (user_id) WHERE seminar_id = ? AND status = 'dozent' AND nachname != 'N.' ORDER BY position DESC");
            $stmt->execute(array($asset['Seminar_id']));

            $dozenten = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $asset['dozenten'] = join(', ', $dozenten);

            // Fetch teilnehmer
            $stmt = DBManager::get()->prepare("SELECT COUNT(*) FROM seminar_user WHERE seminar_id = ? AND (status = 'autor' OR status = 'user')");
            $stmt->execute(array($asset['Seminar_id']));
            $asset['teilnehmer'] = $stmt->fetch(PDO::FETCH_COLUMN) ? : null;
        }
    }

}

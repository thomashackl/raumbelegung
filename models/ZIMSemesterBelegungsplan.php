<?php

class ZIMSemesterBelegungsplan {

    const PAGESIZE = 540;
    const SLOTSIZE = 26;
    const STARTTIME = 8;
    const ENDTIME = 23;

    public $semester;
    public $object;
    public $hour = array();
    public $takenSlot = array();
    public $dayassigns = array();
    private $empty = true;

    public function __construct(RoomUsageResourceObject $object, $starthour = 8, $endhour = 23) {
        $this->object = $object;
        $this->starthour = $starthour;
        $this->endhour = $endhour;
        $this->fillHours($starthour, $endhour);
    }

    public function loadFromSemester(Semester $semester, $vorlesungsbeginn = false) {
        if ($vorlesungsbeginn) {
            $this->start = $semester->vorles_beginn;
            $this->end = $semester->vorles_ende;
        } else {
            $this->start = $semester->beginn;
            $this->end = $semester->ende;
        }
        $this->load();
    }

    public function loadFromTimespan($start, $end) {
        $this->start = $start;
        $this->end = $end;
        $this->load();
    }

    public function isEmpty() {
        return $this->empty;
    }

    private function load() {
        $this->getHeadline($this->object, $this->start, $this->end);
        $this->getAssignement($this->object);
    }

    private static function timeformat($stamp) {
        return strftime('%a. %d.%m.%y', $stamp);
    }

    /**
     * Fetches roomasignments for a specified day and time
     */
    private function getAssignement($object) {
        $sql = "SELECT * ,
            COALESCE(IF(LENGTH(s.Name),CONCAT_WS(' ', s.VeranstaltungsNummer, s.Name),NULL), u.Nachname, user_free_name) as realname
            FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    JOIN resources_rooms_order ro USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    LEFT JOIN auth_user_md5 u ON (t.range_id = u.user_id)
                    WHERE o.resource_id = :id
                    AND ro.user_id = :userid
                    AND
                    ((a.begin >= :start AND  a.begin <= :end)
                    OR
                    (a.begin <= :end AND a.repeat_end >= :start))
                    ORDER BY ro.priority, a.begin";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->bindParam(':start', $this->start);
        $stmt->bindParam(':end', $this->end);
        $stmt->bindParam(':id', $object->id);
        $stmt->bindParam(":userid", $GLOBALS['user']->id);
        $stmt->execute();
        $assigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->enfoldAssigns($assigns);
        $this->parseCyclicAssigns($assigns);
    }

    private function parseCyclicAssigns($assigns) {
        $geilheit = array();
        foreach ($assigns as $assign) {
            if ($assign['metadate_id']) {

                // This timetable is not empty
                $this->empty = false;

                if (strftime('%u', $assign['begin']) > 5) {
                    $this->weekendDate($assign);
                } else {
                    $geilheit[$assign['metadate_id']] ++;
                    $map[$assign['metadate_id']] = $assign;
                }
            } else {

                // Check for repeated assigns.
                if ($assign['repeat_end'] && $assign['repeat_quantity'] != 0) {

                    $i = 0;

                    while ($assign['end'] <= $assign['repeat_end'] && ($assign['repeat_quantity'] == -1 || $i < $assign['repeat_quantity'])) {

                        // Füge dem Raum einen Termin hinzu
                        if ($assign['begin'] >= $this->start && $assign['begin'] <= $this->end && $assign['end'] >= $this->start && $assign['end'] <= $this->end) {
                            // This timetable is not empty
                            $this->empty = false;

                            // Now check for weekend
                            if (strftime('%u', $assign['begin']) <= 5) {
                                $geilheit[++$terminnr] = 1 / $terminnr - 0.1;
                                $map[$terminnr] = $assign;
                            } else {
                                $this->weekendDate($assign);
                            }
                        }

                        // Calculate next
                        if ($assign['repeat_day_of_week']) {
                            $next = $assign['repeat_interval'] == 1 ? '+1 week' : '+' . $assign['repeat_interval'] . ' weeks';
                            $assign['begin'] = strtotime($next, $assign['begin']);
                            $assign['end'] = strtotime($next, $assign['end']);
                        } else if ($assign['repeat_week_of_month']) {
                            // We need english names for numbers here.
                            switch ($assign['repeat_interval']) {
                                case '1':
                                    $number = 'first';
                                    break;
                                case '2':
                                    $number = 'second';
                                    break;
                                case '3':
                                    $number = 'third';
                                    break;
                                case '4':
                                    $number = 'fourth';
                                    break;
                                case '5':
                                    $number = 'fifth';
                                    break;
                                default:
                                    $number = 'first';
                                    break;
                            }

                            // Ho many months do we have to look ahead?
                            $monthdistance = $assign['repeat_interval'] == 1 ? '+1 month' :
                                '+' . $assign['repeat_interval'] . ' months';

                            // Calculate next date according to given week number in month.
                            // (something like "second monday of 2016-12 14:00")
                            $next = $number . date(' l \o\f Y-m H:i:00', strtotime($monthdistance, $assign['begin']));
                            $assign['begin'] = strtotime($next);
                            $next = $number . date(' l \o\f Y-m H:i:59', strtotime($monthdistance, $assign['end']));
                            $assign['end'] = strtotime($next);
                        } else if ($assign['repeat_day_of_month']) {
                            $next = $assign['repeat_interval'] == 1 ? '+1 month' : '+' . $assign['repeat_interval'] . ' months';
                            $assign['begin'] = strtotime($next, $assign['begin']);
                            $assign['end'] = strtotime($next, $assign['end']);
                        } else if ($assign['repeat_month_of_year']) {
                            $next = $assign['repeat_interval'] == 1 ? '+1 year' : '+' . $assign['repeat_interval'] . ' years';
                            $assign['begin'] = strtotime($next, $assign['begin']);
                            $assign['end'] = strtotime($next, $assign['end']);
                        } else {
                            $next = $assign['repeat_interval'] == 1 ? '+1 day' : '+' . $assign['repeat_interval'] . ' days';
                            $assign['begin'] = strtotime($next, $assign['begin']);
                            $assign['end'] = strtotime($next, $assign['end']);
                        }

                        $i++;
                    }

                // Check if assign is really on this timetable
                } else if ($assign['begin'] >= $this->start && $assign['begin'] <= $this->end && $assign['end'] >= $this->start && $assign['end'] <= $this->end) {

                    // This timetable is not empty
                    $this->empty = false;

                    // Now check for weekend
                    if (strftime('%u', $assign['begin']) <= 5) {
                        $geilheit[++$terminnr] = 1 / $terminnr - 0.1;
                        $map[$terminnr] = $assign;
                    } else {
                        $this->weekendDate($assign);
                    }
                }
            }
        }
        arsort($geilheit);
        foreach ($geilheit as $key => $assign) {

            // Get the object from the map
            $assignment = $map[$key];

            // Calculate runtime
            $assignment['runtime'] = ($assignment['end'] - max(array(strtotime('today 8am', $assignment['begin']),$assignment['begin']))) / 3600;

            $slots = $this->getSlots($assignment);
            if (!$this->slotsTaken($this->getSlots($assignment))) {
                $this->takeSlots($slots);
                $this->loadDozentenAndTeilnehmer($assignment);
                self::fetchDateinfo($assignment);
                $this->hour[max(array(8, date('G', $assignment['begin'])))][strftime('%u', $assignment['begin'])] = self::forgeEntry($assignment, $this->participants, $this->object->getProperty('Sitzplätze'));
            } else {
                $this->addUngeilerAssign($assignment);
            }
        }

        // sort the assigns by date
        array_walk($this->dayassigns, "ksort");
    }

    private function addUngeilerAssign($assign) {
        $this->initAdditionalAssigns();
        $this->dayassigns[strftime('%u', $assign['begin'])][$assign['begin']] = self::fetchDateinfo($assign, true) . ', ' . $assign['realname'];
    }

    private function initAdditionalAssigns() {
        if (!($this->dayassigns)) {
            $this->dayassigns = array_fill(0, 7, array());
        }
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
        $this->headline = $object->name ? : $object->description;
        $this->adress = $object->parent->getProperty('Adresse');
        $this->places = $object->getProperty('Sitzplätze') . ($object->getProperty('Sitzplätze Ergänzung') ? ' (' . $object->getProperty('Sitzplätze Ergänzung') . ')' : '');
        $this->area = $object->getProperty('Fläche');
        $this->timespan = self::timeformat($start) . ' - ' . self::timeformat($end);
        $this->timestamp = self::timeformat(time());
    }

    private function fillHours($start, $end) {
        for ($time = $start; $time <= $end; $time++) {
            $this->hour[$time] = array($time, '', '', '', '', '', $time);
        }
        $this->hour[$this->starthour][] = array('weekend' => array());
    }

    private function weekendDate($asset) {
        $name = strftime('%a. %d.%m., %H', $asset['begin'])
                . '-'
                . strftime('%H', $asset['end'])
                . ', ';
        $name .= $asset['realname'];
        $this->hour[$this->starthour][7]['weekend'][] = $name;
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

    private static function forgeEntry($assignment, $participants = true, $maxplaces = PHP_INT_MAX) {
        return array(
            'content' => array(
                //"name" => mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;"),
                "name" => $assignment['realname'],
                "dozenten" => $assignment['dozenten'],
                "teilnehmer" => $participants ? ($assignment['teilnehmer'] ? dgettext('roomplanplugin', 'Teilnehmer') . ": " . $assignment['teilnehmer'] : null) : null,
                "size" => self::SLOTSIZE * $assignment['runtime'],
                "dateinfo" => self::fetchDateinfo($assignment),
                "margin" => ltrim(date('i', $assignment['begin']), '0') / 60 * self::SLOTSIZE,
                "classes" => ($assignment['metadate_id'] ? 'cyclic' : '') . ' ' . ($participants && $assignment['teilnehmer'] > $maxplaces ? 'overfilled' : '')
        ));
    }

    private static function getMultidayEntry(&$assign) {
        // multiday repeat
        while ($assign['repeat_end'] > $assign['end'] && $assign['repeat_quantity'] == 0) {
            $endOfDay = strtotime("tomorrow", $assign['begin']) - 1;
            if ($assign['repeat_end'] > $endOfDay) {
                $assign['end'] = $endOfDay;
            } else {
                $assign['end'] = $assign['repeat_end'];
            }
            $additional[] = $assign;
            $assign['begin'] = $endOfDay + 1;
        }
        return $additional;
    }

    private static function fetchDateinfo(&$assign, $noCut = false) {
        // if we have a metadate fetch the information of the metadate
        if ($assign['metadate_id']) {
            $cycle = new SeminarCycleDate($assign['metadate_id']);
            if ($noCut) {
                $cycle->toString('full');
            }
            $string = explode(', ', $cycle->toString('full'), 2);
            return $string[1];
        } else {
            return strftime('%d.%m., %H:%M', $assign['begin'])
                    . '-'
                    . strftime('%H:%M', $assign['end']);
        }
    }

    private function enfoldAssigns(&$assigns) {
        $new = array();
        foreach ($assigns as $key => $assign) {
            if (!$assign['metadate_id']) {
                $floating = $this->getFloatingAssigns($assign);
                if ($floating) {
                    $new = array_merge($new, $floating);
                }
                $floating = self::getMultidayEntry($assign);
                if ($floating) {
                    unset($assigns[$key]);
                    $new = array_merge($new, $floating);
                }
            }
        }
        $assigns = array_merge($assigns, $new);

        // Sort by date
        usort($assigns, function($a, $b) {
            return $a['begin'] >= $b['begin'];
        });
    }

    private function getFloatingAssigns($assign) {
        if ($assign['repeat_end'] && $assign['repeat_quantity'] != 0) {

            // Calculate next
            if ($assign['repeat_day_of_week']) {
                $next = "+ {$assign['repeat_interval']} week";
            } elseif ($assign['repeat_week_of_month']) {
                $next = "+ {$assign['repeat_interval']} month";
            } elseif ($assign['repeat_month_of_year']) {
                $next = "+ {$assign['repeat_interval']} year";
            } elseif ($assign['repeat_interval']) {
                $next = "+ {$assign['repeat_interval']} day";
            }

            $i = 0;
            while ($assign['end'] <= $assign['repeat_end'] && ($assign['repeat_quantity'] == -1 || $assign['repeat_quantity'] < $i)) {
                $assign['begin'] = strtotime($next, $assign['begin']);
                $assign['end'] = strtotime($next, $assign['end']);

                // If we are out of the repeat cycle dont add
                if (($assign['end'] > $assign['repeat_end']) || ($assign['end'] > $this->end)) {
                    break;
                }
                if ($assign['begin'] >= $this->start) {
                    $additional[] = $assign;
                }

                $i++;
            }
        }
        return $additional;
    }

    public static function getMaxFootersize() {
        return self::PAGESIZE - (self::ENDTIME - self::STARTTIME) * self::SLOTSIZE;
    }

}

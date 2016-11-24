<?php

/**
 * Belegungsplanobjekt - Repräsentiert die Belegung eines Tages
 */
class Belegung {

    public $date;
    public $begin;
    public $end;
    public $rooms = array();
    public $root;
    public $opening = PHP_INT_MAX;
    public $close = 0;

    /**
     * Lade einen Belegungsplan für einen Tag
     * @param string Datum
     */
    public function __construct($date = null) {
        $datum = date("d.m.Y", strtotime($date));
        $this->root = new Room(0, "Belegungsplan {$datum}");
        $this->rooms['0'] = &$this->root;
        $this->setDate($date);
    }

    /**
     * Lade das Datum
     */
    private function setDate($date) {
        if (is_int($date)) {
            $this->date = $date;
        } else {
            $this->date = strtotime($date);
        }
        $this->calcBeginAndEnd();
        $this->loadBelegung();
    }

    /**
     * Berechne die Zeitspanne, in der Veranstaltungen liegen müssen
     */
    private function calcBeginAndEnd() {
        $this->begin = strtotime("midnight", $this->date);
        $this->end = strtotime("tomorrow", $this->begin) - 1;
    }

    /**
     * FETCH ALL THE TERMINE!!!
     */
    private function loadBelegung() {

        /*
         * Lade alle Veranstaltungen die sich in dem vorher berechneten Bereich
         * liegen
         */
        $db = DBManager::get();
        $sql = "SELECT o.resource_id as id,
                       o.name, 
                       o.description, 
                       o.parent_id, 
                       a.*,
                       a.user_free_name as directname, 
                       s.VeranstaltungsNummer as nr, 
                       s.Name as sname, 
                       au.Nachname as dozent,
                       s.seminar_id as link
                FROM resources_objects o
                JOIN resources_assign a USING (resource_id)
                JOIN resources_categories c USING (category_id)
                JOIN resources_rooms_order ro USING (resource_id)
                LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                LEFT JOIN seminare s ON t.range_id = s.seminar_id
                LEFT JOIN seminar_user su ON (s.seminar_id = su.Seminar_id AND su.status = 'dozent')
                LEFT JOIN auth_user_md5 au ON (su.user_id = au.user_id)
                WHERE c.is_room = 1
                AND ro.user_id = :userid
                AND ro.checked = 1
                AND ((a.begin >= :begin AND a.begin <= :end)
                OR (a.end >= :begin AND a.end <= :end)
                OR (a.begin <= :begin AND a.repeat_end >= :end))
                ORDER BY ro.priority, a.begin";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":begin", $this->begin);
        $stmt->bindParam(":end", $this->end);
        $stmt->bindParam(":userid", $GLOBALS['user']->id);
        $stmt->execute();

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {

            /*
             * Wenn sich der Termin in einem Raum befindet, der noch nicht
             * gelistet ist, dann lege diesen Raum an
             */
            if (!$this->rooms[$result['id']]) {

                $room = new Room($result['id'], "{$result['name']}");
                $this->rooms[$result['id']] = $room;
                $parent_id = $result['parent_id'];

                /*
                 * Da wir im Vorhinein nicht wissen können, an welchen
                 * Ressourcen letzen Endes ein Raum hängt führen wir hier eine
                 * Rückwärtssuche aus, bis wir einen Knoten erreichen, den wir
                 * bereits kennen oder am root Knoten angelangt sind.
                 * 
                 * Da jede Ressource über eine ID verfügt, führen wir also eine
                 * Liste, in der wir festhalten, was bereits im Baum hängt.
                 * 
                 * Hier evtl Suche auf dem Baum implementieren dann sparst du
                 * dir Speicher du fauler Sack :)
                 */
                $sql = "SELECT o.parent_id, o.name, o.description FROM resources_objects o WHERE resource_id = ?";
                $stmt2 = $db->prepare($sql);
                $added = false;
                while (!$added) {
                    if (key_exists($parent_id, $this->rooms)) {
                        $this->rooms[$parent_id]->children[] = $room;
                        $added = true;
                    } else {
                        $stmt2->execute(array($parent_id));
                        $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                        $tmp = new Room($parent_id, "{$result2['name']} {$result2['description']}");
                        $tmp->children[] = $room;
                        $this->rooms[$parent_id] = $tmp;
                        $parent_id = $result2['parent_id'];
                        $room = $tmp;
                    }
                }
            }

            // multiday repead
            /*while ($result['repeat_end'] > $result['end'] && $result['repeat_quantity'] == 0) {
                echo "BENIS";die;
                $endOfDay = strtotime("tomorrow", $result['begin']) - 1;
                if ($result['repeat_end'] > $endOfDay) {
                    $result['end'] = $endOfDay;
                } else {
                    $result['end'] = $result['repeat_end'];
                }
                $this->rooms[$result['id']]->addTermin($result);
                $result['begin'] = $endOfDay + 1;
            }*/

            if ($result['repeat_end'] && $result['repeat_quantity'] != 0) {

                $i = 0;

                while ($result['end'] <= $result['repeat_end'] && ($result['repeat_quantity'] == -1 || $i < $result['repeat_quantity'])) {

                    // Füge dem Raum einen Termin hinzu
                    if ($result['begin'] >= $this->begin && $result['begin'] <= $this->end) {
                        $this->rooms[$result['id']]->addTermin($result, $this->begin, $this->end);
                    }

                    // Calculate next
                    if ($result['repeat_day_of_week']) {
                        $next = $result['repeat_interval'] == 1 ? '+1 week' : '+' . $result['repeat_interval'] . ' weeks';
                        $result['begin'] = strtotime($next, $result['begin']);
                        $result['end'] = strtotime($next, $result['end']);
                    } else if ($result['repeat_week_of_month']) {
                        // We need english names for numbers here.
                        switch ($result['repeat_interval']) {
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
                        $monthdistance = $result['repeat_interval'] == 1 ? '+1 month' :
                            '+' . $result['repeat_interval'] . ' months';

                        // Calculate next date according to given week number in month.
                        // (something like "second monday of 2016-12 14:00")
                        $next = $number . date(' l \o\f Y-m H:i:00', strtotime($monthdistance, $result['begin']));
                        $result['begin'] = strtotime($next);
                        $next = $number . date(' l \o\f Y-m H:i:59', strtotime($monthdistance, $result['end']));
                        $result['end'] = strtotime($next);
                    } else if ($result['repeat_day_of_month']) {
                        $next = $result['repeat_interval'] == 1 ? '+1 month' : '+' . $result['repeat_interval'] . ' months';
                        $result['begin'] = strtotime($next, $result['begin']);
                        $result['end'] = strtotime($next, $result['end']);
                    } else if ($result['repeat_month_of_year']) {
                        $next = $result['repeat_interval'] == 1 ? '+1 year' : '+' . $result['repeat_interval'] . ' years';
                        $result['begin'] = strtotime($next, $result['begin']);
                        $result['end'] = strtotime($next, $result['end']);
                    } else {
                        $next = $result['repeat_interval'] == 1 ? '+1 day' : '+' . $result['repeat_interval'] . ' days';
                        $result['begin'] = strtotime($next, $result['begin']);
                        $result['end'] = strtotime($next, $result['end']);
                    }

                    $i++;
                }
            } else {

                $this->rooms[$result['id']]->addTermin($result, $this->begin, $this->end);
            }
        }
    }

}

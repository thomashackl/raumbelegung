<?php

class IntelecBelegungsplan {

    public static function display($date, RoomUsageResourceObject $object) {

        $start = strtotime('last monday', strtotime($date));
        $end = $start + 7 * 24 * 60 * 60 - 1;

        $footertext = 'Dreizeiliges Bla Bla Bla, das sich alle 3-4 Monate ändert. Daher wäre es toll, wenn die Raumvergabe diesen Text in Stud.IP editieren könnte!?Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ...';

        // Parse start and end
        // Start table
        $html = '<table class="intelec_roomtable">';

        // Header
        $html .= '<thead>'
                . '<tr>'
                . '<td colspan="3">'
                . $object->name . ($object->description ? (' (' . $object->description . ')') : '') . '<br>'
                . $object->parent->getProperty('Adresse')
                . '</td>'
                . '<td colspan="2">'
                . 'Plätze: ' . $object->getProperty('Sitzplätze') . ($object->getProperty('Sitzplätze Ergänzung') ? '(' . $object->getProperty('Sitzplätze Ergänzung') . ')' : '') . '<br>'
                . 'Fläche: ' . $object->getProperty('Fläche')
                . '</td>'
                . '<td colspan="3">'
                . 'Zeitraum: ' . self::timeformat($start) . ' - ' . self::timeformat($end) . '<br>'
                . 'Stand: ' . self::timeformat(time())
                . '</td>'
                . '</tr>'
                . '</thead>';

        // Body
        $html .= '<tbody>';

        // Headline
        $html .= '<tr>'
                . '<td></td>'
                . '<td>Montag</td>'
                . '<td>Dienstag</td>'
                . '<td>Mittwoch</td>'
                . '<td>Donnerstag</td>'
                . '<td>Freitag</td>'
                . '<td></td>'
                . '<td>Samstag&thinsp;/&thinsp;Sonntag</td>'
                . '</tr>';

        $html .= '</tbody>';

        // Actual data
        for ($time = 8; $time <= 23; $time++) {
            $html .= '<tr>'
                    . '<td>' . $time . '</td>';

            // Now get actuall stuff
            for ($day = 0; $day < 5; $day++) {

                $assignment = self::getAssignement($object, $start, $day, $time);


                $html .= '<td>';
                if ($assignment) {
                $html .= $assignment['realname'];
                }
                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        // Footer
        $html .= '<tfoot>'
                . '<tr>'
                . '<td colspan="0">' . $footertext . '</td>'
                . '</tr>'
                . '</tfoot>';

        // End table
        $html .= '</table>';


        return $html;
    }

    private static function timeformat($stamp) {
        return strftime('%a. %d.%m.%y', $stamp);
    }

    private static function getAssignement($object, $start, $day, $hour) {
        $startstamp = $start + $day * 3600 * 24 + $hour * 3600;
        $endstamp = $startstamp + 3599;

        $sql = "SELECT *, COALESCE(s.Name, user_free_name) as realname, COUNT( * ) AS teilnehmer FROM resources_objects o
                    JOIN resources_assign a USING (resource_id)
                    LEFT JOIN termine t ON t.termin_id = a.assign_user_id
                    LEFT JOIN seminare s ON t.range_id = s.seminar_id
                    LEFT JOIN seminar_user u USING (seminar_id) 
                    LEFT JOIN auth_user_md5 au USING(user_id)
                    LEFT JOIN seminar_user u2 USING (seminar_id) 
                    WHERE o.resource_id = ? 
                    AND (u.status = 'dozent' OR u.status is null)
                    AND a.begin >= ? and a.begin < ?
                    GROUP BY termin_id";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($object->id, $startstamp, $endstamp));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

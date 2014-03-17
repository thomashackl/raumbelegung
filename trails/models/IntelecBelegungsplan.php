<?php

class IntelecBelegungsplan {

    public static function display($date, RoomUsageResourceObject $object) {

        $start = strtotime('last monday', strtotime($date));
        $end = $start + 7 * 24 * 60 * 60 - 1;

        $footertext = 'Dreizeiliges Bla Bla Bla, das sich alle 3-4 Monate ändert. Daher wäre es toll, wenn die Raumvergabe diesen Text in Stud.IP editieren könnte!?Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ...';

        // Parse start and end
        // Start table
        $html = '<table class="intelec_roomtable">';
        $html .= '  <colgroup>
    <col width="1%">
    <col width="16%">
    <col width="16%">
    <col width="16%">
    <col width="16%">
    <col width="16%">
    <col width="1%">
    <col width="16%">
  </colgroup>';

        // Header
        $html .= '<thead>'
                . '<tr>'
                . '<td colspan="3">'
                . '<span class="headline">'.$object->name . ($object->description ? (' (' . $object->description . ')') : '') . '</span><br>'
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
                . '<th>Montag</th>'
                . '<th>Dienstag</th>'
                . '<th>Mittwoch</th>'
                . '<th>Donnerstag</th>'
                . '<th>Freitag</th>'
                . '<td></td>'
                . '<th>Samstag&thinsp;/&thinsp;Sonntag</th>'
                . '</tr>';

        $html .= '</tbody>';

        $jump = array();
        // Actual data
        for ($time = 8; $time <= 23; $time++) {
            $html .= '<tr>'
                    . '<td>' . $time . '</td>';

            // Now get actuall stuff
            for ($day = 0; $day < 5; $day++) {

                // Check if jump is required
                if ($jump[$day]) {
                    $jump[$day] --;
                    continue;
                }

                $assignment = self::getAssignement($object, $start, $day, $time);

                if ($assignment) {
                    // Check how long the assignment runs
                    $runtime = ($assignment['end'] - $assignment['begin']) / 3600;

                    // Set jumper
                    $jump[$day] += $runtime - 1;

                    $html .= '<td rowspan="' . $runtime . '">';
                    $html .= '<div class="entry">'
                            . mb_strimwidth($assignment['VeranstaltungsNummer'] . ' ' . $assignment['realname'], 0, 40, "&hellip;")
                            . '<br>'
                            . $assignment['dozenten']
                            . '<br>'
                            . $assignment['teilnehmer'] . '</div>';
                } else {
                    $html .= '<td>';
                }
                $html .= '</td>';
            }

            $html .= '<td>' . $time . '</td>';

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

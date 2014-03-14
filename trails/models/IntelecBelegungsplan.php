<?php

class IntelecBelegungsplan {
    
    public static function display($date, RoomUsageResourceObject $object) {
        
        $footertext = 'Dreizeiliges Bla Bla Bla, das sich alle 3-4 Monate ändert. Daher wäre es toll, wenn die Raumvergabe diesen Text in Stud.IP editieren könnte!?Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ...';
        
        // Parse start and end
        
        // Start table
        $html = '<table class="intelec_roomtable">';
        
        // Header
        $html .= '<thead>'
                . '<tr>'
                . '<td colspan="3">'.$object->getProperty('Sitzplätze').'</td>'
                . '<td colspan="2"></td>'
                . '<td colspan="3"></td>'
                . '</tr>'
                . '</thead>';
        
        // Headline
        
        // Footer
        $html .= '<tfoot>'
                . '<tr>'
                . '<td>'.$footertext.'</td>'
                . '</tr>'
                . '</tfoot>';
        
        // End table
        $html .= '</table>';
        
        
        return $html;
    }
    
}
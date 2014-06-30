<?php

/**
 * Ein Raum
 */
class Room {

    public $id;
    public $name;
    public $termine = array();
    public $children = array();
    public $date;
    public $opening;
    public $close;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Rekursive Berechnung der Raumöffnungszeit anhand der Unterräume und
     * Termine
     * 
     * @return int Öffnungstimestamp
     */
    public function getOpening() {
        if (!$this->opening) {
            $this->opening = PHP_INT_MAX;
            foreach ($this->children as $child) {
                $this->opening = min(array($this->opening, $child->getOpening()));
            }
        }
        return $this->opening;
    }

    /**
     * Rekursive Berechnung der Raumöffnungszeit anhand der Unterräume und
     * Termine
     * 
     * @return int Öffnungstimestamp
     */
    public function getClose() {
        if (!$this->close) {
            $this->close = 0;
            foreach ($this->children as $child) {
                $this->close = max(array($this->close, $child->getClose()));
            }
        }
        return $this->close;
    }

    /**
     * Gibt einen Datumsstring zurück
     * 
     * @return string Datumsstring
     */
    public function getDate() {
        if ($this->getClose() == 0) {
            return "Keine Veranstaltungen";
        }
        return date('H:i', $this->getOpening()) . " - " . date('H:i', $this->getClose()) . " Uhr";
    }

    /**
     * Fügt einem Raum einen Termin hinzu
     * @param array SQL Result des Belegungsplans
     */
    public function addTermin($result) {
        
        // Finde einen passenden Namen
        $name = "Unbekannt";
        $name = $result['directname'] ? : $name;
        $name = $result['realname'] ? : $name;
        $name = $result['sname'] ? : $name;
        $name = $result['nr'] ? "{$result['nr']} {$name}" : $name;
        if ($result['link']) {
            $link = URLHelper::getLink('details.php', array('sem_id' => $result['link']));
            $name = "<a href='{$link}'>{$name}</a>";
        }
        
        // Dozenten hinzufügen
        $name = $result['dozent'] ? "{$name} ({$result['dozent']})" : $name;
        
        // Füge den Termin an und berechne die Öffnungszeiten des Raums neu
        $this->termine[$result['begin']] = new Termin($result['begin'], $result['end'], $name);
        ksort($this->termine);
        
        if (!$this->close) {
            $this->close = 0;
        }

        if (!$this->opening) {
            $this->opening = PHP_INT_MAX;
        }
        //$this->opening = $result['begin'];
        $this->opening = min(array($this->opening, $result['begin']));
        $this->close = max(array($this->close, $result['end']));
    }

}

?>

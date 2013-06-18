<?php
/**
 * Ein Termin
 */
class Termin {
    public $begin;
    public $end;
    public $name;
    public $display;
    
    public function __construct($start, $end, $name) {
        $this->begin = $start;
        $this->end = $end;
        $this->name = $name;
        $this->display = $this->date = date('H:i', $this->begin). " - ". date('H:i', $this->end). " ". $name;
    }
}

?>

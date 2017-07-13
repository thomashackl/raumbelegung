<?php
/**
 * Ein Termin
 */
class Termin {
    public $begin;
    public $end;
    public $name;
    public $display;
    public $info;
    
    public function __construct($start, $end, $name, $assign_id) {
        $this->begin = $start;
        $this->end = $end;
        $this->name = $name;
        $this->display = $this->date = date('H:i', $this->begin). " - ". date('H:i', $this->end). " ". $name;
        $i = ResourceAssignInfo::find($assign_id);
        if ($i) {
            $this->info = $i->info;
        } else {
            $this->info = '';
        }
    }
}

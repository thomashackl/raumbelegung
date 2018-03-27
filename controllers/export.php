<?php
require_once 'app/controllers/studip_controller.php';

class ExportController extends StudipController {

    /**
     * This method is called before every other action.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Lade das standard StudIP Layout
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action()
    {
        PageLayout::setTitle(dgettext('roomplanplugin', 'Export der Raumbelegungen'));
        $this->start = date('d.m.Y', strtotime('Monday next week'));
        $this->end = date('d.m.Y', strtotime('Sunday next week'));
        $this->tree = ResourceAssignExport::getResources();
    }

    public function do_action()
    {
        $resources = ResourceAssignExport::getResources();

        $start = strtotime(Request::get('start') . ' 00:00:00');
        $end = strtotime(Request::get('end') . ' 23:59:59');

        $times = [];
        for ($day = $start ; $day <= $end ; $day += 86400) {
            for ($hour = $day ; $hour < $day + 86400 ; $hour += 1800) {
                $times[date('d.m.Y', $day)][date('H:i', $hour)] = 0;
            }
        }

        $this->matrix = [[
            dgettext('roomplanplugin', 'Zähler'),
            'ID',
            dgettext('roomplanplugin', 'Gebäude'),
            dgettext('roomplanplugin', 'Raum'),
            dgettext('roomplanplugin', 'Tag'),
            '00:00',
            '00:30',
            '01:00',
            '01:30',
            '02:00',
            '02:30',
            '03:00',
            '03:30',
            '04:00',
            '04:30',
            '05:00',
            '05:30',
            '06:00',
            '06:30',
            '07:00',
            '07:30',
            '08:00',
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
            '18:00',
            '18:30',
            '19:00',
            '19:30',
            '20:00',
            '20:30',
            '21:00',
            '21:30',
            '22:00',
            '22:30',
            '23:00',
            '23:30'
        ]];

        $text = '';
        $counter = 1;
        foreach ($resources as $resource) {
            $roomtimes = $times;
            $assigns = new AssignEventList($start, $end, $resource['resource_id']);
            foreach ($assigns->events as $event) {
                for ($i = $event->begin; $i < $event->end; $i += 1800) {
                    $roomtimes[date('d.m.Y', $i)][date('H:i', $i)] = 1;
                }
            }

            foreach ($roomtimes as $day => $hours) {
                $this->matrix[] = array_merge([
                        $counter,
                        $resource['resource_id'],
                        $resources[$resource['parent_id']]['name'],
                        $resource['name'],
                        $day
                    ], $hours);
            }

            $counter++;
        }

        $this->set_content_type('text/csv');
        $this->response->add_header('Content-Disposition', 'attachment;filename=raumbelegungen-' .
            date('Y-m-d-H-i') . '.csv');
        $this->render_text(array_to_csv($this->matrix));
    }

}

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
        $start = strtotime(Request::get('start') . ' 00:00:00');
        $end = strtotime(Request::get('end') . ' 23:59:59');

        $this->set_content_type('text/csv');
        $this->response->add_header('Content-Disposition', 'attachment;filename=raumbelegungen-' .
            date('Y-m-d-H-i') . '.csv');
        $this->render_text(array_to_csv(ResourceAssignExport::buildAssignmentMatrix($start, $end)));
    }

}

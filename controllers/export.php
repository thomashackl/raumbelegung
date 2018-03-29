<?php

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
        $this->relocate('export/files');
    }

    public function files_action($folderId = '')
    {
        Navigation::activateItem('/calendar/raumbelegung/export');

        if (!$folderId) {
            $folder = Folder::findOneByRange_id('roomplanplugin');
        } else {
            $folder = Folder::find($folderId);
        }

        if (!$folder) {

            PageLayout::postInfo(dgettext('roomplanplugin', 'Es sind keine Dateien vorhanden.'));

        } else {

            $this->topFolder = $folder->getTypedFolder();

            if (!$this->topFolder->isVisible($GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }
        }
    }

    public function delete_action($file_id)
    {
        $file = File::find($file_id);
        if ($file->delete()) {
            PageLayout::postInfo(dgettext('roomplanplugin', 'Die Exportdatei wurde gelöscht.'));
        } else {
            PageLayout::postError(dgettext('roomplanplugin', 'Die Exportdatei konnte nicht gelöscht werden.'));
        }
        $this->relocate('export/files');
    }

    public function manual_action()
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

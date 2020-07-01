<?php

class ExportController extends StudipController {

    /**
     * This method is called before every other action.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Lade das standard StudIP Layout
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        Navigation::activateItem('/calendar/raumbelegung/export');
        PageLayout::setTitle($this->dispatcher->plugin->getDisplayName() . ' - ' .
            dgettext('roomplanplugin', 'Export der Raumbelegungen'));

        $this->createSidebar($action);

        PageLayout::addStylesheet($this->dispatcher->plugin->getPluginURL() . "/assets/style.css");
        PageLayout::addScript($this->dispatcher->plugin->getPluginURL() . "/assets/raumbelegung.js");
    }

    /**
     * Just redirect to file overview.
     */
    public function index_action()
    {
        $this->relocate('export/files');
    }

    /**
     * List automatically generated CSV export files for download.
     * @param string $folderId optional start folder ID.
     */
    public function files_action($folderId = '')
    {
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

    /**
     * Delete an export file.
     * @param $file_id the file to delete
     */
    public function delete_action($file_id)
    {
        $file = FileRef::find($file_id);
        if ($file->delete()) {
            PageLayout::postInfo(dgettext('roomplanplugin', 'Die Exportdatei wurde gelöscht.'));
        } else {
            PageLayout::postError(dgettext('roomplanplugin', 'Die Exportdatei konnte nicht gelöscht werden.'));
        }
        $this->relocate('export/files');
    }

    /**
     * Show settings for a manual export of room assignments into a CSV file.
     */
    public function manual_action()
    {
        PageLayout::setTitle(dgettext('roomplanplugin', 'Export der Raumbelegungen'));
        $this->start = date('d.m.Y', strtotime('today 00:00:00'));
        $this->end = date('d.m.Y', strtotime('today + 6 days 23:59:59'));
        $this->resources = ResourceAssignExport::getResources('', true);
        $this->selected = Config::get()->ROOMPLAN_CSV_EXPORT_ROOMS;
        $this->prefix = 'manual';
    }

    /**
     * Process manual export of room assignments, generating a CSV file for direct download.
     */
    public function do_action()
    {
        $start = strtotime(Request::get('start') . ' 00:00:00');
        $end = strtotime(Request::get('end') . ' 23:59:59');

        $this->set_content_type('text/csv');
        $this->response->add_header('Content-Disposition', 'attachment;filename=raumbelegungen-' .
            date('Y-m-d-H-i') . '.csv');
        $this->render_text(array_to_csv(
            ResourceAssignExport::buildAssignmentMatrix($start, $end, Request::getArray('selected'))));
    }

    /**
     * List all rooms, providing the possibility to (de-)select entries for export.
     */
    public function rooms_action()
    {
        $this->resources = ResourceAssignExport::getResources('', true);
        $this->selected = Config::get()->ROOMPLAN_CSV_EXPORT_ROOMS;
        $this->prefix = 'auto';
    }

    /**
     * Save room selection for CSV export.
     */
    public function save_rooms_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Config::get()->store('ROOMPLAN_CSV_EXPORT_ROOMS', Request::getArray('selected'))) {
            PageLayout::postSuccess(_('Die Daten wurden gespeichert.'));
        } else {
            PageLayout::postError(_('Die Daten konnten nicht gespeichert werden.'));
        }

        $this->relocate('export');
    }

    private function createSidebar($action)
    {
        $this->sidebar = Sidebar::get();

        $this->sidebar->setImage('sidebar/export-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(dgettext('roomplanplugin', 'Exportdateien'),
            $this->url_for('export/files'),
            Icon::create('files', 'clickable'))->setActive($action == 'files');
        $views->addLink(dgettext('roomplanplugin', 'Manuell exportieren'),
            $this->url_for('export/manual'),
            Icon::create('export', 'clickable'))->setActive($action == 'manual');
        $views->addLink(dgettext('roomplanplugin', 'Zu exportierende Räume'),
            $this->url_for('export/rooms'),
            Icon::create('export', 'clickable'))->setActive($action == 'rooms')->asDialog();
        $this->sidebar->addWidget($views);
    }

}

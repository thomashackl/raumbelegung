<?php
/**
 * Class AssignmentExportCronjob
 *
 * Cronjob for creating a CSV export file of room assignments.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Raumbelegung
 */

class AssignmentsExportCronjob extends CronJob {

    public static function getName() {
        return dgettext('roomplanplugin', 'Export der Raumbelegungen für Betriebstechnik');
    }

    public static function getDescription() {
        return dgettext('roomplanaplugin', 'Erzeugt einen CSV-Export aller Raumbelegungen '.
            'in einem Format, das von der Betriebstechnik weiterverarbeitet werden kann.');
    }

    public static function getParameters() {
        return [];
    }

    public function setUp() {

    }

    /**
     * Create CSV file.
     */
    public function execute($last_result, $parameters = array()) {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Provide room assignments for next week.
        $start = strtotime('today 00:00:00');
        $startDate = date('d.m.Y', $start);
        $end = strtotime('today + 6 days 23:59:59');
        $endDate = date('d.m.Y', $end);

        $matrix = ResourceAssignExport::buildAssignmentMatrix($start, $end);

        $filename = $GLOBALS['TMP_PATH'] . '/raumbelegungen-' . date('Y-m-d-H-i') . '.csv';

        $file = fopen($filename, 'w');
        fwrite($file, array_to_csv($matrix));
        fclose($file);

        $folder = AssignmentsExportFolder::findTopFolder('');

        if ($folder) {
            $fileObj = new File();
            $fileObj->user_id = $GLOBALS['user']->id;
            $fileObj->mime_type = 'text/csv';
            $fileObj->name = 'Raumbelegungen ' . $startDate . ' - ' . $endDate . ' (Stand ' . date('d.m.Y H:i') . ')';
            $fileObj->size = filesize($filename);
            $fileObj->storage = 'disk';
            $fileObj->store();
            $fileObj->connectWithDataFile($filename);

            // Remove all previous files.
            foreach ($folder->getFiles() as $file) {
                $file->delete();
            }

            // Create new file.
            if (!$fileref = $folder->createFile($fileObj)) {
                echo "\nERROR: Could not add export file to folder.\n";
            } else {
                $fileref->name = $startDate . ' - ' . $endDate;
                $fileref->description = date('d.m.Y H:i');
                $fileref->store();
            }

        } else {
            echo "\nERROR: Could not find or create target folder.\n";
        }

        unlink($filename);

    }

    public function tearDown() {

    }
}


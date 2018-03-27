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
        $mailto = Config::get()->RAUMBELEGUNG_EXPORT_CSV_MAILTO;

        if ($mailto) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

            // Provide room assignments for next week.
            $start = strtotime('Monday next week');
            $startDate = date('d.m.Y', $start);
            $end = strtotime('Sunday next week 23:59:59');
            $endDate = date('d.m.Y', $end);

            $matrix = ResourceAssignExport::buildAssignmentMatrix($start, $end);

            $filename = 'raumbelegungen-' . date('Y-m-d-H-i') . '.csv';

            $file = fopen($GLOBALS['TMP_PATH'] . '/' . $filename, 'w');
            fwrite($file, array_to_csv($matrix));
            fclose($file);

            $message = "Hallo,\n\nanbei der aktuelle Export aller Raumbelegungen für den Zeitraum vom " .
                $startDate . " bis " . $endDate . ".\n\nViele Grüße,\nIhr Stud.IP";

            $mail = new StudipMail();
            $mail->setSubject('Raumbelegungen ' . $startDate . ' - ' . date('d.m.Y', $endDate))
                ->setReplyToEmail($GLOBALS['UNI_CONTACT'])
                ->setBodyText($message)
                ->setSenderEmail($GLOBALS['UNI_CONTACT'])
                ->addRecipient($mailto)
                ->addFileAttachment($GLOBALS['TMP_PATH'] . '/' . $filename, $filename, 'text/csv');

            if (!$mail->send()) {
                echo "\nERROR: Cannot send mail.\n";
            }
        } else {
            echo "\nERROR: No target mail address set.\n";
        }
    }

    public function tearDown() {

    }
}


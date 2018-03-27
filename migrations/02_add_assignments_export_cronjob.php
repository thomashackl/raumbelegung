<?php

require_once(__DIR__.'/../AssignmentsExportCronjob.php');

class addAssignmentsExportCronjob extends Migration {

    function up() {
        AssignmentsExportCronjob::register()->schedulePeriodic(41, 0)->activate();

        Config::get()->create('RAUMBELEGUNG_EXPORT_CSV_MAILTO', array(
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'raumbelegungsplugin',
            'description' =>
                _('An welche E-Mailadresse soll der automatische CSV-Export der Raumbelegungen verschickt werden?')
        ));
    }

    function down() {
        Config::get()->delete('RAUMBELEGUNG_EXPORT_CSV_MAILTO');
        AssignmentsExportCronjob::unregister();
    }

}

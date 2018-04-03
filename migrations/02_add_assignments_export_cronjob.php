<?php

require_once(__DIR__.'/../AssignmentsExportCronjob.php');
require_once(__DIR__.'/../models/ResourceAssignExport.php');

class addAssignmentsExportCronjob extends Migration {

    function up() {
        AssignmentsExportCronjob::register()->schedulePeriodic(41, 0)->activate();

        Config::get()->create('ROOMPLAN_CSV_EXPORT_ROOMS', array(
            'value'       => json_encode(
                                 array_map(function ($e) { return $e['resource_id']; },
                                 ResourceAssignExport::getResources())
                             ),
            'is_default'  => '1',
            'type'        => 'array',
            'range'       => 'global',
            'section'     => 'Raumbelegung',
            'description' => 'Räume, die bei CSV-Export der Raumbelegungen berücksichtigt werden sollen',
        ));
    }

    function down() {
        Config::get()->delete('ROOMPLAN_CSV_EXPORT_ROOMS');

        AssignmentsExportCronjob::unregister();
    }

}

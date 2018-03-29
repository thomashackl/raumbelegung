<?php

require_once(__DIR__.'/../AssignmentsExportCronjob.php');

class addAssignmentsExportCronjob extends Migration {

    function up() {
        AssignmentsExportCronjob::register()->schedulePeriodic(41, 0)->activate();
    }

    function down() {
        AssignmentsExportCronjob::unregister();
    }

}

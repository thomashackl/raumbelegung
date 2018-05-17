<?php
class OpeningTimes extends Migration {

    function up() {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `resources_opening_times` (
            `resource_id` CHAR(32) NOT NULL REFERENCES `resources_objects`.`resource_id`,
            `weekdays_start` TIME NULL DEFAULT NULL,
            `weekdays_end` TIME NULL DEFAULT NULL,
            `saturday_start` TIME NULL DEFAULT NULL,
            `saturday_end` TIME NULL DEFAULT NULL,
            `sunday_start` TIME NULL DEFAULT NULL,
            `sunday_end` TIME NULL DEFAULT NULL,
            PRIMARY KEY (`resource_id`))
            ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `resources_objects_opening_times` (
            `resource_id` CHAR(32) NOT NULL REFERENCES `resources_objects`.`resource_id`,
            PRIMARY KEY (`resource_id`))
            ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }

    function down() {
        DBManager::get()->execute("DROP TABLE `resources_opening_times`");
        DBManager::get()->execute("DROP TABLE `resources_objects_opening_times`");
    }

}

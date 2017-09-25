<?php
class ResourcesAssignInfo extends Migration {

    function up() {
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `resources_assign_info` (
            `assign_id` CHAR(32) NOT NULL REFERENCES `resources_assign`.`assign_id`,
            `user_id` CHAR(32) NOT NULL REFERENCES `auth_user_md5`.`user_id`,
            `info` TEXT NOT NULL,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`assign_id`))
            ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }

    function down() {
        DBManager::get()->execute("DROP TABLE `resources_assign_info`");
    }

}

<?php
/**
 * ResourceAssignExport.php
 *
 * Helper class which builds an array of all assignments for selected room(s)
 * and time span.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class ResourceAssignExport {

    public static function getResources($parentId = '0')
    {
        $resources = [];
        $level = DBManager::get()->fetchAll("SELECT `resource_id`, `category_id`, `name`, `description`, `parent_id`
            FROM `resources_objects`
            WHERE `parent_id` = :parent
            ORDER BY `name`", ['parent' => $parentId]);

        foreach ($level as $one) {
            $resources[$one['resource_id']] = $one;
            $resources = array_merge($resources, self::getResources($one['resource_id']));
        }

        return $resources;
    }

}

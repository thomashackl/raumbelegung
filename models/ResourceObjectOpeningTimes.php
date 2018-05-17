<?php
/**
 * ResourceObjectOpeningTimes.php
 *
 * Model class for table resources_objects_opening_times which contains
 * the information which room uses building opening times instead of
 * only considering regular booked times.
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
class ResourceObjectOpeningTimes extends SimpleORMap {

    public static function configure($config=array()) {
        $config['db_table'] = 'resources_objects_opening_times';

        $config['belongs_to']['resource_object'] = [
            'class_name' => 'RoomUsageResourceObject',
            'foreign_key' => 'resource_id'
        ];
        $config['has_one']['openingtimes'] = [
            'class_name' => 'ResourceOpeningTimes',
            'assoc_func' => 'findTimesByResource_id'
        ];

        parent::configure($config);
    }

}

<?php
/**
 * ResourceOpeningTimes.php
 *
 * Model class for table resources_opening times which contains
 * information about opening times of special resources.
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
class ResourceOpeningTimes extends SimpleORMap {

    public static function configure($config=array()) {
        $config['db_table'] = 'resources_opening_times';
        parent::configure($config);
    }

    public static function findTimesByResource_id($resource_id)
    {
        return self::find(Room::find($resource_id)->findBuilding()->id);
    }

}

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

    /**
     * Generates a "matrix view" of all rooms and their assignments for the given timespan.
     * The result shows for each day and half hour if the room is occupied or not.
     * @param $start
     * @param $end
     */
    public static function buildAssignmentMatrix($start, $end)
    {
        $times = [];
        for ($day = $start ; $day <= $end ; $day += 86400) {
            for ($hour = $day ; $hour < $day + 86400 ; $hour += 1800) {
                $times[date('d.m.Y', $day)][date('H:i', $hour)] = 0;
            }
        }

        $resources = self::getResources();

        $matrix = [[
            dgettext('roomplanplugin', 'Zähler'),
            'ID',
            dgettext('roomplanplugin', 'Gebäude'),
            dgettext('roomplanplugin', 'Raum'),
            dgettext('roomplanplugin', 'Tag'),
            '00:00',
            '00:30',
            '01:00',
            '01:30',
            '02:00',
            '02:30',
            '03:00',
            '03:30',
            '04:00',
            '04:30',
            '05:00',
            '05:30',
            '06:00',
            '06:30',
            '07:00',
            '07:30',
            '08:00',
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
            '18:00',
            '18:30',
            '19:00',
            '19:30',
            '20:00',
            '20:30',
            '21:00',
            '21:30',
            '22:00',
            '22:30',
            '23:00',
            '23:30'
        ]];

        $text = '';
        $counter = 1;
        foreach ($resources as $resource) {
            if ($resource['parent_id'] != '0') {
                $roomtimes = $times;
                $assigns = new AssignEventList($start, $end, $resource['resource_id']);
                if ($assign->events) {
                    foreach ($assigns->events as $event) {
                        for ($i = $event->begin; $i < $event->end; $i += 1800) {
                            $roomtimes[date('d.m.Y', $i)][date('H:i', $i)] = 1;
                        }
                    }
                }

                foreach ($roomtimes as $day => $hours) {
                    $matrix[] = array_merge([
                        $counter,
                        $resource['resource_id'],
                        $resources[$resource['parent_id']]['name'],
                        $resource['name'],
                        $day
                    ], $hours);
                }

                $counter++;
            }
        }

        return $matrix;
    }

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

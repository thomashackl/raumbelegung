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
     * @param int $start start time for exporting assignments
     * @param int $end end time for exporting assignments
     * @param array $ids use only these resource IDs for export
     */
    public static function buildAssignmentMatrix($start, $end, $ids = [])
    {
        $times = [];
        for ($day = $start ; $day <= $end ; $day += 86400) {
            for ($hour = $day ; $hour < $day + 86400 ; $hour += 1800) {
                $times[date('d.m.Y', $day)][date('H:i', $hour)] = 0;
            }
        }

        $resources = self::getResources();
        $selected = $ids ?: Config::get()->ROOMPLAN_CSV_EXPORT_ROOMS;

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

        $counter = 1;
        foreach ($selected as $id) {
            if ($resources[$id]['parent_id'] != '0') {
                $roomtimes = $times;
                $assigns = new AssignEventList($start, $end, $id);
                if ($assigns->events) {
                    foreach ($assigns->events as $event) {

                        // Round begin down to nearest half hour if necessary.
                        $newBegin = $event->begin;
                        $beginMinute = date('i', $newBegin);
                        if ($beginMinute != 0 && $beginMinute != 30) {
                            $newBegin = $beginMinute < 30 ?
                                strtotime(date('d.m.Y H:00', $event->begin)) :
                                strtotime(date('d.m.Y H:30', $event->begin));
                        }

                        // Round end up to nearest half hour if necessary.
                        $newEnd = $event->end;
                        $endMinute = date('i', $end);
                        if ($endMinute != 0 && $endMinute != 59 && $endMinute != 30) {
                            $newEnd = $endMinute < 30 ?
                                strtotime(date('d.m.Y H:30', $event->end)) :
                                strtotime(date('d.m.Y H:59', $event->end));
                        }

                        for ($i = $newBegin; $i < $newEnd; $i += 1800) {
                            $roomtimes[date('d.m.Y', $i)][date('H:i', $i)] = 1;
                        }
                    }
                }

                foreach ($roomtimes as $day => $hours) {
                    $matrix[] = array_merge([
                        $counter,
                        $id,
                        $resources[$resources[$id]['parent_id']]['name'],
                        $resources[$id]['name'],
                        $day
                    ], $hours);
                }

                $counter++;
            }
        }

        return $matrix;
    }

    /**
     * Gets all resources in order.
     *
     * @param string $parentId resource ID to start from, default at root ("0")
     * @param bool $preserve_hierarchy keep parent/child relations or just return entries as flat structure?
     * @return array
     */
    public static function getResources($parentId = '0', $preserve_hierarchy = false)
    {
        $resources = [];
        $level = DBManager::get()->fetchAll("SELECT `resource_id`, `category_id`, `name`, `description`, `parent_id`
            FROM `resources_objects`
            WHERE `parent_id` = :parent
            ORDER BY `name`", ['parent' => $parentId]);

        foreach ($level as $one) {
            $resources[$one['resource_id']] = $one;

            if ($preserve_hierarchy) {
                $resources[$one['resource_id']]['children'] = self::getResources($one['resource_id']);
            } else {
                $resources = array_merge($resources, self::getResources($one['resource_id']));
            }
        }

        return $resources;
    }

}

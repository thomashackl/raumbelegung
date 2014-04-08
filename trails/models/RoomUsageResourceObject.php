<?php

/**
 * ResourceObjects.php
 * model class for table ResourceObjects
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */
class RoomUsageResourceObject extends SimpleORMap {

    public function __construct($id = null) {
        $this->db_table = 'resources_objects';
        $this->belongs_to['parent'] = array(
            'class_name' => 'RoomUsageResourceObject',
            'foreign_key' => 'parent_id'
        );
        $this->has_many['children'] = array(
            'class_name' => 'RoomUsageResourceObject',
            'assoc_foreign_key' => 'parent_id'
        );
        $this->has_many['prop_values'] = array(
            'class_name' => 'RoomUsageResourceObjectProperty'
        );
        $this->additional_fields['order'] = true;
        $this->additional_fields['checked'] = true;
        $this->additional_fields['prop'] = true;
        parent::__construct($id);
    }

    public function getOrder() {
        $room_order = current(ResourceRoomOrder::findBySQL('resource_id = ? AND user_id = ?', array($this->resource_id, $GLOBALS['user']->id)));
        if (!$room_order) {
            $room_order = ResourceRoomOrder::create(
                            array(
                                'resource_id' => $this->resource_id,
                                'user_id' => $GLOBALS['user']->id,
                                'checked' => 1,
                                'priority' => 0
                            )
            );
        }
        return $room_order;
    }
    
    public function getChecked() {
        return $this->order->checked ? "checked": "";
    }

    public static function getAll() {
        // Fetch the top layer rooms
        $buildings = current(RoomUsageResourceCategory::findByName('Gebäude'))->objects;
        return $buildings;
    }
    
    public function getFilteredChildren() {
        return $this->children;
    }

    private static function filter(SimpleORMapCollection &$collection) {
        $collection->filter(function ($object) {
            return $object->order->checked;
        });
    }

    private static function compare($a, $b) {
        return $a->order->priority >= $b->order->priority;
    }

    public function getProp() {
        return SimpleORMapCollection::createFromArray(RoomUsageResourceProperty::findBySQL('1'));
    }

    public function getProperty($name, $type = null) {

        // Fetch id of prop array
        if ($type) {
            $props = $this->prop->findBy('name', $name);
            $prop = $props->findOneBy('type', $name);
        } else {
            $prop = $this->prop->findOneBy('name', $name);
        }

        // If we got no property we already know what is gonna happen
        if (!$prop) {
            return null;
        }

        // Now find the value and return
        $value = $this->prop_values->findOneBy('property_id', $prop->id);
        return $value->state;
    }

}

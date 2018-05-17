<?php

/**
 * RoomUsageResourceObject.phpbject.php
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

    protected static function configure($config = array()) {
        $config['db_table'] = 'resources_objects';

        $config['belongs_to']['parent'] = array(
            'class_name' => 'RoomUsageResourceObject',
            'foreign_key' => 'parent_id'
        );

        $config['has_many']['children'] = array(
            'class_name' => 'RoomUsageResourceObject',
            'assoc_foreign_key' => 'parent_id'
        );
        $config['has_many']['prop_values'] = array(
            'class_name' => 'RoomUsageResourceObjectProperty'
        );
        $config['belongs_to']['category'] = array(
            'class_name' => 'RoomUsageResourceCategory',
            'foreign_key' => 'category_id'
        );
        $config['has_one']['opening_times'] = array(
            'class_name' => 'ResourceOpeningTimes',
            'foreign_key' => 'resource_id'
        );

        $config['additional_fields']['order'] = true;
        $config['additional_fields']['checked'] = true;
        $config['additional_fields']['priority'] = true;
        $config['additional_fields']['filteredchildren'] = true;
        $config['additional_fields']['prop'] = true;

        parent::configure($config);
    }

    public function getOrder() {
        $room_order = current(ResourceRoomOrder::findBySQL('resource_id = ? AND user_id = ?', array($this->resource_id, $GLOBALS['user']->id)));
        if (!$room_order) {
            $room_order = ResourceRoomOrder::create(
                            array(
                                'resource_id' => $this->resource_id,
                                'user_id' => $GLOBALS['user']->id,
                                'checked' => 0,
                                'priority' => 99999
                            )
            );
        }
        return $room_order;
    }

    public function getPriority() {
        return $this->order->priority;
    }

    public function getChecked() {
        return $this->order->checked ? "checked": "";
    }

    public static function getAll() {
        // Fetch the top layer rooms
        $buildings = current(RoomUsageResourceCategory::findByName('Gebäude'))->objects;
        return $buildings->orderBy('priority');
    }

    public static function getFiltered() {
        $buildings = current(RoomUsageResourceCategory::findByName('Gebäude'))->objects;
        return self::filter($buildings->orderBy('priority'));
    }

    public function getFilteredChildren() {
        return self::filter($this->children->orderBy('priority'));
    }

    private static function filter($collection) {
        return $collection->filter(function ($object) {
            return $object->order->checked;
        });
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

    public static function findBuilding($resource_id, $category_id = '')
    {
        if (!$category_id) {
            $category_id = RoomUsageResourceCategory::findOneByName('Gebäude')->id;
        }

        $resource = self::find($resource_id);
        if ($resource->category_id == $category_id) {
            return $resource;
        } else {
            return self::findBuilding($resource->parent_id, $category_id);
        }
    }

}

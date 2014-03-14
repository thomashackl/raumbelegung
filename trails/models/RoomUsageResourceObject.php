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
        $this->additional_fields['prop'] = true;
        parent::__construct($id);
    }

    public function getProp() {
        return SimpleORMapCollection::createFromArray(RoomUsageResourceProperty::findBySQL('1'));
    }

    public function getProperty($name) {

        // Fetch id of prop array
        $prop = $this->prop->findOneBy('name', $name);
        if (!$prop) {
            return null;
        }

        // Now find the value and return
        $value = $this->prop_values->findOneBy('property_id', $prop->id);
        return $value->state;
    }

}

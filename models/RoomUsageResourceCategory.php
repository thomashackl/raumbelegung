<?php
/**
 * ResourceCategorie.php
 * model class for table ResourceCategorie
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

class RoomUsageResourceCategory extends SimpleORMap {

    protected static function configure($config = array()) {
        $config['db_table'] = 'resources_categories';

        $config['has_many']['objects'] = array(
            'class_name' => 'RoomUsageResourceObject',
            'foreign_key' => 'category_id',
            'assoc_foreign_key' => 'category_id'
        );

        parent::configure($config);
    }

}

<?php
/**
 * ResourceSearch.class.php - Class of type SearchType used for searches with QuickSearch
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

/**
 * Class of type SearchType used for searches with QuickSearch
 * (lib/classes/QuickSearch.class.php). You can search with a sql-syntax in the
 * database. You just need to give in a query like for a PDB-prepare statement
 * and at least the variable ":input" in the query (the :input will be replaced
 * with the input of the QuickSearch userinput.
 *  [code]
 *  $search = new SQLSearch("username");
 *  [/code]
 *
 * @author Thomas Hackl
 *
 */

class ResourceSearch extends SQLSearch
{

    public $search;

    /**
     *
     * @param string $search
     *
     * @return void
     */
    public function __construct()
    {
        $this->avatarLike = $this->search = 'resource_id';
        $this->sql = $this->getSQL();
    }


    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        return dgettext('roomplanplugin', 'Raum suchen');
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL()
    {
        $this->extendedLayout = true;
        return "SELECT DISTINCT `resource_id`, `name` " .
            "FROM `resources_objects` " .
            "WHERE `name` LIKE :input " .
            "ORDER BY name ASC";
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath()
    {
        return __file__;
    }
}

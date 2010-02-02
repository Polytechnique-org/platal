<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

// {{{ class DirEnum
/** This class stores all data for the different kinds of fields.
 * It is only a dispatcher for the various DirEnum_XXX classes.
 */
class DirEnum
{
    /** Name of availables Enumerations
     * Each of these consts contains the basename of the class (its full name
     * being DE_$basename).
     */
    const BINETS         = 'binets';
    const SECTIONS       = 'sections';
    const SCHOOLS        = 'schools';
    const DEGREES        = 'degrees';
    const STUDIESDOMAINS = 'studiesdomains';
    const NATIONALITIES  = 'nationalities';
    const COUNTRIES      = 'countries';
    const GROUPESX       = 'groupesx';
    const SECTORS        = 'sectors';
    const NETWORKS       = 'networking';
    const ADMINAREAS     = 'adminareas';

    static private $enumerations = array();

    static private function init($type)
    {
        $cls = "DE_" . ucfirst($type);
        self::$enumerations[$type] = new $cls();
    }

    /** Retrieves all options for a given type
     * @param $type Type of enum for which options are requested
     * @return XorgDbIterator over the results
     * TODO : Find a way to get either an array, or the adequate PlIterator
     */
    static public function getOptions()
    {
        $args = func_get_args();
        $type = array_shift($args);
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        return call_user_func_array(array($obj, 'getOptions'), $args);
    }

    /** Retrieves a list of IDs for a given type
     * @param $type Type of enum for which IDs are requested
     * @param $text Text to search in enum valuees
     * @param $mode Mode of search for those IDs (prefix/suffix/infix)
     */
    static public function getIDs()
    {
        $args = func_get_args();
        $type = array_shift($args);
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        return call_user_func_array(array($obj, 'getIDs'), $args);
    }
}
// }}}

// {{{ class DirEnumeration
abstract class DirEnumeration
{
    /** Modes for LIKE searches
     */
    const MODE_EXACT    = 0x000;
    const MODE_PREFIX   = 0x001;
    const MODE_SUFFIX   = 0x002;
    const MODE_CONTAINS = 0x003;

    /** An internal array of ID => optionTxt
     */
    protected $options;

    /** Description of the MySQL storage of the fields
     */
    protected $idfield  = 'id';
    protected $valfield = 'text';
    protected $from;
    protected $join = '';
    protected $where = '';

    public function __construct() {
        $this->loadOptions();
    }

    public function getOptions()
    {
        return $this->options;
    }

    /** Retrieves possible IDs for given text
     * @param $text Text to search for IDs
     * @param $mode Mode of search (PREFIX, SUFFIX, CONTAINS)
     * @return An array of matching IDs ; if empty, input should be considered invalid
     */
    public function getIDs($text, $mode)
    {
        if ($mode == self::MODE_EXACT) {
            $options = $this->getOptions();
            return array_keys($options, $text);
        } else {
            if ($this->where == null) {
                $where = 'WHERE ';
            } else {
                $where = $this->where . ' AND ';
            }
            return XDB::fetchColumn('SELECT ' . $this->idfield . '
                                       FROM ' . $this->from . '
                                            ' . $this->join . '
                                            ' . $where . $this->valfield . self::makeSqlConcat($text, $mode) . '
                                   GROUP BY ' . $this->idfield);
        }
    }

    static protected function makeSqlConcat($text, $mode)
    {
        if ($mode == self::MODE_EXACT) {
            return ' = ' . XDB::format('{?}', $text);
        }
        if (($mode & self::MODE_CONTAINS) == self::MODE_PREFIX) {
            $right = XDB::format('CONCAT({?}, \'%\')', $text);
        } else if (($mode & self::MODE_CONTAINS) == self::MODE_SUFFIX) {
            $right = XDB::format('CONCAT(\'%\', {?})', $text);
        } else {
            $right = XDB::format('CONCAT(\'%\', {?}, \'%\')', $text);
        }
        return ' LIKE ' . $right;
    }

    /** The function used to load options
     */
    protected function loadOptions()
    {
        $this->options = XDB::iterator('SELECT ' . $this->valfield . ' AS field,
                                               ' . $this->idfield . ' AS id
                                          FROM ' . $this->from . '
                                               ' . $this->join . '
                                               ' . $this->where . '
                                      GROUP BY ' . $this->valfield . '
                                      ORDER BY ' . $this->valfield);
    }
}
// }}}

// {{{ class DE_Binets
class DE_Binets extends DirEnumeration
{
    protected $from = 'binets_def';
}
// }}}

// {{{ class DE_Sections
class DE_Sections extends DirEnumeration
{
    protected $from = 'sections';
}
// }}}

// {{{ class DE_Schools
class DE_Schools extends DirEnumeration
{
    protected $valfield = 'name';
    protected $from = 'profile_education_enum';
}
// }}}

// {{{ class DE_Degrees
class DE_Degrees extends DirEnumeration
{
    protected $suboptions = array();

    protected function loadOptions()
    {
        $res = XDB::query('SELECT ped.eduid, ped.degreeid, pede.degree
                             FROM profile_education_enum AS pee
                        LEFT JOIN profile_education_degree AS ped ON (pee.id = ped.eduid)
                        LEFT JOIN profile_education_degree_enum AS pede ON (ped.degreeid = pede.id)
                         ORDER BY pede.degree');
        foreach($res->fetchAllRow() as $row) {
            list($eduid, $degreeid, $name) = $row;
            $this->options[$degreeid] = array('id' => $degreeid, 'field' => $name);
            if (!array_key_exists($eduid, $this->suboptions)) {
                $this->suboptions[$eduid] = array();
            }
            $this->suboptions[$eduid][] = array('id' => $degreeid, 'field' => $name);
        }
    }

    public function getOptions($eduid = null)
    {
        if ($eduid == null) {
            return PlIteratorUtils::fromArray($this->options, 1, true);
        }
        if (array_key_exists($eduid, $this->suboptions)) {
            return PlIteratorUtils::fromArray($this->suboptions[$eduid], 1, true);
        } else {
            return array();
        }
    }

    public function getIDs($text, $mode, $eduid = null)
    {
        if ($eduid == null) {
            return XDB::fetchColumn('SELECT id
                                       FROM profile_education_degree_enum
                                       WHERE degree ' . self::makeSqlConcat($text, $mode));
        } else {
            return XDB::fetchColumn('SELECT pede.id
                                       FROM profile_education_degree AS ped
                                  LEFT JOIN profile_education_degree_enum AS pede ON (ped.degreeid = pede.id)
                                      WHERE ped.eduid = {?} AND pede.degree ' . self::makeSqlConcat($text, $mode), $eduid);
        }
    }
}
// }}}

// {{{ class DE_StudiesSector
class DE_StudiesSector extends DirEnumeration
{
    protected $valfield = 'field';
    protected $from = 'profile_education_field_enum';
}
// }}}

// {{{ class DE_Nationalities
class DE_Nationalities extends DirEnumeration
{
    protected $idfield  = 'iso_3166_1_a2';
    protected $valfield = 'nationalityFR';
    protected $from     = 'geoloc_countries AS gc';
    protected $join     = 'INNER JOIN profiles AS p ON (gc.iso_3166_1_a2 IN (p.nationality1, p.nationality2, p.nationality3))';
}
// }}}

// {{{ class DE_Countries
class DE_Countries extends DirEnumeration
{
    protected $idfield  = 'iso_3166_1_a2';
    protected $valfield = 'countryFR';
    protected $from     = 'geoloc_countries';
}
// }}}

// {{{ class DE_AdminAreas
class DE_AdminAreas extends DirEnumeration
{
    protected $suboptions = array();

    protected function loadOptions()
    {
        $res = XDB::query('SELECT id, name AS field, country
                             FROM geoloc_administrativeareas
                         GROUP BY name
                         ORDER BY name');
        foreach($res->fetchAllRow() as $row) {
            list($id, $field, $country) = $row;
            $this->options[] = array('id' => $id, 'field' => $field);
            if (!array_key_exists($country, $this->suboptions)) {
                $this->suboptions[$country] = array();
            }
            $this->suboptions[$country][] = array('id' => $id, 'field' => $field);
        }
    }

    public function getOptions($country = null)
    {
        if ($country == null) {
            return PlIteratorUtils::fromArray($this->options, 1, true);
        }
        if (array_key_exists($country, $this->suboptions)) {
            return PlIteratorUtils::fromArray($this->suboptions[$country], 1, true);
        } else {
            return array();
        }
    }

    public function getIDs($text, $mode, $country = null)
    {
        if ($country == null) {
            return XDB::fetchColumn('SELECT id
                                       FROM geoloc_administrativeareas
                                       WHERE name ' . self::makeSqlConcat($text, $mode));
        } else {
            return XDB::fetchColumn('SELECT id
                                       FROM geoloc_administrativeareas
                                      WHERE country = {?} AND name' . self::makeSqlConcat($text, $mode), $country);
        }
    }
}
// }}}

// {{{ class DE_GroupesX
class DE_GroupesX extends DirEnumeration
{
    protected $valfield = 'nom';
    protected $from     = '#groupex#.asso';
    protected $where    = 'WHERE (cat = \'GroupesX\' OR cat = \'Institutions\') AND pub = \'public\'';
}
// }}}

// {{{ class DE_Sectors
class DE_Sectors extends DirEnumeration
{
    protected $valfield = 'name';
    protected $from     = 'profile_job_sector_enum';
}
// }}}

// {{{ class DE_Networking
class DE_Networking extends DirEnumeration
{
    protected $idfield  = 'network_type';
    protected $valfield = 'name';
    protected $from     = 'profile_networking_enum';
}
// }}}
?>

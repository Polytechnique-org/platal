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
    const NAMETYPES      = 'nametypes';

    const BINETS         = 'binets';
    const GROUPESX       = 'groupesx';
    const SECTIONS       = 'sections';

    const EDUSCHOOLS     = 'educationschools';
    const EDUDEGREES     = 'educationdegrees';
    const EDUFIELDS      = 'educationfields';

    const NATIONALITIES  = 'nationalities';
    const COUNTRIES      = 'countries';
    const ADMINAREAS     = 'adminareas';
    const LOCALITIES     = 'localities';

    const COMPANIES      = 'companies';
    const SECTORS        = 'sectors';
    const JOBDESCRIPTION = 'jobdescription';

    const NETWORKS       = 'networking';

    static private $enumerations = array();

    static private function init($type)
    {
        $cls = "DE_" . ucfirst($type);
        self::$enumerations[$type] = new $cls();
    }

    /** Retrieves all options for a given type
     * @param $type Type of enum for which options are requested
     * @return XorgDbIterator over the results
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

    /** Retrieves all options for a given type
     * @param $type Type of enum for which options are requested
     * @return Array of the results the results
     */
    static public function getOptionsArray()
    {
        $args = func_get_args();
        $type = array_shift($args);
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        return call_user_func_array(array($obj, 'getOptionsArray'), $args);
    }

    /** Retrieves all options with number of profiles for autocompletion
     * @param $type Type of enum for which options are requested
     * @param $text Text to autocomplete
     * @return XorgDbIterator over the results
     */
    static public function getAutoComplete()
    {
        $args = func_get_args();
        $type = array_shift($args);
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        return call_user_func_array(array($obj, 'getAutoComplete'), $args);
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
    /** An internal array of ID => optionTxt
     */
    protected $options = null;

    /** Description of the MySQL storage of the fields
     */
    protected $idfield  = 'id';
    protected $valfield = 'text';
    protected $valfield2 = null;
    protected $from;
    protected $join = '';
    protected $where = '';

    /** Fields for autocompletion
     */
    protected $ac_join = ''; // Additional joins
    protected $ac_where = null; // Additional where
    protected $ac_beginwith = true; // Whether to search for 'x%' or for '%x%'
    protected $ac_unique; // Which field is to be taken as unique
    protected $ac_distinct = true; // Whether we want to keep only distinct valfield value
    protected $ac_withid = true; // Do we want to fetch id too ?

    protected function _fetchOptions()
    {
        if (is_null($this->options)) {
            $this->loadOptions();
        }
    }

    public function getOptions()
    {
        $this->_fetchOptions();
        return $this->options;
    }

    public function getOptionsArray()
    {
        $this->_fetchOptions();
        $options = array();
        while ($row = $this->options->next()) {
            $options[$row['id']] = $row['field'];
        }
        return $options;
    }

    // {{{ function getIDs
    /** Retrieves possible IDs for given text
     * @param $text Text to search for IDs
     * @param $mode Mode of search (PREFIX, SUFFIX, CONTAINS)
     * @return An array of matching IDs ; if empty, input should be considered invalid
     */
    public function getIDs($text, $mode)
    {
        if ($mode == XDB::WILDCARD_EXACT) {
            $options = $this->getOptions();
            return array_keys($options, $text);
        } else {
            if ($this->where == null) {
                $where = 'WHERE ';
            } else {
                $where = $this->where . ' AND ';
            }
            $conds = array();
            $conds[] = $this->valfield . XDB::formatWildcards($mode, $text);
            if ($this->valfield2 != null) {
                $conds[] = $this->valfield2 . XDB::formatWildcards($mode, $text);
            }
            $where .= '(' . implode(' OR ', $conds) . ')';

            return XDB::fetchColumn('SELECT ' . $this->idfield . '
                                       FROM ' . $this->from . '
                                            ' . $this->join . '
                                            ' . $where . '
                                   GROUP BY ' . $this->idfield);
        }
    }
    // }}}

    private function mkTests($field, $text)
    {
        $tests = array();
        $tests[] = $field . XDB::formatWildcards(XDB::WILDCARD_PREFIX, $text);
        if (!$this->ac_beginwith) {
            $tests[] = $field . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, ' ' . $text);
            $tests[] = $field . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, '-' . $text);
        }
        return $tests;
    }

    // {{{ function getAutoComplete
    public function getAutoComplete($text)
    {
        $text = str_replace(array('%', '_'), '', $text);

        if (is_null($this->ac_where) || $this->ac_where == '') {
            $where = '';
        } else {
            $where = $this->ac_where . ' AND ';
        }

        $tests = $this->mkTests($this->valfield, $text);
        if (!is_null($this->valfield2)) {
            $tests = array_merge($tests, $this->mkTests($this->valfield2, $text));
        }

        $where .= '(' . implode(' OR ', $tests) . ')';

        return XDB::iterator('SELECT ' . $this->valfield . ' AS field'
                                       . ($this->ac_distinct ? (', COUNT(DISTINCT ' . $this->ac_unique . ') AS nb') : '')
                                       . ($this->ac_withid ? (', ' . $this->idfield . ' AS id') : '') . '
                                FROM ' . $this->from . '
                                     ' . $this->ac_join . '
                               WHERE ' . $where . '
                            GROUP BY ' . $this->valfield . '
                            ORDER BY ' . ($this->ac_distinct ? 'nb DESC' : $this->valfield) . '
                               LIMIT 11');
    }
    // }}}

    // {{{ function loadOptions
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
    // }}}
}
// }}}

// {{{ class DE_NameTypes
// returns 'system' names ('lastname', 'lastname_marital', ...)
class DE_NameTypes extends DirEnumeration
{
    protected $from     = 'profile_name_enum';
    protected $valfield = 'type';
}
// }}}

/** GROUPS
 */
// {{{ class DE_Binets
class DE_Binets extends DirEnumeration
{
    protected $from = 'binets_def';

    protected $ac_join = 'INNER JOIN binets_ins ON (binets_def.id = binets_ins.binet_id)';
    protected $ac_unique = 'binets_ins.user_id';
}
// }}}

// {{{ class DE_Sections
class DE_Sections extends DirEnumeration
{
    protected $from = 'sections';

    protected $ac_join = 'INNER JOIN profiles ON (profiles.section = sections.id)';
    protected $ac_unique = 'profiles.pid';
}
// }}}

// {{{ class DE_GroupesX
class DE_GroupesX extends DirEnumeration
{
    protected $idfield   = 'asso.id';
    protected $valfield  = 'asso.nom';
    protected $valfield2 = 'asso.diminutif';
    protected $from      = 'groups AS asso';
    protected $where     = 'WHERE (cat = \'GroupesX\' OR cat = \'Institutions\') AND pub = \'public\'';

    protected $ac_join   = "INNER JOIN group_members AS memb ON (asso.id = memb.asso_id
                                    AND (asso.cat = 'GroupesX' OR asso.cat = 'Institutions')
                                    AND asso.pub = 'public')";
    protected $ac_unique = 'memb.uid';
}
// }}}

/** EDUCATION
 */
// {{{ class DE_EducationSchools
class DE_EducationSchools extends DirEnumeration
{
    protected $valfield  = 'name';
    protected $valfield2 = 'abbreviation';
    protected $from      = 'profile_education_enum';

    protected $ac_join   = 'INNER JOIN profile_education ON (profile_education.eduid = profile_education_enum.id)';
    protected $ac_unique = 'profile_education.uid';
}
// }}}

// {{{ class DE_EducationDegrees
class DE_EducationDegrees extends DirEnumeration
{
    protected $from = 'profile_education_degree_enum';
    protected $valfield = 'degree';

    protected $suboptions = array();

    protected function loadOptions()
    {
        $res = XDB::query('SELECT ped.eduid, ped.degreeid, pede.degree
                             FROM profile_education_enum AS pee
                        LEFT JOIN profile_education_degree AS ped ON (pee.id = ped.eduid)
                        LEFT JOIN profile_education_degree_enum AS pede ON (ped.degreeid = pede.id)
                         ORDER BY pede.degree');
        $options = array();
        foreach($res->fetchAllRow() as $row) {
            list($eduid, $degreeid, $name) = $row;
            $options[$degreeid] = array('id' => $degreeid, 'field' => $name);
            if (!array_key_exists($eduid, $this->suboptions)) {
                $this->suboptions[$eduid] = array();
            }
            $this->suboptions[$eduid][] = array('id' => $degreeid, 'field' => $name);
        }
        $this->options = PlIteratorUtils::fromArray($options, 1, true);
    }

    public function getOptions($eduid = null)
    {
        $this->_fetchOptions();
        if ($eduid == null) {
            return $this->options;
        }
        if (array_key_exists($eduid, $this->suboptions)) {
            return PlIteratorUtils::fromArray($this->suboptions[$eduid], 1, true);
        } else {
            return array();
        }
    }

    public function getOptionsArray($eduid = null)
    {
        $it = $this->getOptions($eduid);
        $options = array();
        while ($row = $it->next()) {
            $options[$row['id']] = $row['field'];
        }
        return $options;
    }

    public function getIDs($text, $mode, $eduid = null)
    {
        if ($eduid == null) {
            return XDB::fetchColumn('SELECT id
                                       FROM profile_education_degree_enum
                                       WHERE degree ' . XDB::formatWildcards($mode, $text));
        } else {
            return XDB::fetchColumn('SELECT pede.id
                                       FROM profile_education_degree AS ped
                                  LEFT JOIN profile_education_degree_enum AS pede ON (ped.degreeid = pede.id)
                                      WHERE ped.eduid = {?} AND pede.degree ' . XDB::formatWildcards($mode, $text), $eduid);
        }
    }
}
// }}}

// {{{ class DE_EducationFields
class DE_EducationFields extends DirEnumeration
{
    protected $valfield = 'field';
    protected $from     = 'profile_education_field_enum';

    protected $ac_join   = 'INNER JOIN profile_education ON (profile_education.fieldid = profile_education_field_enum.id)';
    protected $ac_unique = 'profile_education.uid';
}
// }}}

/** GEOLOC
 */
// {{{ class DE_Nationalities
class DE_Nationalities extends DirEnumeration
{
    protected $idfield   = 'iso_3166_1_a2';
    protected $valfield  = 'nationalityFR';
    protected $valfield2 = 'nationality';
    protected $from      = 'geoloc_countries AS gc';
    protected $join      = 'INNER JOIN profiles AS p ON (gc.iso_3166_1_a2 IN (p.nationality1, p.nationality2, p.nationality3))';

    protected $ac_join   = 'INNER JOIN profiles AS p ON (gc.iso_3166_1_a2 IN (p.nationality1, p.nationality2, p.nationality3))';
    protected $ac_unique = 'profiles.pid';
}
// }}}

// {{{ class DE_Countries
class DE_Countries extends DirEnumeration
{
    protected $idfield   = 'iso_3166_1_a2';
    protected $valfield  = 'countryFR';
    protected $valfield2 = 'country';
    protected $from      = 'geoloc_countries';

    protected $ac_join   = 'INNER JOIN profile_addresses ON (geoloc_countries.iso_3166_1_a2 = profile_addresses.countryFR';
    protected $ac_unique = 'profile_addresses.pid';
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
        $options = array();
        foreach($res->fetchAllRow() as $row) {
            list($id, $field, $country) = $row;
            $options[$id] = array('id' => $id, 'field' => $field);
            if (!array_key_exists($country, $this->suboptions)) {
                $this->suboptions[$country] = array();
            }
            $this->suboptions[$country][] = array('id' => $id, 'field' => $field);
        }
        $this->options = PlIteratorUtils::fromArray($options, 1, true);
    }

    public function getOptions($country = null)
    {
        $this->_fetchOptions();

        if ($country == null) {
            return $this->options;
        }
        if (array_key_exists($country, $this->suboptions)) {
            return PlIteratorUtils::fromArray($this->suboptions[$country], 1, true);
        } else {
            return array();
        }
    }

    public function getOptionsArray($country = null)
    {
        $it = $this->getOptions($eduid);
        $options = array();
        while ($row = $it->next()) {
            $options[$row['id']] = $row['field'];
        }
        return $options;
    }

    public function getIDs($text, $mode, $country = null)
    {
        if ($country == null) {
            return XDB::fetchColumn('SELECT id
                                       FROM geoloc_administrativeareas
                                       WHERE name ' . XDB::formatWildcards($mode, $text));
        } else {
            return XDB::fetchColumn('SELECT id
                                       FROM geoloc_administrativeareas
                                      WHERE country = {?} AND name' . XDB::formatWildcards($mode, $text), $country);
        }
    }
}
// }}}

// {{{ class DE_Localities
class DE_Localities extends DirEnumeration
{
    protected $valfield  = 'gl.name';
    protected $from      = 'geoloc_localities AS gl';

    protected $ac_join   = 'profile_addresses AS pa ON (pa.localityID = gl.id)';
    protected $ac_unique = 'pa.pid';
}
// }}}

/** JOBS
 */
// {{{ class DE_Companies
class DE_Companies extends DirEnumeration
{
    protected $valfield  = 'pje.name';
    protected $valfield2 = 'pje.acronym';
    protected $from      = 'profile_job_enum AS pje';

    protected $ac_join   = 'INNER JOIN profile_job AS pj ON (pj.jobid = pje.id)';
    protected $ac_unique = 'pj.uid';
}
// }}}

// {{{ class DE_Sectors
class DE_Sectors extends DirEnumeration
{
    protected $valfield  = 'name';
    protected $from      = 'profile_job_sector_enum';

    protected $ac_join   = 'INNER JOIN profile_job ON (profile_job_sector_enum.id = profile_job.sectorid)';
    protected $ac_unique = 'profile_job.uid';
}
// }}}

// {{{ class DE_JobDescription
class DE_JobDescription
{
    protected $valfield = 'pj.description';
    protected $from     = 'profile_job AS pj';
    protected $idfield  = 'pj.pid';

    protected $ac_unique = 'pj.pid';
}
// }}}

/** NETWORKING
 */
// {{{ class DE_Networking
class DE_Networking extends DirEnumeration
{
    protected $idfield  = 'profile_networking_enum.network_type';
    protected $valfield = 'profile_networking_enum.name';
    protected $from     = 'profile_networking_enum';


    protected $ac_join   = 'INNER JOIN profile_networking ON (profile_networking.network_type = profile_networking_enum.network_type';
    protected $ac_unique = 'profile_networking.uid';
}
// }}}
?>

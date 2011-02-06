<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
    const NAMES          = 'names';

    const BINETS         = 'binets';
    const GROUPESX       = 'groupesx';
    const SECTIONS       = 'sections';

    const EDUSCHOOLS     = 'educationschools';
    const EDUDEGREES     = 'educationdegrees';
    const EDUFIELDS      = 'educationfields';

    const CORPS          = 'corps';
    const CORPSRANKS     = 'corpsranks';

    const NATIONALITIES  = 'nationalities';
    const COUNTRIES      = 'countries';
    const ADMINAREAS     = 'adminareas';
    const SUBADMINAREAS  = 'subadminareas';
    const LOCALITIES     = 'localities';

    const COMPANIES      = 'companies';
    const JOBDESCRIPTION = 'jobdescription';
    const JOBTERMS       = 'jobterms';

    const NETWORKS       = 'networking';

    const MEDALS         = 'medals';

    const ACCOUNTTYPES   = 'accounttypes';
    const SKINS          = 'skins';

    static private $enumerations = array();

    static private function init($type)
    {
        if (Platal::globals()->cacheEnabled() && S::has('__DE_' . $type)) {
            self::$enumerations[$type] = S::v('__DE_' . $type);
        } else {
            $cls = "DE_" . ucfirst($type);
            $obj = new $cls();
            self::$enumerations[$type] = $obj;
            if (Platal::globals()->cacheEnabled()
                 && $obj->capabilities & DirEnumeration::SAVE_IN_SESSION) {
                S::set('__DE_' . $type, $obj);
            }
        }
    }

    /** Retrieves all options for a given type
     * @param $type Type of enum for which options are requested
     * @return Array of the results
     */
    static public function getOptions($type)
    {
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        if ($obj->capabilities & DirEnumeration::HAS_OPTIONS) {
            return call_user_func(array($obj, 'getOptions'));
        } else {
            return array();
        }
    }

    /** Retrieves all options for a given type
     * @param $type Type of enum for which options are requested
     * @return PlIterator over the results
     */
    static public function getOptionsIter($type)
    {
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        $args = func_get_args();
        array_shift($args);
        if ($obj->capabilities & DirEnumeration::HAS_OPTIONS) {
            return call_user_func_array(array($obj, 'getOptionsIter'), $args);
        } else {
            return PlIteratorUtils::fromArray(array());
        }
    }

    /** Retrieves all options with number of profiles for autocompletion
     * @param $type Type of enum for which options are requested
     * @param $text Text to autocomplete
     * @return PlIterator over the results
     */
    static public function getAutoComplete($type, $text)
    {
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        if ($obj->capabilities & DirEnumeration::HAS_AUTOCOMP) {
            return call_user_func(array($obj, 'getAutoComplete'), $text);
        } else {
            return PlIteratorUtils::fromArray(array());
        }
    }

    /** Retrieves a list of IDs for a given type
     * @param $type Type of enum for which IDs are requested
     * @param $text Text to search in enum valuees
     * @param $mode Mode of search for those IDs (prefix/suffix/infix)
     */
    static public function getIDs($type, $text, $mode = XDB::WILDCARD_EXACT)
    {
        if (!array_key_exists($type, self::$enumerations)) {
            self::init($type);
        }
        $obj = self::$enumerations[$type];
        if ($obj->capabilities & DirEnumeration::HAS_OPTIONS) {
            return call_user_func(array($obj, 'getIDs'), $text, $mode);
        } else {
            return array();
        }
    }

    /** Retrieves a single ID for a given type.
     * @param $type Type of the enum for which an ID is requested
     * @param $text Text to search in enum values
     * @param $mode Mode of search of that ID (prefix/suffix/infix/exact)
     */
    static public function getID($type, $text, $mode = XDB::WILDCARD_EXACT)
    {
        $ids = self::getIDs($type, $text, $mode);
        return array_shift($ids);
    }
}
// }}}

// {{{ class DirEnumeration
abstract class DirEnumeration
{
    const AUTOCOMPLETE_LIMIT = 11;

    const HAS_OPTIONS  = 0x001;
    const HAS_AUTOCOMP = 0x002;
    const SAVE_IN_SESSION = 0x004;

    public $capabilities = 0x003; // self::HAS_OPTIONS | self::HAS_AUTOCOMP;

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

    public function getOptionsIter()
    {
        $options = $this->getOptions();
        $options = self::expandArray($options);
        return PlIteratorUtils::fromArray($options, 1, true);
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

    /** Builds a list of query parts for searching @$text in @$field :
     * field LIKE 'text%', field LIKE '% text%', field LIKE '%-text%'
     */
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

    static protected function expandArray(array $tab, $keyname = 'id', $valname = 'field')
    {
        $res = array();
        foreach ($tab as $key => $val) {
            $res[$key] = array(
                            $keyname => $key,
                            $valname => $val,
                        );
        }
        return $res;
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
                               LIMIT ' . self::AUTOCOMPLETE_LIMIT);
    }
    // }}}

    // {{{ function loadOptions
    /** The function used to load options
     */
    protected function loadOptions()
    {
        $this->options = XDB::fetchAllAssoc('id', 'SELECT ' . $this->valfield . ' AS field,
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

// {{{ class DE_WithSuboption
/** A class for DirEnum with possibility to select only suboptions for a given parameter (country, school, ...)
 */
abstract class DE_WithSuboption extends DirEnumeration
{
    protected $optfield;

    protected $suboptions = null;

    protected function loadOptions()
    {
        $opts = XDB::fetchAllAssoc('id', 'SELECT ' . $this->valfield . ' AS field,
                                                 ' . $this->optfield . ' AS subid,
                                                 ' . $this->idfield . ' AS id
                                            FROM ' . $this->from . '
                                                 ' . $this->join . '
                                                 ' . $this->where . '
                                        GROUP BY ' . $this->valfield . '
                                        ORDER BY ' . $this->valfield);
        $this->options = array();
        $this->suboptions = array();
        foreach ($opts as $id => $opt) {
            $this->options[$id] = $opt['field'];
            if (!array_key_exists($opt['subid'], $this->suboptions)) {
                $this->suboptions[$opt['subid']] = array();
            }
            $this->suboptions[$opt['subid']][$id] = $opt['field'];
        }
    }

    public function getOptions($subid = null)
    {
        $this->_fetchOptions();
        if ($subid == null) {
            return $this->options;
        } else if (array_key_exists($subid, $this->suboptions)) {
            return $this->suboptions[$subid];
        } else {
            return array();
        }
    }

    public function getOptionsIter($subid = null)
    {
        return PlIteratorUtils::fromArray(self::expandArray($this->getOptions($subid)), 1, true);
    }

    public function getIDs($text, $mode, $subid = null)
    {
        if ($mode == XDB::WILDCARD_EXACT) {
            $options = $this->getOptions($subid);
            return array_keys($options, $text);
        } else {
            if ($this->where == null) {
                $where = 'WHERE ';
            } else {
                $where = $this->where . ' AND ';
            }
            if ($subid != null && array_key_exists($subid, $this->suboptions)) {
                $where .= XDB::format($this->optfield . ' = {?} AND ', $subid);
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

    public function getAutoComplete($text, $subid = null)
    {
        $text = str_replace(array('%', '_'), '', $text);

        if (is_null($this->ac_where) || $this->ac_where == '') {
            $where = '';
        } else {
            $where = $this->ac_where . ' AND ';
        }

        if ($subid != null && array_key_exists($subid, $this->suboptions)) {
            $where .= XDB::format($this->optfield . ' = {?} AND ', $subid);
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
                               LIMIT ' . self::AUTOCOMPLETE_LIMIT);
    }
}
// }}}

// {{{ class DE_NameTypes
// returns 'system' names ('lastname', 'lastname_marital', ...)
class DE_NameTypes extends DirEnumeration
{
    public $capabilities = 0x005; // self::HAS_OPTIONS | self::SAVE_IN_SESSION;

    protected $from     = 'profile_name_enum';
    protected $valfield = 'type';
}
// }}}

// {{{ class DE_Names
// returns 'system' names ('lastname', 'lastname_marital', ...)
class DE_Names extends DirEnumeration
{
    public $capabilities = 0x005; // self::HAS_OPTIONS | self::SAVE_IN_SESSION;

    protected $from     = 'profile_name_enum';
    protected $idfield  = 'type';
    protected $valfield = 'name';
}
// }}}

/** GROUPS
 */
// {{{ class DE_Binets
class DE_Binets extends DirEnumeration
{
    protected $from = 'profile_binet_enum';

    protected $ac_join = 'INNER JOIN profile_binets ON (profile_binet_enum.id = profile_binets.binet_id)';
    protected $ac_unique = 'profile_binets.pid';
}
// }}}

// {{{ class DE_Sections
class DE_Sections extends DirEnumeration
{
    protected $from = 'profile_section_enum';

    protected $ac_join = 'INNER JOIN profiles ON (profiles.section = profile_section_enum.id)';
    protected $ac_unique = 'profiles.pid';
}
// }}}

// {{{ class DE_GroupesX
class DE_GroupesX extends DirEnumeration
{
    protected $idfield   = 'groups.id';
    protected $valfield  = 'groups.nom';
    protected $valfield2 = 'groups.diminutif';
    protected $from      = 'groups';
    protected $where     = 'WHERE (cat = \'GroupesX\' OR cat = \'Institutions\') AND pub = \'public\'';

    protected $ac_join   = "INNER JOIN group_members ON (groups.id = group_members.asso_id
                                    AND (groups.cat = 'GroupesX' OR groups.cat = 'Institutions')
                                    AND groups.pub = 'public')";
    protected $ac_unique = 'group_members.uid';
}
// }}}

/** EDUCATION
 */
// {{{ class DE_EducationSchools
class DE_EducationSchools extends DirEnumeration
{
    protected $idfield   = 'profile_education_enum.id';
    protected $valfield  = 'profile_education_enum.name';
    protected $valfield2 = 'profile_education_enum.abbreviation';
    protected $from      = 'profile_education_enum';

    protected $ac_join   = 'INNER JOIN profile_education ON (profile_education.eduid = profile_education_enum.id)';
    protected $ac_unique = 'profile_education.pid';
}
// }}}

// {{{ class DE_EducationDegrees
class DE_EducationDegrees extends DirEnumeration
{
    public $capabilities = self::HAS_OPTIONS;

    protected $idfield  = 'profile_education_degree.degreeid';
    protected $optfield = 'profile_education_degree.eduid';
    protected $valfield = 'profile_education_degree_enum.degree';
    protected $from = 'profile_education_degree_enum';
    protected $join = 'INNER JOIN profile_education_degree ON (profile_education_degree.degreeid = profile_education_degree_enum.id)';

}
// }}}

// {{{ class DE_EducationFields
class DE_EducationFields extends DirEnumeration
{
    protected $valfield = 'profile_education_field_enum.field';
    protected $from     = 'profile_education_field_enum';

    protected $ac_join   = 'INNER JOIN profile_education ON (profile_education.fieldid = profile_education_field_enum.id)';
    protected $ac_unique = 'profile_education.pid';
}
// }}}

// {{{ class DE_Corps
class DE_Corps extends DirEnumeration
{
    protected $idfield   = 'profile_corps_enum.id';
    protected $valfield  = 'profile_corps_enum.name';
    protected $valfield2 = 'profile_corps_enum.abbrev';
    protected $from      = 'profile_corps_enum';

    protected $ac_unique = 'profile_corps.pid';
    protected $ac_join   = 'INNER JOIN profile_corps ON (profile_corps.current_corpsid = profile_corps_enum.id)';
}
// }}}

// {{{ class DE_CorpsRanks
class DE_CorpsRanks extends DirEnumeration
{
    protected $idfield   = 'profile_corps_rank_enum.id';
    protected $valfield  = 'profile_corps_rank_enum.name';
    protected $valfield2 = 'profile_corps_rank_enum.abbrev';
    protected $from      = 'profile_corps_rank_enum';

    protected $ac_unique = 'profile_corps.pid';
    protected $ac_join   = 'INNER JOIN profile_corps ON (profile_corps.rankid = profile_corps_rank_enum.id)';
}
// }}}

/** GEOLOC
 */
// {{{ class DE_Nationalities
class DE_Nationalities extends DirEnumeration
{
    protected $idfield   = 'geoloc_countries.iso_3166_1_a2';
    protected $valfield  = 'geoloc_countries.nationality';
    protected $valfield2 = 'geoloc_countries.nationalityEn';
    protected $from      = 'geoloc_countries';
    protected $join      = 'INNER JOIN profiles ON (geoloc_countries.iso_3166_1_a2 IN (profiles.nationality1, profiles.nationality2, profiles.nationality3))';

    protected $ac_join   = 'INNER JOIN profiles ON (geoloc_countries.iso_3166_1_a2 IN (profiles.nationality1, profiles.nationality2, profiles.nationality3))';
    protected $ac_unique = 'profiles.pid';
}
// }}}

// {{{ class DE_Countries
class DE_Countries extends DirEnumeration
{
    protected $idfield   = 'geoloc_countries.iso_3166_1_a2';
    protected $valfield  = 'geoloc_countries.country';
    protected $valfield2 = 'geoloc_countries.countryEn';
    protected $from      = 'geoloc_countries';

    protected $ac_join   = 'INNER JOIN profile_addresses ON (geoloc_countries.iso_3166_1_a2 = profile_addresses.countryId)';
    protected $ac_unique = 'profile_addresses.pid';
}
// }}}

// {{{ class DE_AdminAreas
class DE_AdminAreas extends DE_WithSuboption
{
    protected $idfield   = 'geoloc_administrativeareas.id';
    protected $optfield  = 'geoloc_administrativeareas.country';
    protected $valfield  = 'geoloc_administrativeareas.name';
    protected $from      = 'geoloc_administrativeareas';

    protected $ac_join   = 'INNER JOIN profile_addresses ON (profile_addresses.administrativeAreaId = geoloc_administrativeareas.id)';
    protected $ac_unique = 'profile_addresses.pid';
}
// }}}

// {{{ class DE_SubAdminAreas
class DE_SubAdminAreas extends DE_WithSuboption
{
    protected $idfield   = 'geoloc_subadministrativeareas.id';
    protected $optfield  = 'geoloc_subadministrativeareas.administrativearea';
    protected $valfield  = 'geoloc_subadministrativeareas.name';
    protected $from      = 'geoloc_subadministrativeareas';

    protected $ac_join   = 'INNER JOIN profile_addresses ON (profile_addresses.subadministrativeAreaId = geoloc_subadministrativeareas.id)';
    protected $ac_unique = 'profile_addresses.pid';
}
// }}}

// {{{ class DE_Localities
class DE_Localities extends DirEnumeration
{
    protected $idfield   = 'geoloc_localities.id';
    protected $valfield  = 'geoloc_localities.name';
    protected $from      = 'geoloc_localities';

    protected $ac_join   = 'INNER JOIN profile_addresses ON (profile_addresses.localityID = geoloc_localities.id)';
    protected $ac_unique = 'profile_addresses.pid';
}
// }}}

/** JOBS
 */
// {{{ class DE_Companies
class DE_Companies extends DirEnumeration
{
    protected $idfield   = 'profile_job_enum.id';
    protected $valfield  = 'profile_job_enum.name';
    protected $valfield2 = 'profile_job_enum.acronym';
    protected $from      = 'profile_job_enum';

    protected $ac_join   = 'INNER JOIN profile_job ON (profile_job.jobid = profile_job_enum.id)';
    protected $ac_unique = 'profile_job.pid';
}
// }}}

// {{{ class DE_JobDescription
class DE_JobDescription extends DirEnumeration
{
    protected $valfield = 'profile_job.description';
    protected $from     = 'profile_job';
    protected $idfield  = 'profile_job.pid';

    protected $ac_unique = 'profile_job.pid';
}
// }}}

// {{{ class DE_JobTerms
class DE_JobTerms extends DirEnumeration
{
    // {{{ function getAutoComplete
    public function getAutoComplete($text)
    {
        $tokens = JobTerms::tokenize($text.'%');
        if (count($tokens) == 0) {
            return PlIteratorUtils::fromArray(array());
        }
        $token_join = JobTerms::token_join_query($tokens, 'e');
        return XDB::iterator('SELECT  e.jtid AS id, e.full_name AS field, COUNT(DISTINCT p.pid) AS nb
                                 FROM  profile_job_term_enum AS e
                           INNER JOIN  profile_job_term_relation AS r ON (r.jtid_1 = e.jtid)
                           INNER JOIN  profile_job_term AS p ON (r.jtid_2 = p.jtid)
                           '.$token_join.'
                             GROUP BY  e.jtid
                             ORDER BY  nb DESC, field
                                LIMIT ' . self::AUTOCOMPLETE_LIMIT);
    }
    // }}}
}
// }}}

/** NETWORKING
 */
// {{{ class DE_Networking
class DE_Networking extends DirEnumeration
{
    protected $idfield  = 'profile_networking_enum.nwid';
    protected $valfield = 'profile_networking_enum.name';
    protected $from     = 'profile_networking_enum';


    protected $ac_join   = 'INNER JOIN profile_networking ON (profile_networking.nwid = profile_networking_enum.nwid)';
    protected $ac_unique = 'profile_networking.pid';
}
// }}}

/** MEDALS
 */
// {{{ class DE_Medals
class DE_Medals extends DirEnumeration
{
    protected $from = 'profile_medal_enum';

    protected $ac_join = 'INNER JOIN profile_medals ON (profile_medals.mid = profile_medal_enum.id)';
    protected $ac_unique = 'profile_medals.pid';
}
// }}}

/** ACCOUNTS
 */
// {{{ class DE_AccountTypes
class DE_AccountTypes extends DirEnumeration
{
    public $capabilities = 0x005; // self::HAS_OPTIONS | self::SAVE_IN_SESSION;

    protected $from     = 'account_types';
    protected $valfield = 'perms';
    protected $idfield  = 'type';
}
// }}}

// {{{ class DE_Skins
class DE_Skins extends DirEnumeration
{
    public $capabilities = 0x005; // self::HAS_OPTIONS | self::SAVE_IN_SESSION;

    protected $from      = 'skins';
    protected $valfield  = 'name';
    protected $idfield   = 'skin_tpl';
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

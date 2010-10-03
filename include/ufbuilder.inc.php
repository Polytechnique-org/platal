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

// {{{ class UserFilterBuilder
class UserFilterBuilder
{
    private $envprefix;
    private $fields;
    private $valid = true;
    private $ufc = null;
    private $orders = array();

    /** Constructor
     * @param $fields An array of UFB_Field objects
     * @param $envprefix Prefix to use for parts of the query
     */
    public function __construct($fields, $envprefix = '')
    {
        $this->fields = $fields;
        $this->envprefix   = $envprefix;
    }

    /** Builds the UFC; returns as soon as a field says it is invalid
     */
    private function buildUFC()
    {
        if ($this->ufc != null) {
            return;
        }
        $this->ufc = new PFC_And();

        foreach ($this->fields as $field) {
            $this->valid = $field->apply(&$this);
            if (!$this->valid) {
                return;
            }
        }
    }

    public function addCond(PlFilterCondition &$cond)
    {
        $this->ufc->addChild($cond);
    }

    public function addOrder(PlFilterOrder &$order)
    {
        $this->order[] = $order;
    }

    public function isValid()
    {
        $this->buildUFC();
        return $this->valid;
    }

    public function isEmpty()
    {
        $this->buildUFC();
        foreach ($this->fields as $field) {
            if (! $field->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /** Returns the built UFC
     * @return The UFC, or PFC_False() if an error happened
     */
    public function getUFC()
    {
        $this->buildUFC();
        if ($this->valid) {
            return $this->ufc;
        } else {
            return new PFC_False();
        }
    }

    /** Returns adequate orders
     */
    public function getOrders()
    {
        $this->buildUFC();
        return $this->orders;
    }

    /** Wrappers around Env::i/s/..., to add envprefix
     */
    public function s($key, $def = '')
    {
        return Env::s($this->envprefix . $key, $def);
    }

    public function t($key, $def = '')
    {
        return Env::t($this->envprefix . $key, $def);
    }

    public function i($key, $def = 0)
    {
        return Env::i($this->envprefix . $key, $def);
    }

    public function v($key, $def = null)
    {
        return Env::v($this->envprefix . $key, $def);
    }

    public function b($key, $def = false)
    {
        return Env::b($this->envprefix . $key, $def);
    }

    public function has($key)
    {
        return Env::has($this->envprefix . $key);
    }

    public function blank($key, $strict = false)
    {
        return Env::blank($key, $strict);
    }

    public function hasAlnum($key)
    {
        $str = $this->s($key);
        return preg_match('/[a-z0-9]/i', $str);
    }

    public function hasAlpha($key)
    {
        $str = $this->s($key);
        return preg_match('/[a-z]/i', $str);
    }

    public function isOn($key)
    {
        return $this->has($key) && $this->t($key) == 'on';
    }
}
// }}}

// {{{ class UFB_QuickSearch
class UFB_QuickSearch extends UserFilterBuilder
{
    public function __construct($envprefix = '')
    {
        $fields = array(
            new UFBF_Quick('quick', 'Recherche rapide'),
        );
        parent::__construct($fields, $envprefix);
    }
}
// }}}

// {{{ class UFB_AdvancedSearch
class UFB_AdvancedSearch extends UserFilterBuilder
{
    public function __construct($envprefix = '')
    {
        $fields = array(
            new UFBF_Name('name', 'Nom'),
            new UFBF_Promo('promo1', 'Promotion', 'egal1'),
            new UFBF_Promo('promo2', 'Promotion', 'egal2'),
            new UFBF_Sex('woman', 'Sexe'),
            new UFBF_Registered('subscriber', 'Inscrit'),
            new UFBF_Dead('alive', 'En vie'),

            new UFBF_Town('city', 'Ville / Code Postal'),
            new UFBF_Country('countryTxt', 'country', 'Pays'),
            new UFBF_AdminArea('region', 'Région'),

            new UFBF_JobCompany('entreprise', 'Entreprise'),
            new UFBF_JobDescription('jobdescription', 'Fonction'),
            new UFBF_JobCv('cv', 'CV'),
            new UFBF_JobTerms('jobterm', 'Mots-clefs'),

            new UFBF_Nationality('nationaliteTxt', 'nationalite', 'Nationalité'),
            new UFBF_Binet('binetTxt', 'binet', 'Binet'),
            new UFBF_Group('groupexTxt', 'groupex', 'Groupe X'),
            new UFBF_Section('sectionTxt', 'section', 'Section'),

            new UFBF_EducationSchool('schoolTxt', 'school', "École d'application"),
            new UFBF_EducationDegree('diplomaTxt', 'diploma', 'Diplôme'),
            new UFBF_EducationField('fieldTxt', 'field', "Domaine d'études"),

            new UFBF_Comment('free', 'Commentaire'),
            new UFBF_Phone('phone_number', 'Téléphone'),
            new UFBF_Networking('networking_address', 'networking_type', 'Networking et sites webs'),

            new UFBF_Mentor('only_referent', 'Référent'),
        );
        parent::__construct($fields, $envprefix);
    }
}
// }}}

// {{{ class UFB_MentorSearch
class UFB_MentorSearch extends UserFilterBuilder
{
    public function __construct($envprefix = '')
    {
        $fields = array(
            new UFBF_MentorCountry('country'),
            new UFBF_MentorTerm('jobterm', 'jobtermText'),
            new UFBF_MentorExpertise('expertise'),
        );
        parent::__construct($fields, $envprefix);
    }
}
// }}}

// {{{ class UFB_Field
abstract class UFB_Field
{
    protected $envfield;
    protected $formtext;

    protected $empty = false;
    protected $val = null;

    /** Constructor
     * @param $envfield Name of the field in the environment
     * @param $formtext User-friendly name of that field
     */
    public function __construct($envfield, $formtext = '')
    {
        $this->envfield = $envfield;
        if ($formtext != '') {
            $this->formtext = $formtext;
        } else {
            $formtext = ucfirst($envfield);
        }
    }

    /** Prints the given error message to the user, and returns false
     * in order to be used as return $this->raise('ERROR');
     *
     * All %s in the $msg will be replaced with the formtext.
     */
    protected function raise($msg)
    {
        Platal::page()->trigError(str_replace('%s', $this->formtext, $msg));
        return false;
    }

    public function apply(UserFilterBuilder &$ufb) {
        if (!$this->check($ufb)) {
            return false;
        }

        if (!$this->empty) {
            $ufc = $this->buildUFC($ufb);
            if ($ufc != null) {
                $ufb->addCond($ufc);
            }
        }
        return true;
    }

    public function isEmpty()
    {
        return $this->empty;
    }

    /** Create the UFC associated to the field; won't be called
     * if the field is "empty"
     * @param &$ufb UFB to which fields must be added
     * @return UFC
     */
    abstract protected function buildUFC(UserFilterBuilder &$ufb);

    /** This function is intended to run consistency checks on the value
     * @return boolean Whether the input is valid
     */
    abstract protected function check(UserFilterBuilder &$ufb);
}
// }}}

// {{{ class UFBF_Text
abstract class UFBF_Text extends UFB_Field
{
    private $minlength;
    private $maxlength;

    public function __construct($envfield, $formtext = '', $minlength = 2, $maxlength = 255)
    {
        parent::__construct($envfield, $formtext);
        $this->minlength      = $minlength;
        $this->maxlength      = $maxlength;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->t($this->envfield);
        if (strlen($this->val) < $this->minlength) {
            return $this->raise("Le champ %s est trop court (minimum {$this->minlength}).");
        } else if (strlen($this->val) > $this->maxlength) {
            return $this->raise("Le champ %s est trop long (maximum {$this->maxlength}).");
        } else if (preg_match(":[\]\[<>{}~§_`|%$^=]|\*\*:u", $this->val)) {
            return $this->raise('Le champ %s contient un caractère interdit rendant la recherche impossible.');
        }

        return true;
    }
}
// }}}

// {{{ class UFBF_Range
/** Subclass to use for fields which only allow integers within a range
 */
abstract class UFBF_Range extends UFB_Field
{

    private $min;
    private $max;

    public function __construct($envfield, $formtext = '', $min = 0, $max = 65535)
    {
        parent::__construct($envfield, $formtext);
        $this->min = $min;
        $this->max = $max;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->i($this->envfield);
        if ($this->val < $this->min) {
            return $this->raise("Le champs %s est inférieur au minimum ({$this->min}).");
        } else if ($this->val > $this->max) {
            return $this->raise("Le champ %s est supérieur au maximum ({$this->max}).");
        }
        return true;
    }
}
// }}}

// {{{ class UFBF_Index
/** Subclass to use for indexed fields
 */
abstract class UFBF_Index extends UFB_Field
{
    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
        }
        $this->val = $ufb->i($this->envfield);
        return true;
    }
}
// }}}

// {{{ class UFBF_Enum
/** Subclass to use for fields whose value must belong to a specific set of values
 */
abstract class UFBF_Enum extends UFB_Field
{
    protected $allowedvalues;

    public function __construct($envfield, $formtext = '', $allowedvalues = array(), $strict = false)
    {
        parent::__construct($envfield, $formtext);
        $this->allowedvalues = $allowedvalues;
        $this->strict = $strict;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->v($this->envfield);
        if (! in_array($this->val, $this->allowedvalues)) {
            if ($this->strict) {
                return $this->raise("La valeur {$this->val} n'est pas valide pour le champ %s.");
            } else {
                $this->empty = true;
            }
        }
        return true;
    }
}
// }}}

// {{{ class UFBF_Bool
abstract class UFBF_Bool extends UFB_Field
{
    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->b($this->envfield);
        return true;
    }
}
// }}}

// {{{ class UFBF_Mixed
/** A class for building UFBFs when the user can input either a text or an ID
 */
abstract class UFBF_Mixed extends UFB_Field
{
    /** Name of the DirEnum on which class is based
     */
    protected $direnum;

    protected $envfieldindex;

    public function __construct($envfieldtext, $envfieldindex, $formtext = '')
    {
        parent::__construct($envfieldtext, $formtext);
        $this->envfieldindex = $envfieldindex;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfieldindex) && !$ufb->hasAlnum($this->envfield)) {
            $this->empty = true;
            return true;
        }

        if (!$ufb->blank($this->envfieldindex)) {
            $index = $ufb->v($this->envfieldindex);
            if (is_int($index)) {
                $index = intval($index);
            } else {
                $index = strtoupper($index);
            }
            $this->val = array($index);
        } else {
            $indexes = DirEnum::getIDs($this->direnum, $ufb->t($this->envfield),
                $ufb->b('exact') ? XDB::WILDCARD_EXACT : XDB::WILDCARD_CONTAINS);
            if (count($indexes) == 0) {
                return false;
            }
            $this->val = $indexes;
        }
        return true;
    }
}
// }}}

// {{{ class UFBF_Quick
class UFBF_Quick extends UFB_Field
{
    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = str_replace('*', '%', replace_accent($ufb->t($this->envfield)));

        return true;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {

        $r = $s = $this->val;

        /** Admin: Email, IP
         */
        if (S::admin() && strpos($s, '@') !== false) {
            return new UFC_Email($s);
        } else if (S::admin() && preg_match('/[0-9]+\.([0-9]+|%)\.([0-9]+|%)\.([0-9]+|%)/', $s)) {
            return new UFC_Ip($s);
        }

        $conds = new PFC_And();

        /** Name
         */
        $s = preg_replace('!\d+!', ' ', $s);
        $strings = preg_split("![^a-zA-Z%]+!",$s, -1, PREG_SPLIT_NO_EMPTY);
        if (count($strings) > 5) {
            Platal::page()->trigWarning("Tu as indiqué trop d'éléments dans ta recherche, seuls les 5 premiers seront pris en compte");
            $strings = array_slice($strings, 0, 5);
        }

        if (count($strings)) {
            if (S::logged()) {
                $flags = array();
            } else {
                $flags = array('public');
            }
            $exact =$ufb->b('exact');
            $conds->addChild(new UFC_NameTokens($st, $flags, $ufb->b('with_soundex'), $exact));

            $ufb->addOrder(new UFO_Score());
        }

        /** Promo ranges
         */
        $s = preg_replace('! *- *!', '-', $r);
        $s = preg_replace('!([<>]) *!', ' \1', $s);
        $s = preg_replace('![^0-9\-><]!', ' ', $s);
        $s = preg_replace('![<>\-] !', '', $s);
        $ranges = preg_split('! +!', $s, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ranges as $r) {
            if (preg_match('!^\d{4}$!', $r)) {
                $conds->addChild(new UFC_Promo('=', UserFilter::DISPLAY, 'X' . $r));
            } elseif (preg_match('!^(\d{4})-(\d{4})$!', $r, $matches)) {
                $p1=min(intval($matches[1]), intval($matches[2]));
                $p2=max(intval($matches[1]), intval($matches[2]));
                $conds->addChild(new PFC_And(
                    new UFC_Promo('>=', UserFilter::DISPLAY, 'X' . $p1),
                    new UFC_Promo('<=', UserFilter::DISPLAY, 'X' . $p2)
                ));
            } elseif (preg_match('!^<(\d{4})!', $r, $matches)) {
                $conds->addChild(new UFC_Promo('<=', UserFilter::DISPLAY, 'X' . $matches[1]));
            } elseif (preg_match('!^>(\d{4})!', $r, $matches)) {
                $conds->addChild(new UFC_Promo('>=', UserFilter::DISPLAY, 'X' . $matches[1]));
            }
        }

        /** Phone number
         */
        $t = preg_replace('!(\d{4}-\d{4}|>\d{4}|<\d{4})!', '', $s);
        $t = preg_replace('![<>\- ]!', '', $t);
        if (strlen($t) > 4) {
            $conds->addChild(new UFC_Phone($t));
        }

        return $conds;
    }
}
// }}}

// {{{ class UFBF_Name
class UFBF_Name extends UFBF_Text
{
    protected function check(UserFilterBuilder &$ufb)
    {
        if (!parent::check($ufb)) {
            return false;
        }

        $this->val = preg_split('/[[:space:]]/', $this->val);
        if (count($this->val) == 0) {
            $this->empty = true;
        }
        return true;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_NameTokens($this->val, array(), $ufb->b('with_soundex'), $ufb->b('exact'));
    }
}
// }}}

// {{{ class UFBF_Promo
class UFBF_Promo extends UFB_Field
{
    private static $validcomps = array('<', '<=', '=', '>=', '>');
    private $comp;
    private $envfieldcomp;

    public function __construct($envfield, $fromtext = '', $envfieldcomp)
    {
        parent::__construct($envfield, $fromtext);
        $this->envfieldcomp = $envfieldcomp;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if ($ufb->blank($this->envfield) || $ufb->blank($this->envfieldcomp)) {
            $this->empty = true;
            return true;
        }

        $this->val  = $ufb->i($this->envfield);
        $this->comp = $ufb->v($this->envfieldcomp);

        if (!in_array($this->comp, self::$validcomps)) {
            return $this->raise("Le critère {$this->comp} n'est pas valide pour le champ %s");
        }

        if (preg_match('/^[0-9]{2}$/', $this->val)) {
            $this->val += 1900;
        }
        if ($this->val < 1900 || $this->val > 9999) {
            return $this->raise("Le champ %s doit être une année à 4 chiffres.");
        }
        return true;
    }

    protected function buildUFC(UserFilterBuilder &$ufb) {
        return new UFC_Promo($this->comp, UserFilter::GRADE_ING, $this->val);
    }
}
// }}}

// {{{ class UFBF_Sex
class UFBF_Sex extends UFBF_Enum
{
    public function __construct($envfield, $formtext = '')
    {
        parent::__construct($envfield, $formtext, array(1, 2));
    }

    private static function getVal($id)
    {
        switch($id) {
        case 1:
            return User::GENDER_MALE;
            break;
        case 2:
            return User::GENDER_FEMALE;
            break;
        }
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Sex(self::getVal($this->val));
    }
}
// }}}

// {{{ class UFBF_Registered
class UFBF_Registered extends UFBF_Enum
{
    public function __construct($envfield, $formtext = '')
    {
        parent::__construct($envfield, $formtext, array(1, 2));
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($this->val == 1) {
            return new UFC_Registered();
        } else if ($this->val == 2) {
            return new PFC_Not(new UFC_Registered());
        }
    }
}
// }}}

// {{{ class UFBF_Dead
class UFBF_Dead extends UFBF_Enum
{
    public function __construct($envfield, $formtext = '')
    {
        parent::__construct($envfield, $formtext, array(1, 2));
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($this->val == 1) {
            return new PFC_Not(new UFC_Dead());
        } else if ($this->val == 2) {
            return new UFC_Dead();
        }
    }
}
// }}}

// {{{ class UFBF_Town
/** Retrieves a town, either from a postal code or a town name
 */
class UFBF_Town extends UFBF_Text
{
    const TYPE_TEXT = 1;
    const TYPE_ZIP  = 2;
    const TYPE_ANY  = 3;

    private $type;
    private $onlycurrentfield;

    public function __construct($envfield, $formtext = '', $type = self::TYPE_ANY, $onlycurrentfield = 'only_current')
    {
        $this->type = $type;
        $this->onlycurrentfield = $onlycurrentfield;
        parent::__construct($envfield, $formtext, 2, 30);
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->isOn($this->onlycurrentfield)) {
            $flags = UFC_Address::FLAG_CURRENT;
        } else {
            $flags = UFC_Address::FLAG_ANY;
        }

        if (preg_match('/[0-9]/', $this->val)) {
            if ($this->type & self::TYPE_ZIP) {
                return new UFC_AddressField($this->val, UFC_AddressField::FIELD_ZIPCODE, UFC_Address::TYPE_ANY, $flags);
            } else {
                return new PFC_False();
            }
        } else {
            $byname = new UFC_AddressText(null, XDB::WILDCARD_CONTAINS, UFC_Address::TYPE_ANY, $flags, null, $this->val);
            $byzip  = new UFC_AddressField($this->val, UFC_AddressField::FIELD_ZIPCODE, UFC_Address::TYPE_ANY, $flags);
            if ($this->type & self::TYPE_ANY) {
                return new PFC_Or($byname, $byzip);
            } else if ($this->type & self::TYPE_TEXT) {
                return $byname;
            } else {
                return $byzip;
            }
        }
    }
}
// }}}

// {{{ class UFBF_Country
class UFBF_Country extends UFBF_Mixed
{
    protected $direnum = DirEnum::COUNTRIES;
    protected $onlycurrentfield;

    public function __construct($envfieldtext, $envfieldindex, $formtext = '', $onlycurrentfield = 'only_current')
    {
        parent::__construct($envfieldtext, $envfieldindex, $formtext);
        $this->onlycurrentfield = $onlycurrentfield;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->isOn($this->onlycurrentfield)) {
            $flags = UFC_Address::FLAG_CURRENT;
        } else {
            $flags = UFC_Address::FLAG_ANY;
        }

        return new UFC_AddressField($this->val, UFC_AddressField::FIELD_COUNTRY, UFC_Address::TYPE_ANY, $flags);
    }
}
// }}}

// {{{ class UFBF_AdminArea
class UFBF_AdminArea extends UFBF_Index
{
    protected $direnum = DirEnum::ADMINAREAS;
    protected $onlycurrentfield;

    public function __construct($envfield, $formtext = '', $onlycurrentfield = 'only_current')
    {
        parent::__construct($envfield, $formtext);
        $this->onlycurrentfield = $onlycurrentfield;
    }


    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->isOn($this->onlycurrentfield)) {
            $flags = UFC_Address::FLAG_CURRENT;
        } else {
            $flags = UFC_Address::FLAG_ANY;
        }

        return new UFC_AddressField($this->val, UFC_AddressField::FIELD_ADMAREA, UFC_Address::TYPE_ANY, $flags);
    }
}
// }}}

// {{{ class UFBF_JobCompany
class UFBF_JobCompany extends UFBF_Text
{
    private $onlymentorfield;

    public function __construct($envfield, $formtext = '', $onlymentorfield = 'only_referent')
    {
        parent::__construct($envfield, $formtext);
        $this->onlymentorfield = $onlymentorfield;
    }

    public function check(UserFilterBuilder &$ufb) {
        if (parent::check($ufb)) {
            # No company check for mentors
           if ($ufb->isOn($this->onlymentorfield)) {
                $this->empty = true;
           }
           return true;
        } else {
            return false;
        }
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Job_Company(UFC_Job_Company::JOBNAME, $this->val);
    }
}
// }}}

// {{{ class UFBF_JobTerms
class UFBF_JobTerms extends UFBF_Index
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Job_Terms($this->val);
    }
}
// }}}

// {{{ class UFBF_JobDescription
class UFBF_JobDescription extends UFBF_Text
{
    private $onlymentorfield;

    public function __construct($envfield, $formtext = '', $onlymentorfield = 'only_referent')
    {
        parent::__construct($envfield, $formtext);
        $this->onlymentorfield = $onlymentorfield;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->isOn($this->onlymentorfield)) {
            return new UFC_Mentor_Expertise($this->val);
        } else {
            return new UFC_Job_Description($this->val, UserFilter::JOB_USERDEFINED);
        }
    }
}
// }}}

// {{{ class UFBF_JobCv
class UFBF_JobCv extends UFBF_Text
{
    private $onlymentorfield;

    public function __construct($envfield, $formtext = '', $onlymentorfield = 'only_referent')
    {
        parent::__construct($envfield, $formtext);
        $this->onlymentorfield = $onlymentorfield;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->isOn($this->onlymentorfield)) {
            return new UFC_Mentor_Expertise($this->val);
        } else {
            return new UFC_Job_Description($this->val, UserFilter::JOB_CV);
        }
    }
}
// }}}

// {{{ class UFBF_Nationality
class UFBF_Nationality extends UFBF_Mixed
{
    protected $direnum = DirEnum::NATIONALITIES;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Nationality($this->val);
    }
}
// }}}

// {{{ class UFBF_Binet
class UFBF_Binet extends UFBF_Mixed
{
    protected $direnum = DirEnum::BINETS;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Binet($this->val);
    }
}
// }}}

// {{{ class UFBF_Group
class UFBF_Group extends UFBF_Mixed
{
    protected $direnum = DirEnum::GROUPESX;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if (count($this->val) == 1) {
            return new UFC_Group($this->val[0]);
        }

        $or = new PFC_Or();
        foreach ($this->val as $grp) {
            $or->addChild(new UFC_Group($grp));
        }
        return $or;
    }
}
// }}}

// {{{ class UFBF_Section
class UFBF_Section extends UFBF_Mixed
{
    protected $direnum = DirEnum::SECTIONS;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Section($this->val);
    }
}
// }}}

// {{{ class UFBF_EducationSchool
class UFBF_EducationSchool extends UFBF_Mixed
{
    protected $direnum = DirEnum::EDUSCHOOLS;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_EducationSchool($this->val);
    }
}
// }}}

// {{{ class UFBF_EducationDegree
class UFBF_EducationDegree extends UFBF_Mixed
{
    protected $direnum = DirEnum::EDUDEGREES;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_EducationDegree($this->val);
    }
}
// }}}

// {{{ class UFBF_EducationField
class UFBF_EducationField extends UFBF_Mixed
{
    protected $direnum = DirEnum::EDUFIELDS;

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_EducationField($this->val);
    }
}
// }}}

// {{{ class UFBF_Comment
class UFBF_Comment extends UFBF_Text
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Comment($this->val);
    }
}
// }}}

// {{{ class UFBF_Phone
class UFBF_Phone extends UFBF_Text
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Phone($this->val);
    }
}
// }}}

// {{{ class UFBF_Networking
class UFBF_Networking extends UFBF_Text
{
    private $networktypefield;
    private $nwtype;

    public function __construct($envfield, $networktypefield, $formtext = '')
    {
        parent::__construct($envfield, $formtext);
        $this->networktypefield  = $networktypefield;
    }

    public function check(UserFilterBuilder &$ufb)
    {
        if (parent::check($ufb)) {
            $this->nwtype = $ufb->i($this->networktypefield);
            return true;
        } else {
            return false;
        }
    }

    public function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Networking($this->nwtype, $this->val);
    }
}
// }}}

// {{{ class UFBF_Mentor
class UFBF_Mentor extends UFBF_Bool
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Mentor();
    }
}
// }}}

// {{{ class UFBF_MentorCountry
class UFBF_MentorCountry extends UFBF_Text
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Mentor_Country($this->val);
    }
}
// }}}

// {{{ class UFBF_Mentorterm
class UFBF_MentorTerm extends UFBF_Index
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Mentor_Terms($this->val);
    }
}
// }}}

// {{{ class UFBF_MentorExpertise
class UFBF_MentorExpertise extends UFBF_Text
{
    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        return new UFC_Mentor_Expertise($this->val);
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

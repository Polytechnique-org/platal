<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

class UserFilterBuilder
{
    private $envprefix;
    private $fields;
    private $valid = true;
    private $ufc = null;

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

    public function isValid()
    {
        $this->buildUFC();
        return $this->valid;
    }

    /** Returns the built UFC
     * @return The UFC, or PFC_False() if an error happened
     */
    public function &getUFC()
    {
        $this->buildUFC();
        if ($this->valid) {
            return $this->ufc;
        } else {
            return new PFC_False();
        }
    }

    /** Wrappers around Env::i/s/..., to add envprefix
     */
    public function s($key, $def) {
        return trim(Env::s($this->envprefix . $key, $def));
    }

    public function i($key, $def) {
        return intval(trim(Env::i($this->envprefix . $key, $def)));
    }

    public function v($key, $def) {
        return Env::v($this->envprefix . $key, $def);
    }

    public function has($key) {
        return Env::has($this->envprefix . $key);
    }
}

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

abstract class UFBF_Text extends UFB_Field
{
    private $forbiddenchars;
    private $minlength;
    private $maxlength;

    public function __construct($envfield, $formtext = '', $forbiddenchars = '', $minlength = 2, $maxlength = 255)
    {
        parent::__construct($envfield, $formtext);
        $this->forbiddenchars = $forbiddenchars;
        $this->minlength      = $minlength;
        $this->maxlength      = $maxlength;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if (!$ufb->has($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->s($this->envfield);
        if (strlen($this->val) < $this->minlength) {
            return $this->raise("Le champ %s est trop court (minimum {$this->minlength}).");
        } else if (strlen($this->val) > $this->maxlength) {
            return $this->raise("Le champ %s est trop long (maximum {$this->maxlength}).");
        }
        return true;
    }
}

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
        if (!$ufb->has($this->envfield)) {
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

/** Subclass to use for indexed fields
 */
abstract class UFBF_Index extends UFB_Field
{
    protected function check(UserFilterBuilder &$ufb)
    {
        if (!$ufb->has($this->envfield)) {
            $this->empty = true;
        }
        return true;
    }
}

/** Subclass to use for fields whose value must belong to a specific set of values
 */
abstract class UFBF_Enum extends UFB_Field
{
    public function __construct($envfield, $formtext = '', $allowedvalues = array())
    {
        parent::__construct($envfield, $formtext);
        $this->allowedvalues = $allowedvalues;
    }

    protected function check(UserFilterBuilder &$ufb)
    {
        if (!$ufb->has($this->envfield)) {
            $this->empty = true;
            return true;
        }

        $this->val = $ufb->v($this->envfield);
        if (! in_array($this->val, $this->allowedvalues)) {
            return $this->raise("La valeur {$this->val} n'est pas valide pour le champ %s.");
        }
        return true;
    }
}

class UFBF_Name extends UFBF_Text
{
    private $type;

    public function __construct($envfield, $type, $formtext = '', $forbiddenchars = '', $minlength = 2, $maxlength = 255)
    {
        parent::__construct($envfield, $formtext, $forbiddenchars, $minlength, $maxlength);
        $this->type = $type;
    }

    protected function buildUFC(UserFilterBuilder &$ufb)
    {
        if ($ufb->i('exact')) {
            return new UFC_Name($this->type, $this->val, UFC_Name::VARIANTS);
        } else {
            return new UFC_Name($this->type, $this->val, UFC_Name::VARIANTS | UFC_Name::CONTAINS);
        }
    }
}

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
        if (!$ufb->has($this->envfield) || !$ufb->has($this->envfieldcomp)) {
            $this->empty = true;
            return true;
        }

        $this->val  = $ubf->i($this->envfield);
        $this->comp = $ubf->v($this->envfieldcomp);

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
        return new UFC_Promo($this->comp, UserFilter::DISPLAY, 'X' . $this->val);
    }
}

?>

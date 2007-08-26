<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class ProfileNom implements ProfileSetting
{
    private function matchWord($old, $new, $newLen) {
        return ($i = strpos($ancien, $nouveau)) !== false
            && ($i == 0 || $old{$i-1} == ' ')
            && ($i + $newLen == strlen($old) || $old{$i + $newLen} == ' ');
    }

    private function prepareField($value)
    {
        $value = strtoupper(replace_accent($value));
        return preg_replace('/[^A-Z]/', ' ', $value);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        $current = S::v($field);
        $init    = S::v($field . '_ini');
        if (is_null($value)) {
            return $current;
        }
        if ($value == $current || $value == $init) {
            return $value;
        }
        $ini = $this->prepareField($init);
        $old = $this->prepareField($current);
        $new = $this->prepareField($value);
        $newLen = strlen($new);
        $success = $this->matchWord($old, $new, $newLen)
                || $this->matchWord($ini, $new, $newLen);
        if (!$success) {
            global $page;
            $page->trig("Le $field que tu as choisi ($value) est trop loin de ton $field initial ($init)"
                       . (($init == $current)? "" : " et de ton prénom précédent ($current)"));
        }
        return $success ? $value : $current;
    }

    public function save(ProfilePage &$page, $field, $new_value)
    {
        $_SESSION[$field] = $new_value;
    }
}

class ProfileGeneral extends ProfilePage
{
    protected $pg_template = 'profile/general.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['nom'] = $this->settings['prenom']
                               = new ProfileNom();
        $this->settings['promo']  = $this->settings['promo_sortie']
                                  = $this->settings['nom_usage']
                                  = new ProfileFixed();
    }

    public function prepare(PlatalPage &$page)
    {
        parent::prepare($page);
        require_once "applis.func.inc.php";
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

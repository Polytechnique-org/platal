<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

// {{{ class OrangeReq

class OrangeReq extends Validate
{
    // {{{ properties

    public $unique = true;

    public $promo;
    public $promo_sortie;

    public $rules = "A priori accepter (la validation sert à repousser les
    petits malins). Refuse si tu connais la personne et que tu es sure
    qu'elle n'est pas orange.";

    // }}}
    // {{{ constructor

    public function __construct($_uid, $_sortie)
    {
        parent::__construct($_uid, true, 'orange');
        $this->promo_sortie  = $_sortie;
        $res = XDB::query("SELECT promo FROM auth_user_md5 WHERE user_id = {?}", $_uid);
        $this->promo = $res->fetchOneCell();
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.orange.tpl';
    }

    // }}}
    // {{{ function _mail_subj()

    protected function _mail_subj()
    {
        return "[Polytechnique.org/ORANGE] Changement de promo de sortie";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            $res = "  La demande de changement de promo de sortie que tu as demandée vient d'être effectuée.";
            return $res;
        } else {
            return "  La demande de changement de promo de sortie tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        XDB::execute("UPDATE auth_user_md5 set promo_sortie={?} WHERE user_id={?}",$this->promo_sortie ,$this->uid);
        return true;
    }

    // }}}
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

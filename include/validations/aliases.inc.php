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

// {{{ class AliasReq

class AliasReq extends Validate
{
    // {{{ properties

    public $alias;
    public $raison;
    public $unique = true;

    public $old='';
    public $public='private';

    public $rules = "Interdire ce qui peut nous servir (virus@, postmaster@, ...),
                  les alias vulgaires, et les prenom.nom (sauf si c'est pour l'utilisateur prenom.nom).
                  Pas de contrainte pour les tirets ou les points, en revanche le souligné (_) est interdit";

    // }}}
    // {{{ constructor

    public function __construct(User &$_user, $_alias, $_raison, $_public, $_stamp=0)
    {
        global $globals;
        parent::__construct($_user, true, 'alias', $_stamp);
        $this->alias  = $_alias.'@'.$globals->mail->alias_dom;
        $this->raison = $_raison;
        $this->public = $_public;

        $res = XDB::query("
                SELECT  v.alias
                  FROM  virtual_redirect AS vr
            INNER JOIN  virtual          AS v  ON (v.vid=vr.vid AND v.alias LIKE '%@{$globals->mail->alias_dom}')
                 WHERE  vr.redirect = {?} OR vr.redirect = {?}",
                $this->user->forlifeEmail(),
                // TODO: remove this über-ugly hack. The issue is that you need
                // to remove all @m4x.org addresses in virtual_redirect first.
                $this->user->login() . '@' . $globals->mail->domain2);
        $this->old = $res->fetchOneCell();
        if (empty($this->old)) {
            unset($this->old);
        }
    }

    // }}}
    // {{{ function get_request()

    static public function get_request($uid)
    {
        return parent::get_typed_request($uid, 'alias');
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.aliases.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/MELIX] Demande de l'alias {$this->alias}";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  L'adresse email {$this->alias} que tu avais demandée vient d'être créée, tu peux désormais l'utiliser à ta convenance.".(($this->public == 'public')?" A ta demande, cette adresse apparaît maintenant sur ta fiche.":"");
        } else {
            return "  La demande que tu avais faite pour l'alias {$this->alias} a été refusée.";
        }
    }

    // }}}
    // {{{ function shorter_domain

    private function shorter_domain()
    {
        global $globals;

        $mail = $globals->mail;

        if (empty($mail->domain2) || strlen($mail->domain2) > strlen($mail->domain)) {
            return $mail->domain;
        } else {
            return $mail->domain2;
        }
    }

    // }}}
    // {{{ function commit()

    public function commit ()
    {
        XDB::execute("UPDATE auth_user_quick SET emails_alias_pub = {?} WHERE user_id = {?}",
                     $this->public, $this->user->id());

        if ($this->old) {
            return XDB::execute("UPDATE virtual SET alias = {?} WHERE alias = {?}",
                                $this->alias, $this->old);
        } else {
            XDB::execute("INSERT INTO virtual SET alias = {?},type='user'", $this->alias);
            $vid = XDB::insertId();
            return XDB::query("INSERT INTO virtual_redirect (vid,redirect) VALUES ({?}, {?})",
                              $vid, $this->user->forlifeEmail());
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

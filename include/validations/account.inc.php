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


class AccountReq extends Validate
{
    // {{{ properties

    public $uid;
    public $hruid;
    public $email;
    public $group;
    public $groups;

    public $rules = "Accepter si l'adresse email parait correcte, et pas absurde
        (ou si le demandeur est de confiance). Si le demandeur marque sa propre
        adresse email, refuser dans tous les cas. Sauf abus flagrant, il n'y a
        pas de raison de refuser des marketing perso répétés.";
    // }}}
    // {{{ constructor

    public function __construct(User $user, $hruid, $email, $group)
    {
        parent::__construct($user, false, 'account');
        $this->hruid = $hruid;
        $this->email = $email;
        $this->group = $group;
        $this->uid = XDB::fetchOneCell('SELECT  uid
                                          FROM  accounts
                                         WHERE  hruid = {?}',
                                       $hruid);
        $this->groups = implode(',', XDB::fetchColumn('SELECT  g.nom
                                                         FROM  groups AS g
                                                   INNER JOIN  group_members AS m ON (g.id = m.asso_id)
                                                        WHERE  m.uid = {?}
                                                     ORDER BY  g.nom',
                                                      $this->uid));
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.account.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org] Création d'un compte Polytechnique.net";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Un email vient d'être envoyé à {$this->email} pour qu'il puisse activer son compte sur Polytechnique.net.";
        } else {
            return "  Nous n'avons pas jugé bon d'envoyer d'email à {$this->email} pour qu'il puisse activer son compte sur Polytechnique.net.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $hash = rand_url_id(12);
        XDB::execute('INSERT INTO  register_pending_xnet (uid, hruid, email, date, hash, sender_name, group_name)
                           VALUES  ({?}, {?}, {?}, NOW(), {?}, {?}, {?})',
                     $this->uid, $this->hruid, $this->email, $hash, $this->user->fullName(), $this->group);

        return true;
    }

    // }}}
    // {{{ function isPending()

    static public function isPending($uid)
    {
        $res = XDB::iterRow('SELECT  data
                               FROM  requests
                              WHERE  type = \'account\'
                           ORDER BY  stamp');

        while (list($data) = $res->next()) {
            $request = Validate::unserialize($data);
            if ($request->uid == $uid) {
                return true;
            }
        }
        return false;
    }

    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

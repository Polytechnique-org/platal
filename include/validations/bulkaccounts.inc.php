<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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


class BulkAccountsReq extends Validate
{
    // {{{ properties

    private $limit = 50;
    public $users;
    public $group;
    public $dim;

    public $rules = "Accepter si les adresses email paraissent correctes, et pas
        absurdes et si le demandeur est de confiance.";
    // }}}
    // {{{ constructor

    public function __construct(User $user, array $uids, $group, $dim)
    {
        parent::__construct($user, false, 'bulkaccounts');
        $this->group = $group;
        $this->dim   = $dim;
        $this->users = XDB::fetchAllAssoc('SELECT  uid, hruid, email
                                             FROM  accounts
                                            WHERE  uid IN {?}',
                                          $uids);
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.bulk_accounts.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org] Création de comptes Polytechnique.net";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Un email vient d'être envoyé aux personnes concernées pour qu'elles puissent activer leur compte sur Polytechnique.net.";
        } else {
            return "  Nous n'avons pas jugé bon d'activer les comptes Polytechnique.net demandés.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $values = array();
        $i = 0;
        foreach ($this->users as $user) {
            $values[] = XDB::format('({?}, {?}, {?}, NOW(), {?}, {?}, {?})',
                                    $user['uid'], $user['hruid'], $user['email'], rand_url_id(12), $this->user->fullName(), $this->group);

            if ($i == $this->limit) {
                XDB::rawExecute('INSERT INTO  register_pending_xnet (uid, hruid, email, date, hash, sender_name, group_name)
                                      VALUES  ' . implode(', ', $values));
                $i = 0;
                $values = array();
            } else {
                ++$i;
            }
        }
        XDB::rawExecute('INSERT INTO  register_pending_xnet (uid, hruid, email, date, hash, sender_name, group_name)
                              VALUES  ' . implode(', ', $values));

        return true;
    }

    // }}}
    // {{{ function isPending()

    static public function isPending($uid)
    {
        $res = XDB::iterRow('SELECT  data
                               FROM  requests
                              WHERE  type = \'bulk_accounts\'
                           ORDER BY  stamp');

        while (list($data) = $res->next()) {
            $request = Validate::unserialize($data);
            foreach ($request->users as $user) {
                if ($user['uid'] == $uid) {
                    return true;
                }
            }
        }
        return false;
    }

    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

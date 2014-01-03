<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

// {{{ class NamesReq

class NamesReq extends ProfileValidate
{
    // {{{ properties

    public $unique = true;
    public $public_names;
    public $old_public_names;
    public $old_alias = null;
    public $new_alias = null;
    public $descriptions = array('lastname_main' => 'Nom patronymique', 'lastname_marital' => 'Nom marital', 'lastname_ordinary' => 'Nom usuel', 'firstname_main' => 'Prénom', 'firstname_ordinary' => 'Prénom usuel', 'pseudonym' => 'Pseudonyme (nom de plume)');
    public $rules = "Refuser tout ce qui n'est visiblement pas un nom de famille (ce qui est extremement rare car à peu près n'importe quoi peut être un nom de famille).";

    // }}}
    // {{{ constructor

    public function __construct(User $_user, Profile $_profile, array $_public_names, array $_old_public_names)
    {
        parent::__construct($_user, $_profile, true, 'usage');

        $this->public_names = $_public_names;
        $this->old_public_names = $_old_public_names;

        if (!is_null($this->profileOwner)) {
            require_once 'name.func.inc.php';

            $this->new_alias = build_email_alias($this->public_names);
            $this->old_alias = XDB::fetchOneCell('SELECT  email
                                                    FROM  email_source_account
                                                   WHERE  uid = {?} AND type = \'alias\' AND FIND_IN_SET(\'usage\', flags)',
                                                 $this->profileOwner->id());

            if ($this->old_alias == $this->new_alias) {
                $this->old_alias = $this->new_alias = null;
            } else {
                $used = XDB::fetchOneCell('SELECT  COUNT(uid)
                                             FROM  email_source_account
                                            WHERE  email = {?} AND type != \'alias_aux\'',
                                          $this->new_alias);
                if (!$used) {
                    // Check against homonyms
                    $used = XDB::fetchOneCell('SELECT  COUNT(email)
                                                 FROM  email_source_other
                                                WHERE  email = {?}',
                                              $this->new_alias);
                }
                if ($used) {
                    $this->new_alias = null;
                }
            }
        }
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.names.tpl';
    }

    // }}}
    // {{{ function _mail_subj()

    protected function _mail_subj()
    {
        return "[Polytechnique.org/NOMS] Changement de noms";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            $res = "  Le changement de nom que tu as demandé vient d'être effectué.";
            if (!is_null($this->profileOwner)) {
                if ($this->old_alias != $this->new_alias) {
                    if ($this->old_alias) {
                        $res .= "\n\n  L'alias {$this->old_alias}@{$this->mail_domain} a été supprimé.";
                    }
                    if ($this->new_alias) {
                        $res .= "\n\n  L'alias {$this->new_alias}@{$this->mail_domain} est maintenant à ta disposition !";
                    }
                }
                if ($globals->mailstorage->googleapps_domain) {
                    require_once 'googleapps.inc.php';
                    $account = new GoogleAppsAccount($this->profileOwner);
                    if ($account->active()) {
                        $res .= "\n\n  Si tu utilises Google Apps, tu peux changer ton nom d'usage sur https://mail.google.com/a/polytechnique.org/#settings/accounts.";
                    }
                }
            }
            return $res;
        } else {
            return "  La demande de changement de nom que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        require_once 'name.func.inc.php';

        update_public_names($this->profile->id(), $this->public_names);
        update_display_names($this->profile, $this->public_names);

        if (!is_null($this->profileOwner)) {
            if (!is_null($this->old_alias)) {
                XDB::execute('DELETE FROM  email_source_account
                                    WHERE  FIND_IN_SET(\'usage\', flags) AND uid = {?} AND type = \'alias\'',
                             $this->profileOwner->id());
            }
            if (!is_null($this->new_alias)) {
                XDB::execute('INSERT INTO  email_source_account (email, uid, type, flags, domain)
                                   SELECT  {?}, {?}, \'alias\', \'usage\', id
                                     FROM  email_virtual_domains
                                    WHERE  name = {?}',
                             $this->new_alias, $this->profileOwner->id(), $this->profileOwner->mainEmailDomain());
            }
            require_once 'emails.inc.php';
            fix_bestalias($this->profileOwner);

            // Update the local User object, to pick up the new bestalias.
            $this->profileOwner = User::getSilentWithUID($this->profileOwner->id());
        }

        return true;
    }

    // }}}
    // {{{ function getPublicNames()

    static public function getPublicNames($pid)
    {
        if ($request = parent::get_typed_request($pid, 'usage')) {
            return $request->public_names;
        }
        return false;
    }

    // }}}

}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

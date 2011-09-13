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

// class AliasReq {{{1
class AliasReq extends Validate
{
    // properties {{{2
    public $alias;
    public $reason;
    public $unique = true;

    public $old;
    public $public = 'private';

    public $rules = "Interdire ce qui peut nous servir (virus@, postmaster@&hellip;),
                  les alias vulgaires, et les prenom.nom (sauf si c'est pour l'utilisateur prenom.nom).
                  Pas de contrainte pour les tirets ou les points, en revanche le souligné (_) est interdit.";

    // constructor {{{2
    public function __construct(User $_user, $_alias, $_reason, $_public, $_old, $_stamp = 0)
    {
        global $globals;
        parent::__construct($_user, true, 'alias', $_stamp);
        $this->alias  = $_alias;
        $this->reason = $_reason;
        $this->public = $_public;
        $this->old    = $_old;
    }

    // function get_request() {{{2
    static public function get_request($uid)
    {
        return parent::get_typed_request($uid, 'alias');
    }

    // function formu() {{{2
    public function formu()
    {
        return 'include/form.valid.aliases.tpl';
    }

    // function _mail_subj {{{2
    protected function _mail_subj()
    {
        global $globals;
        return "[Polytechnique.org/MELIX] Demande de l'alias {$this->alias}@{$globals->mail->alias_dom}";
    }

    // function _mail_body {{{2
    protected function _mail_body($isok)
    {
        global $globals;
        if ($isok) {
            return "  L'adresse email {$this->alias}@{$globals->mail->alias_dom} que tu avais demandée vient d'être créée, tu peux désormais l'utiliser à ta convenance."
                 . ($this->public == 'public' ? ' À ta demande, cette adresse apparaît maintenant sur ta fiche.' : '');
        } else {
            return "  La demande que tu avais faite pour l'alias {$this->alias}@{$globals->mail->alias_dom} a été refusée.";
        }
    }

    // function commit() {{{2
    public function commit()
    {
        if ($this->user->hasProfile()) {
            XDB::execute('UPDATE  profiles
                             SET  alias_pub = {?}
                           WHERE  pid = {?}',
                         $this->public, $this->user->profile()->id());
        }

        if ($this->old) {
            $success = XDB::execute('UPDATE  email_source_account
                                        SET  email = {?}
                                      WHERE  uid = {?} AND type = \'alias_aux\'',
                                    $this->alias, $this->user->id());
        } else {
            $success = XDB::execute('INSERT INTO  email_source_account (email, uid, domain, type, flags)
                                          SELECT  {?}, {?}, id, \'alias_aux\', \'\'
                                            FROM  email_virtual_domains
                                           WHERE  name = {?}',
                                     $this->alias, $this->user->id(), Platal::globals()->mail->alias_dom);
        }

        if ($success) {
            // Update the local User object, to pick up the new bestalias.
            require_once 'emails.inc.php';
            fix_bestalias($this->user);
            $this->user = User::getSilentWithUID($this->user->id());
        }

        return $success;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

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

// class AliasReq {{{1
class AliasReq extends Validate
{
    // properties {{{2
    public $alias;
    public $raison;
    public $unique = true;

    public $old    = '';
    public $public = 'private';

    public $rules = "Interdire ce qui peut nous servir (virus@, postmaster@&hellip;),
                  les alias vulgaires, et les prenom.nom (sauf si c'est pour l'utilisateur prenom.nom).
                  Pas de contrainte pour les tirets ou les points, en revanche le souligné (_) est interdit.";

    // constructor {{{2
    public function __construct(User &$_user, $_alias, $_raison, $_public, $_stamp=0)
    {
        global $globals;
        parent::__construct($_user, true, 'alias', $_stamp);
        $this->alias  = $_alias.'@'.$globals->mail->alias_dom;
        $this->raison = $_raison;
        $this->public = $_public;
        $this->old    = $user->emailAlias();
        if (empty($this->old)) {
            unset($this->old);
        }
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
        return "[Polytechnique.org/MELIX] Demande de l'alias {$this->alias}";
    }

    // function _mail_body {{{2
    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  L'adresse email {$this->alias} que tu avais demandée vient d'être créée, tu peux désormais l'utiliser à ta convenance."
                 . ($this->public == 'public' ? ' À ta demande, cette adresse apparaît maintenant sur ta fiche.' : '');
        } else {
            return "  La demande que tu avais faite pour l'alias {$this->alias} a été refusée.";
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
            return XDB::execute('UPDATE  virtual
                                    SET  alias = {?}
                                  WHERE  alias = {?}',
                                $this->alias, $this->old);
        } else {
            XDB::execute('INSERT INTO  virtual
                                  SET  alias = {?}, type=\'user\'',
                         $this->alias);
            $vid = XDB::insertId();
            return XDB::execute('INSERT INTO  virtual_redirect (vid, redirect)
                                      VALUES  ({?}, {?})',
                                $vid, $this->user->forlifeEmail());
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

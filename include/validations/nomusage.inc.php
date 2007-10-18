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

// {{{ class UsageReq

class UsageReq extends Validate
{
    // {{{ properties

    public $unique = true;

    public $nom_usage;
    public $alias = '';

    public $oldusage;
    public $oldalias;

    public $homonyme;
    public $reason;

    public $rules = "Refuser
    tout ce qui n'est visiblement pas un nom de famille (ce qui est
    extremement rare car à peu près n'importe quoi peut être un nom de
    famille...)";

    // }}}
    // {{{ constructor

    public function __construct($_uid, $_usage, $_reason)
    {
        parent::__construct($_uid, true, 'usage');
        $this->nom_usage  = $_usage;
        $this->reason = $_reason;
        require_once 'xorg.misc.inc.php';
        $this->alias   = make_username($this->prenom, $this->nom_usage);
        if (!$this->nom_usage) $this->alias = "";

        $res = XDB::query("
                SELECT  e.alias, u.nom_usage, a.id
                  FROM  auth_user_md5 as u
             LEFT JOIN  aliases       as e ON(e.type='alias' AND FIND_IN_SET('usage',e.flags) AND e.id = u.user_id)
             LEFT JOIN  aliases       as a ON(a.alias = {?} AND a.id != u.user_id)
                 WHERE  u.user_id = {?}", $this->alias, $this->uid);
        list($this->oldalias, $this->oldusage, $this->homonyme) = $res->fetchOneRow();
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.nomusage.tpl';
    }

    // }}}
    // {{{ function _mail_subj()

    protected function _mail_subj()
    {
        return "[Polytechnique.org/USAGE] Changement de nom d'usage";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        global $globals;
        if ($isok) {
            $res = "  Le changement de nom d'usage que tu as demandé vient d'être effectué.";
            if ($this->oldalias) {
                $res .= "\n\n  Les alias {$this->oldalias}@{$globals->mail->domain} et @{$globals->mail->domain2} ont été supprimés.";
            }
            if ($this->nom_usage) {
                $res .= "\n\n  Les alias {$this->alias}@{$globals->mail->domain} et @{$globals->mail->domain2} sont maintenant à ta disposition !";
            }
            return $res;
        } else {
            return "  La demande de changement de nom d'usage que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        require_once 'notifs.inc.php';
        register_watch_op($this->uid, WATCH_FICHE, 'nom');
        require_once('user.func.inc.php');
        $this->bestalias = set_new_usage($this->uid, $this->nom_usage, $this->alias);
        return true;
    }

    // }}}
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

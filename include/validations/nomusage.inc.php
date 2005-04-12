<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

    var $unique = true;

    var $nom_usage;
    var $alias = '';

    var $oldusage;
    var $oldalias;

    var $homonyme;
    
    var $rules = "Refuser 
    tout ce qui n'est visiblement pas un nom de famille (ce qui est 
    extremement rare car à peu près n'importe quoi peut être un nom de 
    famille...)";

    // }}}
    // {{{ constructor

    function UsageReq($_uid, $_usage)
    {
        global $globals;
        $this->Validate($_uid, true, 'usage');
        $this->nom_usage  = $_usage;
        $this->alias   = make_username($this->prenom, $this->nom_usage);
        if (!$this->nom_usage) $this->alias = "";

        $res = $globals->xdb->query("
                SELECT  e.alias, u.nom_usage, a.id
                  FROM  auth_user_md5 as u
             LEFT JOIN  aliases       as e ON(e.type='alias' AND FIND_IN_SET('usage',e.flags) AND e.id = u.user_id)
             LEFT JOIN  aliases       as a ON(a.alias = {?} AND a.id != u.user_id)
                 WHERE  u.user_id = {?}", $this->alias, $this->uid);
        list($this->oldalias, $this->oldusage, $this->homonyme) = $res->fetchOneRow();
    }

    // }}}
    // {{{ function get_request()

    function get_request($uid)
    {
        return parent::get_request($uid,'usage');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.nomusage.tpl'; }

    // }}}
    // {{{ function _mail_subj()

    function _mail_subj()
    {
        return "[Polytechnique.org/USAGE] Changement de nom d'usage";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        global $globals;
        if ($isok) {
            $res = "  La demande de changement de nom d'usage que tu as demandée vient d'être effectuée.";
            if ($this->oldalias) {
                $res .= "\n\n  Les alias {$this->oldalias}@{$globals->mail->domain} et @{$globals->mail->domain2} ont été supprimés.";
            }
            if ($nom_usage) {
                $res .= "\n\n  Les alias {$this->alias}@{$globals->mail->domain} et @{$globals->mail->domain2} sont maintenant à ta disposition !";
            }
            return $res;
        } else {
            return "  La demande de changement de nom d'usage que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        global $globals;

        $globals->xdb->execute("UPDATE auth_user_md5 set nom_usage={?} WHERE user_id={?}",$this->nom_usage ,$this->uid);
        $globals->xdb->execute("DELETE FROM aliases WHERE FIND_IN_SET('usage',flags) AND id={?}", $this->uid);
        if ($this->alias) {
            $globals->xdb->execute("UPDATE aliases SET flags=flags & 255-1 WHERE id={?}", $this->uid);
            $globals->xdb->execute("INSERT INTO aliases VALUES({?}, 'alias', 'usage,bestalias', {?}, null)",
                $this->alias, $this->uid);
        }
        $r = $globals->xdb->query("SELECT alias FROM aliases WHERE FIND_IN_SET('bestalias', flags) AND id = {?}", $this->uid);
        if ($r->fetchOneCell() == "") {
            $globals->xdb->execute("UPDATE aliases SET flags = 1 | flags WHERE id = {?} LIMIT 1", $this->uid);
        }
        require_once 'user.func.inc.php';
        user_reindex($this->uid);
        return true;
    }

    // }}}
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

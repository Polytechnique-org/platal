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

// {{{ class EpouseReq

class EpouseReq extends Validate
{
    // {{{ properties

    var $unique = true;

    var $epouse;
    var $alias = '';

    var $oldepouse;
    var $oldalias;

    var $homonyme;
    
    var $rules = "Refuser 
    tout ce qui n'est visiblement pas un nom de famille (ce qui est 
    extrmement rare car à peu près n'importe quoi peut être un nom de 
    famille...)";

    // }}}
    // {{{ constructor

    function EpouseReq($_uid, $_epouse)
    {
        global $globals;
        $this->Validate($_uid, true, 'epouse');
        $this->epouse  = $_epouse;
        $this->alias   = make_username($this->prenom, $this->epouse);

        $res = $globals->xdb->query("
                SELECT  e.alias, u.epouse, a.id
                  FROM  auth_user_md5 as u
             LEFT JOIN  aliases       as e ON(e.type='alias' AND FIND_IN_SET('epouse',e.flags) AND e.id = u.user_id)
             LEFT JOIN  aliases       as a ON(a.alias = {?} AND a.id != u.user_id)
                 WHERE  u.user_id = {?}", $this->alias, $this->uid);
        list($this->oldalias, $this->oldepouse, $this->homonyme) = $res->fetchOneRow();
    }

    // }}}
    // {{{ function get_request()

    function get_request($uid)
    {
        return parent::get_request($uid,'epouse');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.epouses.tpl'; }

    // }}}
    // {{{ function _mail_subj()

    function _mail_subj()
    {
        return "[Polytechnique.org/EPOUSE] Changement de nom de mariage";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        global $globals;
        if ($isok) {
            $res = "  La demande de changement de nom de mariage que tu as demandée vient d'être effectuée.";
            if ($this->oldalias) {
                $res .= "\n\n  Les alias {$this->oldalias}@{$globals->mail->domain} et @{$globals->mail->domain2} ont été supprimés.";
            }
            $res .= "\n\n  Les alias {$this->alias}@{$globals->mail->domain} et @{$globals->mail->domain2} sont maintenant à ta disposition !";
            return $res;
        } else {
            return "  La demande de changement de nom de mariage que tu avais faite a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        global $globals;

        $globals->xdb->execute("UPDATE auth_user_md5 set epouse={?} WHERE user_id={?}",$this->epouse ,$this->uid);
        $globals->xdb->execute("DELETE FROM aliases WHERE FIND_IN_SET('epouse',flags) AND id={?}", $this->uid);
        $globals->xdb->execute("UPDATE aliases SET flags='' WHERE flags='bestalias' AND id={?}", $this->uid);
        $globals->xdb->execute("INSERT INTO aliases VALUES({?}, 'alias', 'epouse,bestalias', {?}, null)",
                $this->alias, $this->uid);
        $f = fopen("/tmp/flag_recherche","w");
        fputs($f,"1");
        fclose($f);
        return true;
    }

    // }}}
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

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

    var $epouse;
    var $alias = '';

    var $oldepouse;
    var $oldalias;

    var $homonyme;

    // }}}
    // {{{ constructor

    function EpouseReq($_uid, $_forlife, $_epouse, $_stamp=0)
    {
        global $globals;
        $this->Validate($_uid, true, 'epouse', $_stamp);
        $this->epouse  = $_epouse;
        $this->forlife = $_forlife;
        list($prenom)  = explode('.',$_forlife);
        $this->alias   = make_username($prenom, $this->epouse);

        $sql = $globals->xdb->query("
                SELECT  e.alias, u.epouse, a.id
                  FROM  auth_user_md5 as u
             LEFT JOIN  aliases       as e ON(e.type='alias' AND FIND_IN_SET('epouse',e.flags) AND e.id = u.user_id)
             LEFT JOIN  aliases       as a ON(a.alias = {?} AND a.id != u.user_id)
                 WHERE  u.user_id = {?}", $this->alias, $this->uid);
        list($this->oldalias, $this->oldepouse, $this->homonyme) = $res->fetchOneRow();
    }

    // }}}
    // {{{ function get_unique_request()

    function get_unique_request($uid)
    {
        return parent::get_unique_request($uid,'epouse');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.epouses.tpl'; }

    // }}}
    // {{{ function handle_formu()

    function handle_formu()
    {
        if (Env::get('submit') != "Accepter" && Env::get('submit') != "Refuser") {
            return false;
        }

        require_once("xorg.mailer.inc.php");
        $mymail = new XOrgMailer('valid.epouses.tpl');
        $mymail->assign('forlife', $this->forlife);

        if (Env::get('submit') == "Accepter") {
            $mymail->assign('answer','yes');
            if ($this->oldepouse) {
                $mymail->assign('oldepouse',$this->oldalias);
            }
            $mymail->assign('epouse',$this->alias);
            $this->commit();
        } else { // c'était donc Refuser
            $mymail->assign('answer','no');
        }

        $mymail->send();

        $this->clean();
        return "Mail envoyé";
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
    }

    // }}}
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

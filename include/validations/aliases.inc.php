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

// {{{ class AliasReq

class AliasReq extends Validate
{
    // {{{ properties

    var $alias;
    var $raison;

    var $forlife;
    var $bestalias;
    var $prenom;
    var $nom;
    var $old='';

    // }}}
    // {{{ constructor

    function AliasReq ($_uid, $_alias, $_raison, $_stamp=0)
    {
        global $globals;
        $this->Validate($_uid, true, 'alias', $_stamp);
        $this->alias = $_alias;
        $this->raison = $_raison;

        $sql = $globals->db->query("
                SELECT  l.alias,m.alias,prenom,nom
                  FROM  auth_user_md5    AS u
            INNER JOIN  aliases          AS l  ON (u.user_id=l.id AND l.type='a_vie')
            INNER JOIN  aliases          AS m  ON (u.user_id=m.id AND FIND_IN_SET('bestalias',m.flags))
                 WHERE  user_id='".$this->uid."'");
        list($this->forlife,$this->bestalias,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);

        $sql = $globals->db->query("
                SELECT  v.alias
                  FROM  virtual_redirect AS vr
            INNER JOIN  virtual          AS v  ON (v.vid=vr.vid AND v.alias LIKE '%@melix.net')
                 WHERE  vr.redirect='{$this->forlife}@m4x.org'");
        if (mysql_num_rows($sql)) {
            list($this->old) = mysql_fetch_row($sql);
        }
        mysql_free_result($sql);
    }

    // }}}
    // {{{ function get_unique_request()

    function get_unique_request($uid)
    {
        return parent::get_unique_request($uid,'alias');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.aliases.tpl'; }

    // }}}
    // {{{ function handle_formu()

    function handle_formu()
    {
        if (empty($_REQUEST['submit']) || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser")) {
            return false;
        }

        require_once("xorg.mailer.inc.php");
        $mymail = new XOrgMailer('valid.alias.tpl');
        $mymail->assign('alias', $this->alias);
        $mymail->assign('bestalias', $this->bestalias);

        if ($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer', 'yes');
            $this->commit() ; 
        } else {
            $mymail->assign('answer', 'no');
            $mymail->assign('motif', stripslashes($_REQUEST['motif']));
        }
        $mymail->send();
        //Suppression de la demande
        $this->clean();
        return "Mail envoyé";
    }

    // }}}
    // {{{ function commit()

    function commit ()
    {
        global $globals;

        if ($this->old) {
            $globals->db->query("UPDATE virtual SET alias='{$this->alias}@melix.net' WHERE alias='{$this->old}'");

        } else {
            $globals->db->query("INSERT INTO virtual SET alias='{$this->alias}@melix.net',type='user'");
            $vid = mysql_insert_id();
            $globals->db->query("INSERT INTO virtual_redirect (vid,redirect) VALUES ($vid,'{$this->forlife}@m4x.org')");
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

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
 ***************************************************************************
    $Id: listes.inc.php,v 1.1 2004-11-22 07:24:56 x2000habouzit Exp $
 ***************************************************************************/

// {{{ class ListeReq

class ListeReq extends Validate
{
    // {{{ properties
    
    var $bestalias;
    var $liste;
    var $desc;

    var $advertise;
    var $modlevel;
    var $inslevel;

    var $owners;
    var $members;

    // }}}
    // {{{ constructor
    
    function ListeReq($_uid, $_liste, $_desc, $_advertise, $_modlevel, $_inslevel, $_owners, $_members, $_stamp=0)
    {
        global $globals;
        $this->Validate($_uid, true, 'liste', $_stamp);
        $this->liste = $_liste;
        $this->desc = $_desc;

        $this->advertise = $_advertise;
        $this->modlevel = $_modlevel;
        $this->inslevel = $_inslevel;
        
        $this->owners = $_owners;
        $this->members = $_members;
        
        $sql = $globals->db->query("
                SELECT  l.alias
                  FROM  auth_user_md5   AS u
            INNER JOIN  aliases         AS l ON (u.user_id=l.id AND FIND_IN_SET('bestalias',l.flags))
                 WHERE  user_id='".$this->uid."'");
        list($this->bestalias) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    // }}}
    // {{{ function get_unique_request()

    function get_unique_request($uid)
    {
        return parent::get_unique_request($uid,'liste');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.listes.tpl'; }

    // }}}
    // {{{ function handle_formu()

    function handle_formu()
    {
        if (empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser"))
        {
            return false;
        }

        require_once("tpl.mailer.inc.php");
        $mymail = new TplMailer('valid.liste.tpl');
        $mymail->assign('alias', $this->liste);
        $mymail->assign('bestalias', $this->bestalias);
        $mymail->assign('motif', stripslashes($_REQUEST['motif']));

        if ($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer', 'yes');
            if (!$this->commit()) {
                return 'problème';
            }
        } else {
            $mymail->assign('answer', 'no');
        }
        $mymail->send();

        //Suppression de la demande
        $this->clean();
        return "Mail envoyé";
    }

    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        include('xml-rpc-client.inc.php');
        $res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
        list($pass) = mysql_fetch_row($res);
        mysql_free_result($res);

        $client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949/polytechnique.org");
        $ret = $client->create_list($this->liste, $this->desc,
            $this->advertise, $this->modlevel, $this->inslevel,
            $this->owners, $this->members);
        $liste = strtolower($this->liste);
        if ($ret) {
            $globals->db->query("INSERT INTO aliases (alias,type) VALUES('{$liste}', 'liste')");
            $globals->db->query("INSERT INTO aliases (alias,type) VALUES('{$liste}-owner', 'liste')");
            $globals->db->query("INSERT INTO aliases (alias,type) VALUES('{$liste}-admin', 'liste')");
            $globals->db->query("INSERT INTO aliases (alias,type) VALUES('{$liste}-bounces', 'liste')");
        }
        return $ret;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

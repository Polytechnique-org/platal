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
        $Id: valid_aliases.inc.php,v 1.16 2004-09-05 12:24:41 x2000habouzit Exp $
 ***************************************************************************/

class AliasReq extends Validate {
    var $alias;
    var $raison;

    var $forlife;
    var $prenom;
    var $nom;
    var $old;
    
    function AliasReq ($_uid, $_alias, $_raison, $_stamp=0) {
        global $globals;
        $this->Validate($_uid, true, 'alias', $_stamp);
        $this->alias = $_alias;
        $this->raison = $_raison;
        
        $sql = $globals->db->query("
	    SELECT  l.alias,prenom,nom,domain
	      FROM  auth_user_md5   AS u
	INNER JOIN  aliases         AS l ON (u.user_id=l.id AND type='a_vie')
         LEFT JOIN  groupex.aliases AS a ON (a.email = l.alias and a.id = 12)
             WHERE  user_id='".$this->uid."'");
        list($this->forlife,$this->prenom,$this->nom,$this->old) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    function get_unique_request($uid) {
        return parent::get_unique_request($uid,'alias');
    }

    function formu() { return 'include/form.valid.aliases.tpl'; }

    function handle_formu () {
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter"    && $_REQUEST['submit']!="Refuser"))
            return false;

        require_once("tpl.mailer.inc.php");
        $mymail = new TplMailer('valid.alias.tpl');
        $mymail->assign('alias', $this->alias);
        $mymail->assign('forlife', $this->forlife);

        if($_REQUEST['submit']=="Accepter") {
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

    function commit () {
        global $globals;

        $domain=$this->alias.'@melix.net';
        $globals->db->query("DELETE FROM groupex.aliases WHERE id=12 AND email='{$this->forlife}'");
        $globals->db->query("INSERT INTO groupex.aliases SET email='{$this->forlife}',domain='$domain',id=12");         
    }
}

?>

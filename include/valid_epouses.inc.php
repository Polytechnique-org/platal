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
        $Id: valid_epouses.inc.php,v 1.14 2004-09-01 21:33:08 x2000habouzit Exp $
 ***************************************************************************/


class EpouseReq extends Validate {
    var $epouse;
    var $alias;
    var $username;

    var $oldepouse;
    var $oldalias;
    var $prenom;
    var $nom;

    var $homonyme;
    
    function EpouseReq ($_uid, $_username, $_epouse, $_stamp=0) {
        global $globals;
        $this->Validate($_uid, true, 'epouse', $_stamp);
        $this->epouse = $_epouse;
        $this->username = $_username;
        
        list($prenom) = explode('.',$_username);
        $this->alias = make_username($prenom,$this->epouse);
        if(empty($_epouse))
            $this->alias = "<span class=\"erreur\">suppression</a>";
        
        $sql = $globals->db->query("select u1.alias, u1.epouse, u1.prenom, u1.nom"
            .", IFNULL(u2.username,u3.username)"
            ." FROM auth_user_md5 as u1"
            ." LEFT JOIN auth_user_md5 as u2"
                ." ON(u2.username = '{$this->alias}' and u2.user_id != u1.user_id)"
            ." LEFT JOIN auth_user_md5 as u3"
                ." ON(u3.alias = '{$this->alias}' and u3.user_id != u1.user_id)"
            ." WHERE u1.user_id = ".$this->uid);
        list($this->oldalias, $this->oldepouse, $this->prenom, $this->nom, $this->homonyme) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    function get_unique_request($uid) {
        return parent::get_unique_request($uid,'epouse');
    }

    function formu() { return 'include/form.valid.epouses.tpl'; }

    function handle_formu () {
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser"))
            return false;
            
        require_once("tpl.mailer.inc.php");
        $mymail = new TplMailer('valid.epouses.tpl');
        $mymail->assign('username', $this->username);

        if($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer','yes');
            if($this->oldepouse)
                $mymail->assign('oldepouse',$this->oldalias);
            $mymail->assign('epouse',$this->alias);
            $this->commit();
        } else { // c'était donc Refuser
            $mymail->assign('answer','no');
            if (isset($_REQUEST["motif"]))
                $_REQUEST["motif"] = stripslashes($_REQUEST["motif"]);
        }

        $mymail->send();

        $this->clean();
        return "Mail envoyé";
    }

    function commit () {
        global $globals;
        
        $alias = ($this->epouse ? $this->alias : "");
        $globals->db->query("UPDATE auth_user_md5 set epouse='".$this->epouse."',alias='".$this->alias."' WHERE user_id=".$this->uid);
	$globals->db->query("DELETE FROM aliases WHERE type='epouse' AND id=".$this->uid);
	$globals->db->query("INSERT INTO aliases VALUES('".$this->alias."', 'epouse', ".$this->uid.")");
        $f = fopen("/tmp/flag_recherche","w");
        fputs($f,"1");
        fclose($f);
    }
}

?>

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
        $Id: valid_epouses.inc.php,v 1.17 2004-10-19 22:05:09 x2000habouzit Exp $
 ***************************************************************************/


class EpouseReq extends Validate {
    var $epouse;
    var $alias;
    var $forlife;

    var $oldepouse;
    var $oldalias;
    var $prenom;
    var $nom;

    var $homonyme;
    
    function EpouseReq ($_uid, $_forlife, $_epouse, $_stamp=0) {
        global $globals;
        $this->Validate($_uid, true, 'epouse', $_stamp);
        $this->epouse = $_epouse;
        $this->forlife = $_forlife;
        
        list($prenom) = explode('.',$_forlife);
        $this->alias = make_username($prenom,$this->epouse);
        if(empty($_epouse)) $this->alias = "";
        
        $sql = $globals->db->query("
	    SELECT  e.alias, u.epouse, u.prenom, u.nom, a.id
	      FROM  auth_user_md5 as u
	 LEFT JOIN  aliases       as e ON(e.type='epouse' AND e.id = u.user_id)
	 LEFT JOIN  aliases       as a ON(a.alias = '{$this->alias}' AND a.id != u.user_id)
	     WHERE  u.user_id = ".$this->uid);
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
        $mymail->assign('forlife', $this->forlife);

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
        
        $globals->db->query("UPDATE auth_user_md5 set epouse='".$this->epouse."' WHERE user_id=".$this->uid);
	$globals->db->query("DELETE FROM aliases WHERE type='epouse' AND id=".$this->uid);
	$globals->db->query("INSERT INTO aliases VALUES('".$this->alias."', 'epouse', ".$this->uid.")");
        $f = fopen("/tmp/flag_recherche","w");
        fputs($f,"1");
        fclose($f);
    }
}

?>

<?php

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
        global $no_update_bd;
        if($no_update_bd) return false;
        
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
        global $no_update_bd;
        if($no_update_bd) return false;
        
        $alias = ($this->epouse ? $this->alias : "");
        $globals->db->query("UPDATE auth_user_md5 set epouse='".$this->epouse."',epouse_soundex='".soundex_fr($this->epouse)."',alias='".$this->alias."' WHERE user_id=".$this->uid);
    }
}

?>

<?php

class SondageReq extends Validate {
    var $sid;
    var $titre;
    var $alias;

    var $username;
    var $prenom;
    var $nom;
    
    function SondageReq ($_uid, $_sid, $_stamp) {
        $this->Validate($_uid, false, 'sondage', $_stamp);
        $this->sid = $_sid;
        
        $sql = $globals->db->query("SELECT username,prenom,nom FROM auth_user_md5 "
            .   "WHERE user_id='".$this->uid."'");
        list($this->username,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        $sql = $globals->db->query("SELECT titre FROM sondage.description_generale "
            .   "WHERE ids='".$this->sid."'");
        list($this->titre) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        $this->alias = substr($this->titre,0,min(15,strlen($this->titre)));
    }

    function get_request($uid,$stamp) {
        return parent::get_request($uid,'sondage',$stamp);
    }

    function formu() { return 'include/form.valid.sondages.tpl'; }

    function handle_formu () {
        global $no_update_bd,$baseurl;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter"    && $_REQUEST['submit']!="Refuser"))
            return false;
        
        if ($_REQUEST['submit']!="Refuser") {
            $alias = stripslashes($_REQUEST['alias']);
            if ($alias=="") {
                return '<p class="erreur">Il faut entrer un alias pour valider ce sondage.</p>';
            }
            else {
                if (strlen($alias)>15) {
                    return "<p class='erreur'>L'alias est trop long.</p>";
                }
                else if (strpos($alias,"'")) {
                    return "<p class='erreur'>L'alias ne doit pas contenir le caractère '</p>";
                }
                else {//on vérifie que l'alias n'existe pas déjà
                    $resultat = $globals->db->query("select alias from sondage.description_generale ".
                    "where alias='$alias'");
                    if (mysql_num_rows($resultat)>0) {
                        return "<p class='erreur'>Cet alias est déjà utilisé.</p>";
                    }
                }
            }
            $this->alias=$alias;
        }

        require_once("tpl.mailer.inc.php");
    
        $lien = "$baseurl/sondage/questionnaire.php?alias=".urlencode($this->alias);
        
        $mymail = new TplMailer('valid.sondages.tpl');
        $mymail->assign('username', $this->username);
        $mymail->assign('alias', $this->alias);
        $mymail->assign('titre', '"'.str_replace('&#039;',"'",$this->titre).'"');

        if($_REQUEST['submit']=="Accepter") {
            $this->commit();
            $mymail->assign('answer','yes');
        } else {
            $mymail->assign('answer','no');
        }

        $mymail->send();
        //Suppression de la demande
        $this->clean();
        return "Mail envoyé";
    }

    function commit () {
        global $no_update_bd;
        require_once("sondage.requetes.inc.php");
        if($no_update_bd) return false;

        passer_en_prod($this->sid,$this->alias);
    }
}

?>

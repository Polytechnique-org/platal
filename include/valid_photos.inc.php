<?php

class PhotoReq extends Validate {
    var $mimetype;
    var $data;
    var $x;
    var $y;

    var $username;
    var $prenom;
    var $nom;
   
    function PhotoReq ($_uid, $_file, $_stamp=0) {
        global $erreur, $globals;

        $this->Validate($_uid, true, 'photo', $_stamp);
        $sql = $globals->db->query("SELECT username, prenom, nom FROM auth_user_md5 WHERE user_id=".$this->uid);
        list($this->username,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        
        if(!file_exists($_file)) {
            $erreur = "Fichier inexistant";
            return false;
        }
        // calcul de la taille de l'image
        $image_infos = getimagesize($_file);
        if(empty($image_infos)) {
            $erreur = "Image invalide";
            return false;
        }
        list($this->x, $this->y, $this->mimetype) = $image_infos;
        // récupération du type de l'image
        switch($this->mimetype) {
            case 1: $this->mimetype = "gif"; break;
            case 2: $this->mimetype = "jpeg"; break;
            case 3: $this->mimetype = "png"; break;
            default: $erreur = "Type d'image invalide"; return false;
        }
        // lecture du fichier
        if(!($size = filesize($_file)) or $size > SIZE_MAX) {
            $erreur = "Image trop grande (max 30ko)";
            return false;
        }
        $fd = fopen($_file, 'r');
        if (!$fd) return false;
        $this->data = fread($fd, SIZE_MAX);
        fclose($fd);

        unset($erreur);
    }

    function get_unique_request($uid) {
        return parent::get_unique_request($uid,'photo');
    }

    function formu() { return 'include/form.valid.photos.tpl'; }
    
    function handle_formu () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser"))
            return false;
        
        require_once("tpl.mailer.inc.php");
        $mymail = new TplMailer('valid.photos.tpl');
        $mymail->assign('username', $this->username);

        if($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer','yes');
            $this->commit();
        } else
            $mymail->assign('answer','no');
        
        $mymail->send();

        $this->clean();
        return "Mail envoyé";
    }
    
    function commit () {
        global $no_update_bd, $globals;
        if($no_update_bd) return false;
        
        $globals->db->query("REPLACE INTO photo set uid='".$this->uid."', attachmime = '".$this->mimetype."', attach='"
            .addslashes($this->data)."', x='".$this->x."', y='".$this->y."'");
    }
}

?>

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
        global $erreur;

        $this->Validate($_uid, true, 'photo', $_stamp);
        $sql = mysql_query("SELECT username, prenom, nom FROM auth_user_md5 WHERE user_id=".$this->uid);
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

    function echo_formu() {
        $url_app = isset($_COOKIE[session_name()]) ?  "" : "&amp;".SID;
        return <<<________EOF
        <form action="{$_SERVER['PHP_SELF']}" method="POST">
        <input type="hidden" name="uid" value="{$this->uid}" />
        <input type="hidden" name="type" value="{$this->type}" />
        <input type="hidden" name="stamp" value="{$this->stamp}" />
        <table class="bicol" summary="Demande d'alias">
        <tr>
            <td>Demandeur&nbsp;:</td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x={$this->username}')">
                {$this->prenom} {$this->nom}
                </a>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: middle;" colspan="2">
                <img src="../getphoto.php?x={$this->uid}$url_app" width=110 alt=" [ PHOTO ] " />
                <img src="../getphoto.php?x={$this->uid}&amp;req=true$url_app" width=110 alt=" [ PHOTO ] " />
            </td>
        </tr>
        <tr>
            <td style="vertical-align: middle;">
                <input type="submit" name="submit" value="Accepter" />
                <br /><br />
                <input type="submit" name="submit" value="Refuser" />
            </td>
            <td>
                <p>Raison du refus:</p>
                <textarea rows="5" cols="74" name="motif"></textarea>
            </td>
        </tr>
        </table>
        </form>
________EOF;
    }
    
    function handle_formu () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser"))
            return false;
        
        $message = "Cher(e) camarade,\n\n";
        
        if($_REQUEST['submit']=="Accepter") {
            $sql = mysql_query("SELECT alias FROM auth_user_md5 WHERE user_id=".$this->uid);
            list($old) = mysql_fetch_row($sql);
            mysql_free_result($sql);

            $message .=
                "  La demande de changement de photo que tu as "
                ."demandée vient d'être effectuée.\n\n";

            $this->commit();
        } else { // c'était donc Refuser
            $message .=
                "La demande de changement de photo que tu avais faite a été refusée.\n";
            if ($_REQUEST["motif"] != "" )
                $message .= "\nLa raison de ce refus est : \n".
                    stripslashes($_REQUEST["motif"])."\n\n";
        }
        
        $message .=
            "Cordialement,\n".
            "L'équipe X.org";

        $message = wordwrap($message,78);  
        require_once("diogenes.mailer.inc.php");
        $mymail = new DiogenesMailer('Equipe Polytechnique.org <validation+trombino@polytechnique.org>',
                $this->username."@polytechnique.org",
                "[Polytechnique.org/PHOTO] Changement de photo de ".$this->username,
                false, "validation+trombino@m4x.org");
        $mymail->setBody($message);
        $mymail->send();

        $this->clean();
        return "Mail envoyé";
    }
    
    function commit () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        mysql_query("REPLACE INTO photo set uid='".$this->uid."', attachmime = '".$this->mimetype."', attach='"
            .addslashes($this->data)."', x='".$this->x."', y='".$this->y."'");
    }
}

?>

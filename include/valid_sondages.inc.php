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
        
        $sql = mysql_query("SELECT username,prenom,nom FROM auth_user_md5 "
            .   "WHERE user_id='".$this->uid."'");
        list($this->username,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        $sql = mysql_query("SELECT titre FROM sondage.description_generale "
            .   "WHERE ids='".$this->sid."'");
        list($this->titre) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        $this->alias = substr($this->titre,0,min(15,strlen($this->titre)));
    }

    function get_request($uid,$stamp) {
        return parent::get_request($uid,'sondage',$stamp);
    }

    function echo_formu() {
        require_once("popwin.inc.php");
?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <input type="hidden" name="uid" value="<?php echo $this->uid ?>">
        <input type="hidden" name="type" value="<?php echo $this->type ?>">
        <input type="hidden" name="stamp" value="<?php echo $this->stamp ?>">
        <table class="bicol" align="center" cellpadding="4" summary="Sondage">
        <tr>
            <td>Demandeur&nbsp;:
            </td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x=<?php echo $this->username; ?>')"><?php
                    echo $this->prenom." ".$this->nom;?></a>
                <?php if(isset($this->old)) echo "({$this->old})";?>
            </td>
        </tr>
	    <tr>
            <td>Titre du sondage&nbsp;:</td>
            <td><?php echo $this->titre;?></td>
        </tr>
        <tr>
            <td>Prévisualisation du sondage&nbsp;:</td>
            <td><a href = <?php 
                global $baseurl; 
                echo "\"$baseurl/sondage/questionnaire.php?SID=".$this->sid.'"';
                echo ' target = "_blank">'.$this->titre;
                ?>
                </a></td>
        </tr>
        <tr>
            <td>Alias du sondage&nbsp;:</td>
            <td><input type="text" name="alias" value="<?php echo $this->alias;?>">&nbsp;(ne doit
            pas contenir le caractère ')</td>
        </tr>
        <tr>
            <td align="center" valign="middle">
                <input type="submit" name="submit" value="Accepter">
                <br /><br />
                <input type="submit" name="submit" value="Refuser">
            </td>
            <td>
                <p>Raison du refus:</p>
                <textarea rows="5" cols="74" name=motif></textarea>
            </td>
        </tr>
        </table>
        </form>
<?php
    }

    function handle_formu () {
        global $no_update_bd,$baseurl;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter"    && $_REQUEST['submit']!="Refuser"))
            return false;
        
        if ($_REQUEST['submit']!="Refuser") {
            $alias = stripslashes($_REQUEST['alias']);
            if ($alias=="") {
                echo "<br>Il faut entrer un alias pour valider ce sondage.";
                return false;
            }
            else {
                if (strlen($alias)>15) {
                    echo "<br>L'alias est trop long.";
                    return false;
                }
                else if (strpos($alias,"'")) {
                    echo "<br>L'alias ne doit pas contenir le caractère '";
                    return false;
                }
                else {//on vérifie que l'alias n'existe pas déjà
                    $resultat = mysql_query("select alias from sondage.description_generale ".
                    "where alias='$alias'");
                    if (mysql_num_rows($resultat)>0) {
                        echo "<br>Cet alias est déjà utilisé.";
                        return false;
                    }
                }
            }
            $this->alias=$alias;
        }

        require_once("mailer.inc.php");
    
        $lien = "$baseurl/sondage/questionnaire.php?alias=".urlencode($this->alias);
	    $titre = '"'.str_replace('&#039;',"'",$this->titre).'"';

        $mymail = new mailer('Equipe Polytechnique.org <validation+sondage@polytechnique.org>', 
                $this->username."@polytechnique.org",
                "[Polytechnique.org/SONDAGE] Demande de validation du sondage $titre par ".$this->username,
                false, "validation+sondage@m4x.org");

        $message =
            "Cher(e) camarade,\n".
            "\n";

        if($_REQUEST['submit']=="Accepter") {
            $this->commit();
            $message .=
                "  Le sondage $titre que tu as composé vient d'être validé.\n".
		"Il ne te reste plus qu'à transmettre aux sondés l'adresse".
                " où ils pourront voter. Cette adresse est : $lien.\n";
        } else {
            $message .=
                "Le sondage $titre que tu avais proposé a été refusé.\n";
            if (!empty($_REQUEST["motif"]))
                $message .= "\nLa raison de ce refus est : \n".
                    stripslashes($_REQUEST["motif"])."\n";
        }

        $message .=
            "\n".
            "Cordialement,\n".
            "L'équipe X.org";
        $message = wordwrap($message,78);  
        $mymail->setBody($message);
        $mymail->send();
        echo "<br />Mail envoyé";
        //Suppression de la demande
        $this->clean();
    }

    function commit () {
        global $no_update_bd;
        require_once("sondage.requetes.inc.php");
        if($no_update_bd) return false;

        passer_en_prod($this->sid,$this->alias);
    }
}

?>

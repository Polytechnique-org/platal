<?php

class AliasReq extends Validate {
    var $alias;
    var $raison;

    var $username;
    var $prenom;
    var $nom;
    var $old;
    
    function AliasReq ($_uid, $_alias, $_raison, $_stamp=0) {
        $this->Validate($_uid, true, 'alias', $_stamp);
        $this->alias = $_alias;
        $this->raison = $_raison;
        
        $sql = mysql_query("SELECT username,prenom,nom,domain FROM auth_user_md5 as u "
                        .  "LEFT JOIN groupex.aliases as a ON (a.email = u.username and a.id = 12)    "
                        .  "WHERE user_id='".$this->uid."'");
        list($this->username,$this->prenom,$this->nom,$this->old) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    function get_unique_request($uid) {
        return parent::get_unique_request($uid,'alias');
    }

    function echo_formu() {
        require_once("popwin.inc.php");
?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <input type="hidden" name="uid" value="<?php echo $this->uid ?>">
        <input type="hidden" name="type" value="<?php echo $this->type ?>">
        <input type="hidden" name="stamp" value="<?php echo $this->stamp ?>">
        <table class="bicol" align="center" cellpadding="4" summary="Demande d'alias">
        <tr>
            <td>Demandeur&nbsp;:
            </td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x=<?php echo $this->username; ?>')"><?php
                    echo $this->prenom." ".$this->nom;?></a>
                <?php if(isset($this->old)) echo "({$this->old})";?>
            </td>
        </tr>
        <tr>
            <td>Nouvel&nbsp;alias&nbsp;:</td>
            <td><?php echo $this->alias;?>@melix.net</td>
        </tr>
        <tr>
            <td>Motif :</td>
            <td style="border: 1px dotted inherit">
                <?php echo nl2br($this->raison);?>
            </td>
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
        global $no_update_bd;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter"    && $_REQUEST['submit']!="Refuser"))
            return false;

        require_once("mailer.inc.php");
        $mxnet = $this->alias."@melix.net";
        $mxorg = $this->alias."@melix.org";

        $mymail = new mailer('Equipe Polytechnique.org <validation+melix@polytechnique.org>', 
                $this->username."@polytechnique.org",
                "[Polytechnique.org/MELIX] Demande de l'alias $mxnet par ".$this->username,
                false, "validation+melix@m4x.org");

        $message =
            "Cher(e) camarade,\n".
            "\n";

        if($_REQUEST['submit']=="Accepter") {
            $this->commit();
            $message .=
                "  Les adresses e-mail $mxnet et $mxorg que tu avais demandées viennent".
                " d'être créées, tu peux désormais les utiliser à ta convenance.\n";
        } else {
            $message .=
                "La demande que tu avais faite pour les alias $mxnet et $mxorg a été refusée.\n";
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
        if($no_update_bd) return false;

        mysql_query("DELETE FROM groupex.aliases WHERE id=12 AND email='{$this->username}'");
        mysql_query("INSERT INTO groupex.aliases SET email='{$this->username}',domain='"
                    .$this->alias."@melix.net',id=12");
    }
}

?>

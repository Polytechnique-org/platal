<?php

class MListReq extends Validate {
    var $alias;
    var $topic;
    var $publique;
    var $libre;
    var $archive;
    var $freeins;
    var $comment;
    var $moderos;
    var $membres;

    var $username;
    var $prenom;
    var $nom;

    function MListReq ($_uid, $_alias, $_topic, $_publique, $_libre, $_archive, $_freeins,
            $_comment, $_moderos, $_membres, $_stamp=0) {
        $this->Validate($_uid, false, 'ml', $_stamp);
        $this->alias = $_alias;
        $this->topic = $_topic;
        $this->publique = $_publique;
        $this->libre = $_libre;
        $this->archive = $_archive;
        $this->freeins = $_freeins;
        $this->comment = $_comment;
        $this->moderos = $_moderos;
        $this->membres = $_membres;
        
        $sql = mysql_query("SELECT username,prenom,nom FROM auth_user_md5 WHERE user_id=".$this->uid);
        list($this->username,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    function get_unique_request($uid) {
        return false; // ben oui, c pas un objet unique !!!
    }

    function echo_formu() {
        require_once("popwin.inc.php");
?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <input type="hidden" name="uid" value="<?php echo $this->uid ?>">
        <input type="hidden" name="type" value="<?php echo $this->type ?>">
        <input type="hidden" name="stamp" value="<?php echo $this->stamp ?>">
        <table class="bicol" align="center">
        <tr>
            <td>Demandeur&nbsp;:</td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x=<?php echo $this->username; ?>')">
                <?php echo $this->prenom." ".$this->nom;?>
                </a>
            </td>
        </tr>
        <tr>
            <td>Motif :</td>
            <td><?php echo nl2br($this->comment);?>
            </td>
        </tr>
        <tr>
            <td style="border-top:1px dotted inherit">
                Alias :
            </td>
            <td style="border-top:1px dotted inherit">
                <input type="text" name="alias" value="<?php echo $this->alias ?>" />@polytechnique.org
            </td>
        </tr>
        <tr>
            <td>Topic :</td>
            <td><input type="text" name="topic" size="60" value="<?php echo $this->topic ?>" />
            </td>
        </tr>
        <tr>
            <td>Propriétés :</td>
            <td>
                <input type="checkbox" name="publique" <?php
                    echo($this->publique?"checked=\"checked\"":"")
                ?>/>Publique
                <input type="checkbox" name="libre" <?php
                    echo($this->libre?"checked=\"checked\"":"")
                ?>/>Libre
                <input type="checkbox" name="freeins" <?php
                    echo($this->freeins?"checked=\"checked\"":"")
                ?>/>Freeins
                <input type="checkbox" name="archive" <?php
                    echo($this->archive?"checked=\"checked\"":"")
                ?>/>Archive
            </td>
        </tr>
        <tr>
            <td style="border-top:1px dotted inherit">
                Modéros :
            </td>
            <td style="border-top:1px dotted inherit">
<?php
                $sql = mysql_query("SELECT username FROM auth_user_md5"
                    ." WHERE user_id IN ({$this->moderos})"
                    ." ORDER BY nom, prenom");
                $tab = array();
                while(list($username) = mysql_fetch_row($sql))
                    $tab[] = $username;
                echo implode(', ', $tab);
                mysql_free_result($sql);
?>
            </td>
        </tr>
        <tr>
            <td>Membres :</td>
            <td><?php
                $sql = mysql_query("SELECT username FROM auth_user_md5"
                    ." WHERE user_id IN ({$this->membres})"
                    ." ORDER BY nom, prenom");
                $tab = array();
                while(list($username) = mysql_fetch_row($sql))
                    $tab[] = $username;
                echo implode(', ', $tab);
                mysql_free_result($sql);
                ?>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle" style="border-top:1px dotted inherit">
                <input type="submit" name="submit" value="Accepter">
                <br /><br />
                <input type="submit" name="submit" value="Refuser">
            </td>
            <td style="border-top:1px dotted inherit">
                <p>Explication complémentaire (refus ou changement de config, ...)</p>
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
        
        if(empty($_REQUEST['submit']) || ($_REQUEST['submit']!="Accepter"
            && $_REQUEST['submit']!="Refuser"))
            return false;

        $this->alias = $_REQUEST['alias'];
        $this->topic = $_REQUEST['topic'];
        $this->publique = isset($_REQUEST['publique']) && $_REQUEST['publique'];
        $this->libre = isset($_REQUEST['libre']) && $_REQUEST['libre'];
        $this->archive = isset($_REQUEST['archive']) && $_REQUEST['archive'];
        $this->freeins = isset($_REQUEST['freeins']) && $_REQUEST['freeins'];

        $this->clean();
        $this->submit();

        require_once("mailer.inc.php");
        
        $mymail = new mailer('Equipe Polytechnique.org <validation+listes@polytechnique.org>', 
                $this->username."@polytechnique.org",
                "[Polytechnique.org/LISTES] Demande de la liste {$this->alias} par ".$this->username,
                false, "validation+listes@m4x.org");

        $message =
            "Cher(e) camarade,\n".
            "\n";

        if($_REQUEST['submit']=="Accepter") {
            if($this->commit()) {
                $message .=
                    "  La mailing list {$this->alias} que tu avais demandée vient".
                    " d'être créée\n";
                if (!empty($_REQUEST["motif"]))
                    $message .= "\nInformations complémentaires :\n".
                        stripslashes($_REQUEST["motif"])."\n";
            } else {
                echo "<p class=\"erreur\">Aucun mail envoyé, erreur !</p>\n";
                return false;
            }
        } else {
            $message .=
                "La demande que tu avais faite pour la mailing list ".
                $this->alias." a été refusée.\n";
            if (!empty($_REQUEST["motif"]))
                $message .= "\nLa raison de ce refus est : \n".
                    stripslashes($_REQUEST["motif"])."\n";
            $this->clean();
        }

        $message .=
            "\n".
            "Cordialement,\n".
            "L'équipe X.org";
        $message = wordwrap($message,78);  
        $mymail->setBody($message);
        $mymail->send();
        echo "<p class=\"normal\">Mail envoyé</p>";
        return true;
    }

    function commit () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        require_once("flagset.inc.php");
        $type = new flagset();
        if ($this->libre) $type->addflag('libre');
        if ($this->publique) $type->addflag('publique');
        if ($this->archive) $type->addflag('archive');
        if ($this->freeins) $type->addflag('freeins'); 
        
        mysql_query("INSERT INTO listes_def SET type='".$type->value."', topic='{$this->topic}'");
        echo "<p class=\"normal\">Liste {$this->alias} créée</p>\n";
    
        if(!mysql_errno()) {
            $id = mysql_insert_id();
            if ($this->archive)
                mysql_query("replace into listes_ins set idl=$id, idu=0");
            mysql_query("INSERT INTO aliases (alias,type,id) VALUES".
                    "('{$this->alias}','liste',$id)".
                    ",('owner-{$this->alias}','liste-owner',$id)".
                    ",('sm-{$this->alias}','liste-sans-moderation',$id)".
                    ",('{$this->alias}-request','liste-request',$id)");

            if (!mysql_errno()) {
                echo "<p class=\"normal\">Liste {$this->alias} ajoutée aux alias</p>\n";
                if (isset($this->moderos)) {
                    $tokens = explode(',',$this->moderos);
                    $values = array();
                    foreach ($tokens as $tok) {
                        $values[] = "($id,$tok)";
                    }
                    $values = implode(',', $values);
                    mysql_query("INSERT INTO listes_mod (idl, idu) VALUES $values");
                }

                // ajout des membres si précisés
                if (isset($this->membres)) {
                    $tokens = explode(',',$this->membres);
                    $values = array();
                    foreach ($tokens as $tok) {
                        $values[] = "($id,$tok)";
                    }
                    $values = implode(',', $values);
                    mysql_query("INSERT INTO listes_ins (idl, idu) VALUES $values");
                }

                $this->clean();
                return true;
            } else { // alias déjà existant ?
                mysql_query("DELETE FROM aliases WHERE id='$id'");
                mysql_query("DELETE FROM listes_ins WHERE id='$id'");
                mysql_query("DELETE FROM listes_def WHERE id='$id'");
                echo "<p class=\"erreur\">Nom déjà utilisé (owner-{$this->alias} ou {$this->alias}-request)</p>\n";
                return false;
            } // if mysql_errno == 0 pour insert dans aliases
        } else {
            echo "<p class=\"erreur\">Nom déjà utilisé</p>\n";
            return false;
        } // if mysql_errno == 0 pour insert dans liste_def

        return true;
    }
}

?>

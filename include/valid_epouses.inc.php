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
        
        $sql = mysql_query("select u1.alias, u1.epouse, u1.prenom, u1.nom"
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

    function formu() {
        $old_line = $this->oldepouse ? "({$this->oldepouse} - {$this->oldalias})" : "";
        $homonyme = "";
        
        if (!empty($this->homonyme)) $homonyme = <<<________EOF
        <tr>
            <td colspan="2">
                <span class="erreur">Probleme d'homonymie !
                <a href="javascript:x()"  onclick="popWin('/x.php?x={$this->homonyme}"> {$this->homonyme}</a>
                </span>
            </td>
        </tr>
________EOF;
                
        return <<<________EOF
        <form action="{$_SERVER['PHP_SELF']}" method="POST">
        <input type="hidden" name="uid" value="{$this->uid}">
        <input type="hidden" name="type" value="{$this->type}">
        <input type="hidden" name="stamp" value="{$this->stamp}">
        <table class="bicol" cellpadding="4" summary="Demande d'alias d'épouse">
        <tr>
            <td>Demandeur&nbsp;:</td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x={$this->username}')">
                {$this->prenom} {$this->nom}
                </a>
                $oldline
            </td>
        </tr>
        <tr>
            <td>&Eacute;pouse&nbsp;:</td>
            <td>{$this->epouse}</td>
        </tr>
        <tr>
            <td>Nouvel&nbsp;alias&nbsp;:</td>
            <td>{$this->alias}</td>
        </tr>
        $homonyme
        <tr>
            <td style="vertical-align: middle;">
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
________EOF;
    }

    function handle_formu () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        if(empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter"    && $_REQUEST['submit']!="Refuser"))
            return false;
            
        $message = "Chère camarade,\n\n";
        
        if($_REQUEST['submit']=="Accepter") {
            $message .=
                "  La demande de changement de nom de mariage que tu as demandée vient".
                " d'être effectuée.\n\n";

            if ($this->oldepouse) {
                $message .= 
                    "  Les alias {$this->oldalias}@polytechnique.org et ".
                    "{$this->oldalias}@m4x.org ont été supprimés.\n\n";
            }

            if ($this->epouse) {
                $message .=
                    "  De plus, les alias ".$this->alias."@polytechnique.org et ".
                    $this->alias."@m4x.org ont été créés.\n\n";
            }
            $this->commit();
        } else { // c'était donc Refuser
            $message .=
                "La demande de changement de nom de mariage que tu avais faite a été refusée.\n";
            if ($_REQUEST["motif"] != "" )
                $message .= "\nLa raison de ce refus est : \n".
                    stripslashes($_REQUEST["motif"])."\n\n";
        }
        
        $message .=
            "Cordialement,\n".
            "L'équipe X.org";

        $message = wordwrap($message,78);  
        require_once("diogenes.mailer.inc.php");
        $mymail = new DiogenesMailer('Equipe Polytechnique.org <validation+epouse@polytechnique.org>',
                $this->username."@polytechnique.org",
                "[Polytechnique.org/EPOUSE] Changement de nom de mariage de ".$this->username,
                false, "validation+epouse@m4x.org");
        $mymail->setBody($message);
        $mymail->send();

        $this->clean();
        return "Mail envoyé";
    }

    function commit () {
        global $no_update_bd;
        if($no_update_bd) return false;
        
        $alias = ($this->epouse ? $this->alias : "");
        mysql_query("UPDATE auth_user_md5 set epouse='".$this->epouse."',epouse_soundex='".soundex_fr($this->epouse)."',alias='".$this->alias."' WHERE user_id=".$this->uid);
    }
}

?>

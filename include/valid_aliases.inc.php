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

    function formu() {
        $old = $this->old ? "({$this->old})" : "";
        $raison = nl2br(stripslashes(htmlentities($this->raison)));
        $newAlias = $this->alias.'@melix.net';
        return <<<________EOF
        <form action="{$_SERVER['PHP_SELF']}" method="POST">
        <input type="hidden" name="uid" value="{$this->uid}" />
        <input type="hidden" name="type" value="{$this->type}" />
        <input type="hidden" name="stamp" value="{$this->stamp}" />
        <table class="bicol" cellpadding="4" summary="Demande d'alias">
        <tr>
            <td>Demandeur&nbsp;:
            </td>
            <td><a href="javascript:x()" onclick="popWin('/x.php?x={$this->username}">
                {$this->prenom} {$this->nom}</a> $old
            </td>
        </tr>
        <tr>
            <td>Nouvel&nbsp;alias&nbsp;:</td>
            <td>{$newAlias}</td>
        </tr>
        <tr>
            <td>Motif :</td>
            <td style="border: 1px dotted inherit">
                $raison
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

        require_once("tpl.mailer.inc.php");
        $mymail = new TplMailer('valid.alias.tpl');
        $mymail->assign('alias', $this->alias);
        $mymail->assign('username', $this->username);

        if($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer', 'yes');
            $this->commit() ; 
        } else {
            $mymail->assign('answer', 'no');
            $mymail->assign('motif', stripslashes($_REQUEST['motif']));
        }
        $mymail->send();
        //Suppression de la demande
        $this->clean();
        return "Mail envoyé";
    }

    function commit () {
        global $no_update_bd;
        if($no_update_bd) return false;

        $domain=$this->alias.'@melix.net';
        mysql_query("DELETE FROM groupex.aliases WHERE id=12 AND email='{$this->username}'");
        mysql_query("INSERT INTO groupex.aliases SET email='{$this->username}',domain='$domain',id=12");         
    }
}

?>

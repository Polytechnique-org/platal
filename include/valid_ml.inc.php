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
        global $globals;
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
        
        $sql = $globals->db->query("SELECT username,prenom,nom FROM auth_user_md5 WHERE user_id=".$this->uid);
        list($this->username,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
    }

    function get_unique_request($uid) {
        return false; // ben oui, c pas un objet unique !!!
    }

    function formu() {
        global $globals;
        $sql = $globals->db->query("SELECT username FROM auth_user_md5"
            ." WHERE user_id IN ({$this->moderos})"
            ." ORDER BY nom, prenom");
        $tab = array();
        while(list($username) = mysql_fetch_row($sql)) $tab[] = $username;
        $this->moderos_txt = implode(', ', $tab);
        mysql_free_result($sql);

        $sql = $globals->db->query("SELECT username FROM auth_user_md5"
            ." WHERE user_id IN ({$this->membres})"
            ." ORDER BY nom, prenom");
        $tab = array();
        while(list($username) = mysql_fetch_row($sql)) $tab[] = $username;
        $this->membres_txt = implode(', ', $tab);
        mysql_free_result($sql);
        return 'include/form.valid.ml.tpl';
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

        require_once("tpl.mailer.inc.php");
        
        $mymail = new TplMailer();
        $mymail->assign('username',$this->username);
        $mymail->assign('alias',$this->alias);
        $mymail->assign('motif',stripslashes($_REQUEST['motif']));

        if($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer', 'yes');
            if(!$this->commit()) {
                return "<p class=\"erreur\">Aucun mail envoyé, erreur !</p>\n";
            }
        } else {
            $mymail->assign('answer', 'no');
            $this->clean();
        }

        $mymail->send();
        return "Mail envoyé";
    }

    function commit () {
        global $no_update_bd, $globals;
        if($no_update_bd) return false;
        
        $type = new DiogenesFlagset();
        if ($this->libre) $type->addflag('libre');
        if ($this->publique) $type->addflag('publique');
        if ($this->archive) $type->addflag('archive');
        if ($this->freeins) $type->addflag('freeins'); 
        
        $globals->db->query("INSERT INTO listes_def SET type='".$type->value."', topic='{$this->topic}'");
        echo "<p class=\"normal\">Liste {$this->alias} créée</p>\n";
    
        if(!mysql_errno()) {
            $id = mysql_insert_id();
            if ($this->archive)
                $globals->db->query("replace into listes_ins set idl=$id, idu=0");
            $globals->db->query("INSERT INTO aliases (alias,type,id) VALUES".
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
                    $globals->db->query("INSERT INTO listes_mod (idl, idu) VALUES $values");
                }

                // ajout des membres si précisés
                if (isset($this->membres)) {
                    $tokens = explode(',',$this->membres);
                    $values = array();
                    foreach ($tokens as $tok) {
                        $values[] = "($id,$tok)";
                    }
                    $values = implode(',', $values);
                    $globals->db->query("INSERT INTO listes_ins (idl, idu) VALUES $values");
                }

                $this->clean();
                return true;
            } else { // alias déjà existant ?
                $globals->db->query("DELETE FROM aliases WHERE id='$id'");
                $globals->db->query("DELETE FROM listes_ins WHERE id='$id'");
                $globals->db->query("DELETE FROM listes_def WHERE id='$id'");
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

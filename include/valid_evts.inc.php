<?php

class EvtReq extends Validate {
    var $evtid;
    var $titre;
    var $texte;
    var $pmin;
    var $pmax;
    var $peremption;    
    var $comment;
    
    var $username;
    var $promo;
    var $nom;
    var $prenom;

    function EvtReq($_evtid,$_titre,$_texte,$_pmin,$_pmax,$_peremption,
      $_comment,$_uid,$_stamp=0) {
        global $globals;
        $this->Validate($_uid, false, 'evts', $_stamp);
        $this->evtid = $_evtid;
        $this->titre = $_titre;
        $this->texte = $_texte;
        $this->pmin = $_pmin;
        $this->pmax = $_pmax;
        $this->peremption = $_peremption;
        $this->comment = $_comment;
        $req = $globals->db->query("SELECT username,promo,nom,prenom FROM "
          ."auth_user_md5 WHERE user_id='$_uid'");
        list($this->username,$this->promo,$this->nom,$this->prenom) 
            = mysql_fetch_row($req);
        mysql_free_result($req);
    }

    function get_unique_request($uid) {
        return false;  //non unique
    }

    function formu() { return 'include/form.valid.evts.tpl'; }

    function handle_formu() {
        global $globals;
        if (isset($_POST['action'])) {
            require("tpl.mailer.inc.php");
            $mymail = new TplMailer('valid.evts.tpl');
            $mymail->assign('username',$this->username);
            $mymail->assign('titre',$this->titre);

            if($_REQUEST['action']=="Valider") {
                $globals->db->query("UPDATE evenements
                             SET creation_date = creation_date, validation_user_id = {$_SESSION['uid']},
                                 validation_date = NULL, flags = CONCAT(flags,',valide')
                             WHERE id='{$this->evtid}' LIMIT 1");
                $mymail->assign('answer','yes');
            }
            if($_REQUEST['action']=="Invalider") {
                $globals->db->query("UPDATE evenements
                             SET creation_date = creation_date, validation_user_id = {$_SESSION['uid']},
                                 validation_date = NULL, flags = REPLACE(flags,'valide','')
                             WHERE id='{$this->evtid}' LIMIT 1");
                $mymail->assign('answer', 'no');
            }
            if($_REQUEST['action']=="Supprimer") {
                $globals->db->query("DELETE from evenements WHERE id='{$this->evtid}' LIMIT 1");
            }
            if ($_POST['action']!="Supprimer")
                $mymail->send();
            $this->clean();
        }
    }

    function commit() {
    }
}

?>

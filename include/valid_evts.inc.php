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
        $this->Validate($_uid, false, 'evts', $_stamp);
        $this->evtid = $_evtid;
        $this->titre = $_titre;
        $this->texte = $_texte;
        $this->pmin = $_pmin;
        $this->pmax = $_pmax;
        $this->peremption = $_peremption;
        $this->comment = $_comment;
        $req = mysql_query("SELECT username,promo,nom,prenom FROM "
          ."auth_user_md5 WHERE user_id='$_uid'");
        list($this->username,$this->promo,$this->nom,$this->prenom) 
            = mysql_fetch_row($req);
        mysql_free_result($req);
    }

    function get_unique_request($uid) {
        return false;  //non unique
    }

    function formu() {
        return <<<________EOF
        <form action="{$_SERVER['PHP_SELF']}" method="POST" name="modif">
          <input type="hidden" name="uid" value="{$this->uid}" />
          <input type="hidden" name="type" value="{$this->type}" />
          <input type="hidden" name="stamp" value="{$this->stamp}" />
          <table class="bicol" width="98%">
            <thead>
              <tr>
                <th colspan="2">Événement</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  Posté par <a href="javascript:x()"  onclick="popWin('../x.php?x={$this->username}">
                    {$this->prenom} {$this->nom} (X{$this->promo})
                  </a>
                  [<a href="mailto:{$this->username}@polytechnique.org">lui écrire</a>]"
                </td>
              </tr>
              <tr>
                <th>Titre</th>
                <td>{$this->titre}</td>
              </tr>
              <tr>
                <th>Texte</th>
                <td>{$this->texte}</td>
              </tr>
              <tr>
                <th>Péremption</th>
                <td>{$this->peremption}</td>
              </tr>
              <tr>
                <th>Promos</th>
                <td>{$this->pmin} - {$this->pmax}</td>
              </tr>
              <tr>
                <th>Commentaire</th>
                <td>{$this->comment}</td>
              </tr>
              <tr>
                <td class="center" colspan="2">
                  <input type="submit" name="action" value="Valider" />
                  <input type="submit" name="action" value="Invalider" />
                  <input type="submit" name="action" value="Supprimer" />
                </td>
              </tr>
            </tbody>
          </table>
        </form>
________EOF;
    }

    function handle_formu() {
        if (isset($_POST['action'])) {
            require("diogenes.mailer.inc.php");
            $mymail = new DiogenesMailer('Equipe Polytechnique.org '
                .'<validation+recrutement@polytechnique.org>', 
                $this->username."@polytechnique.org",
                "[Polytechnique.org/EVENEMENTS] Proposition d'événement",
                false, "validation+evts@m4x.org");

            $message = "Cher(e) camarade,\n\n";

            if($_REQUEST['action']=="Valider") {
                $req="UPDATE evenements SET creation_date = "
                ."creation_date, validation_user_id =".$_SESSION['uid']
                .", validation_date = NULL, flags = CONCAT(flags,"
                ."',valide')  WHERE id='{$this->evtid}' LIMIT 1";
                $result = mysql_query ($req);
                $message .= "L'annonce que tu avais proposée ("
                    .strip_tags($this->titre).") vient d'être validée.";
            }
            if($_REQUEST['action']=="Invalider") {
                $req="UPDATE evenements SET creation_date = "
                ."creation_date, validation_user_id =".$_SESSION['uid']
                .", validation_date = NULL, flags = REPLACE(flags,"
                ."'valide','')  WHERE id='{$this->evtid}' LIMIT 1";
                $result = mysql_query ($req);
                $message .= "L'annonce que tu avais proposée ("
                    .strip_tags($this->titre).") a été refusée.";
            }
            if($_REQUEST['action']=="Supprimer") {
                $req="DELETE from evenements WHERE id='{$this->evtid}'"
                ." LIMIT 1";
                $result = mysql_query ($req);
            }
            $message .=
                "\n".
                "Cordialement,\n".
                "L'équipe X.org";
            $message = wordwrap($message,78);  
            $mymail->setBody($message);
            if ($_POST['action']!="Supprimer")
                $mymail->send();
            $this->clean();
        }
    }

    function commit() {
    }
}

?>

<?php
require("auto.prepend.inc.php");
new_admin_page('admin/evenements.tpl', true);

$arch = isset($_REQUEST['arch']) ? $_REQUEST['arch'] : 0;

$page->assign('arch', $arch);

$action = isset($_POST['action']) ? $_POST['action'] : "";

$err = Array();

switch($action) {
    case "Proposer":
	$req = "UPDATE  evenements
	           SET  titre='{$_POST['titre']}', texte='{$_POST['texte']}', peremption='{$_POST['peremption']}',
		        promo_min = {$_POST['promo_min']}, promo_max = {$_POST['promo_max']}
                 WHERE  id = {$_POST['evt_id']}
                 LIMIT  1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Valider":
	// le 'creation_date = creation_date' est indispensable pour que 
	// creation_date conserve sa valeur.
	$req="UPDATE  evenements
                 SET  creation_date = creation_date, validation_user_id ='{$_SESSION['uid']}',
                      validation_date = NULL, flags = CONCAT(flags,',valide')
               WHERE  id ='{$_POST['evt_id']}'
               LIMIT  1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Invalider":
	// le 'creation_date = creation_date' est indispensable pour que 
	// creation_date conserve sa valeur.
	$req="UPDATE  evenements
                 SET  creation_date = creation_date, validation_user_id = ".$_SESSION['uid'].",
                      validation_date = NULL, flags = REPLACE(flags, 'valide','')
               WHERE  id = ".$_POST['evt_id']."
               LIMIT  1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Supprimer":
	$req="DELETE from evenements WHERE id = ".$_POST['evt_id']." LIMIT 1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Archiver":
	$req="UPDATE evenements SET flags = CONCAT(flags,',archive')WHERE id = ".$_POST['evt_id']." LIMIT 1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Desarchiver":
	$req="UPDATE evenements SET flags = REPLACE(flags,'archive','')WHERE id = ".$_POST['evt_id']." LIMIT 1";
        $result = $globals->db->query ($req);
        $err[] = "Requete effectuée : $req";
        break;

    case "Editer":
	$evt_req = $globals->db->query("SELECT titre, texte, peremption, promo_min, promo_max, validation_message FROM evenements WHERE id=".$_POST["evt_id"]);
        list($titre, $texte, $peremption, $promo_min, $promo_max, $validation_message) = mysql_fetch_row($evt_req) ;
        $page->assign('mode', 'edit');
        $page->assign('titre',$titre);
        $page->assign('texte',$texte);
        $page->assign('promo_min',$promo_min);
        $page->assign('promo_max',$promo_max);
        $page->assign('validation_message',$validation_message);
        $page->assign('peremption',$peremption);

        $select = "";
        for ($i = 1 ; $i < 30 ; $i++) {
            $p_stamp=date("Ymd",time()+3600*24*$i);
            $year=substr($p_stamp,0,4);
            $month=substr($p_stamp,4,2);
            $day=substr($p_stamp,6,2);

            $select .= "<option value=\"$p_stamp\"" . (($p_stamp == strtr($peremption, array("-" => ""))) ? " selected" : "")."> $day / $month / $year</option>\n";
        }
        $page->assign('select',$select);

        break;
}

if ($action != "Editer") {

    $sql = "SELECT  e.id, e.titre, e.texte,
                    DATE_FORMAT(e.creation_date,'%d/%m/%Y %T') AS creation_date,
                    DATE_FORMAT(e.validation_date,'%d/%m/%Y %T') AS validation_date,
                    DATE_FORMAT(e.peremption,'%d/%m/%Y') AS peremption,
                    e.promo_min, e.promo_max, e.validation_message, e.validation_user_id,
                    FIND_IN_SET('valide', e.flags) AS fvalide,
                    FIND_IN_SET('archive', e.flags) AS farch,
                    a.promo, a.nom, a.prenom, a.username
              FROM  evenements    AS e
        INNER JOIN  auth_user_md5 AS a ON(e.user_id = a.user_id)
             WHERE  ".($arch ? "" : "!")."FIND_IN_SET('archive',e.flags)
          ORDER BY  FIND_IN_SET('valide',e.flags), peremption";
    $page->mysql_assign($sql, 'evs');
}

$page->assign('err', $err);
$page->run();
?>

<?php
require("auto.prepend.inc.php");
require("validations.inc.php");

new_skinned_page('alias.tpl', AUTH_MDP);

$page->assign('demande', AliasReq::get_unique_request($_SESSION['uid']));

//Récupération des alias éventuellement existants
$sql = "SELECT domain from groupex.aliases WHERE id=12 AND email like '".$_SESSION['username']."'";
if($result = $globals->db->query($sql)) {
    list($aliases) = mysql_fetch_row($result);
    mysql_free_result($result);
    $page->assign('actuel',$aliases);
}

//Si l'utilisateur vient de faire une damande
if (isset($_REQUEST['alias']) and isset($_REQUEST['raison'])) {
    $alias = $_REQUEST['alias'];
    $raison = $_REQUEST['raison'];

    $page->assign('r_alias', $alias);
    $page->assign('r_raison', $raison);

    //Quelques vérifications sur l'alias (caractères spéciaux)
    if (!preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $alias)) {
        $page->assign('error', "L'adresse demandée n'est pas valide.
                                Vérifie qu'elle comporte entre 3 et 20 caractères
                                et qu'elle ne contient que des lettres non accentuées,
                                des chiffres ou les caractères - et .");
        $page->run('error');
    } else {
        //vérifier que l'alias n'est pas déja pris
        $result = $globals->db->query("SELECT 1 FROM groupex.aliases WHERE id=12 AND domain LIKE '$alias@melix.net'");
        if (mysql_num_rows($result)>0) {
            $page->assign('error', "L'alias $alias@melix.net a déja été attribué.
                                    Tu ne peux donc pas l'obtenir.");
            $page->run('error');
        }

        //vérifier que l'alias n'est pas déja en demande
        $it = new ValidateIterator ();
        while($req = $it->next()) {
            if ($req->type == "alias" and $req->alias == $alias) {
                $page->assign('error', "L'alias $alias@melix.net a déja été demandé.
                                        Tu ne peux donc pas l'obtenir pour l'instant.");
                $page->run('error');
            }
        }

        //Insertion de la demande dans la base, écrase les requêtes précédente
        $myalias = new AliasReq($_SESSION['uid'], $alias, $raison);
        $myalias->submit();
        $page->assign('success',$alias);
        $page->run('succes');
    }
}

$page->run();
?>

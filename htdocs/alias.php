<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once("xorg.inc.php");
require_once("validations.inc.php");

new_skinned_page('alias.tpl', AUTH_MDP);

$page->assign('demande', AliasReq::get_unique_request($_SESSION['uid']));

//Récupération des alias éventuellement existants
$sql = "SELECT  alias
          FROM  virtual
    INNER JOIN  virtual_redirect USING(vid)
          WHERE redirect='{$_SESSION['forlife']}@m4x.org' AND alias LIKE '%@melix.net'";
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
        $result = $globals->db->query("SELECT 1 FROM virtual WHERE alias='$alias@melix.net'");
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

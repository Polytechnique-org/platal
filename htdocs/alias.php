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

$uid     = Session::getInt('uid');
$forlife = Session::get('forlife');

$page->assign('demande', AliasReq::get_unique_request($uid));

//Récupération des alias éventuellement existants
$res = $globals->xdb->query(
        "SELECT  alias, emails_alias_pub
           FROM  auth_user_quick, virtual
     INNER JOIN  virtual_redirect USING(vid)
           WHERE ( redirect={?} OR redirect= {?} )
                 AND alias LIKE '%@{$globals->mail->alias_dom}' AND user_id = {?}", 
        $forlife.'@'.$globals->mail->domain, $forlife.'@'.$globals->mail->domain2, Session::getInt('uid'));
list($alias, $visibility) = $res->fetchOneRow();
$page->assign('actuel', $alias);

//Si l'utilisateur vient de faire une damande
if (Env::has('alias') and Env::has('raison')) {
    $alias  = Env::get('alias');
    $raison = Env::get('raison');

    $page->assign('r_alias', $alias);
    $page->assign('r_raison', $raison);

    //Quelques vérifications sur l'alias (caractères spéciaux)
    if (!preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $alias)) {
        $page->trig("L'adresse demandée n'est pas valide.
                    Vérifie qu'elle comporte entre 3 et 20 caractères
                    et qu'elle ne contient que des lettres non accentuées,
                    des chiffres ou les caractères - et .");
        $page->run('error');
    } else {
        //vérifier que l'alias n'est pas déja pris
        $res = $globals->xdb->query('SELECT COUNT(*) FROM virtual WHERE alias={?}', $alias.'@'.$globals->mail->alias_dom);
        if ($res->fetchOneCell() > 0) {
            $page->trig("L'alias $alias@{$globals->mail->alias_dom} a déja été attribué.
                        Tu ne peux donc pas l'obtenir.");
            $page->run('error');
        }

        //vérifier que l'alias n'est pas déja en demande
        $it = new ValidateIterator ();
        while($req = $it->next()) {
            if ($req->type == "alias" and $req->alias == $alias) {
                $page->trig("L'alias $alias@{$globals->mail->alias_dom} a déja été demandé.
                            Tu ne peux donc pas l'obtenir pour l'instant.");
                $page->run('error');
            }
        }

        //Insertion de la demande dans la base, écrase les requêtes précédente
        $myalias = new AliasReq($uid, $alias, $raison);
        $myalias->submit();
        $page->assign('success',$alias);
        $page->run('succes');
    }
}

// montrer son alias
elseif ((Env::get('visible') == 'public') && ($visibility != 'public')) {
    $globals->xdb->execute("UPDATE auth_user_quick SET emails_alias_pub = 'public' WHERE user_id = {?}", Session::getInt('uid'));
    $visibility = 'public';
}

// cacher son alias
elseif ((Env::get('visible') == 'private') && ($visibility != 'private')) {
    $globals->xdb->execute("UPDATE auth_user_quick SET emails_alias_pub = 'private' WHERE user_id = {?}", Session::getInt('uid'));
    $visibility = 'private';
}

if ($visibility == 'public') {
    $page->assign('mail_public', true);
}

$page->run();
?>

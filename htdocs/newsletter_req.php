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
 ***************************************************************************
        $Id: newsletter_req.php,v 1.3 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('newsletter_req.tpl', AUTH_MDP);

if (isset($_POST["action"]) && (($_POST["action"]=="Tester")
            or ($_POST["action"]=="valider"))){
    $article=(isset($_POST["article"])?
            stripslashes(strip_tags($_POST["article"])):"");
    $titre=(isset($_POST["titre"])?
            stripslashes(strip_tags($_POST["titre"])):"");
    $bonus=(isset($_POST["bonus"])?
            stripslashes(strip_tags($_POST["bonus"])):"");

    $page->assign('article', $article);
    $page->assign('titre', $titre);
    $page->assign('bonus', $bonus);

    if (empty($_POST["titre"])) {
        $page->assign('erreur', '<p class="erreur">Tu n\'as pas mentionné de titre !!</p>');
    } elseif (empty($_POST["article"])) {
        $page->assign('erreur', '<p class="erreur"> ton annonce est vide !!</p>');
    } else {
        $exec="echo ".escapeshellarg($article)." | perl "
            ."-MText::Autoformat -e 'autoformat "
            ."{left=>1, right=>68, all=>1, justify=>full };'";
        exec($exec,$result);
        $page->assign('preview', join("\n",$result));

        $page->assign('nb_lines', count($result));

        if ((count($result)<9) and ($_POST["action"]=="valider")) {
            require_once("diogenes.mailer.inc.php");
            $mailer = new DiogenesMailer($_SESSION['username']."@polytechnique.org", 
                    "info+nl@polytechnique.org", 
                    "Proposition d'article pour la newsletter", 
                    false);
            $mailer->setbody(
                    wordwrap($titre,72)."\n\n".
                    join("\n",$result)."\n\n".
                    wordwrap($bonus,72));
            $mailer->send();
            $page->assign('sent', 1);
        }
    }
}

$page->run();
?>

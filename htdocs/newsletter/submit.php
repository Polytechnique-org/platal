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
        $Id: submit.php,v 1.2 2004-10-16 17:53:40 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('newsletter/submit.tpl', AUTH_COOKIE);
require("newsletter.inc.php");

if(isset($_POST['see'])) {
    $art = new NLArticle($_POST['title'], $_POST['body'], $_POST['append']);
    $page->assign('art', $art);
} elseif(isset($_POST['valid'])) {
    $nl = new Newsletter();
    $art = new NLArticle($_POST['title'], $_POST['body'], $_POST['append']);
    $nl->saveArticle($art);

    require("diogenes.mailer.inc.php");
    $to = 'Equipe Newsletter Polytechnique.org <info+nlp@polytechnique.org>';
    $from = "{$_SESSION['prenom']} {$_SESSION['nom']} ({$_SESSION['promo']}) <{$_SESSION['forlife']}@polytechnique.org>";
    $mailer = new DiogenesMailer($from,$to,"proposition d'article dans la NL",false,$from);
    $text = "l'article suivant a été proposé par:\n\n    $from\n\n\n".$art->toText();
    $mailer->setBody($text);
    $mailer->send();
    
    $page->assign('submited', true);
}

$page->run();
?>

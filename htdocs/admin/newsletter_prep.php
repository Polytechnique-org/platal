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
        $Id: newsletter_prep.php,v 1.4 2004-09-02 23:33:56 x2000bedo Exp $
 ***************************************************************************/

require('auto.prepend.inc.php');
new_admin_page('admin/newsletter_prep.tpl');

$from = '"Equipe polytechnique.org" <info_newsletter@polytechnique.org>';
$from_error = 'gld@m4x.org';
$filename = "/home/web/newsletter/newsletter.cur";
$lockfile = "/home/web/newsletter/newsletter.lock";

// regarde si le lock est pris ou pas
$lock = fopen($lockfile,"r+");
// prend un lock en écriture
if (!flock($lock, 2)) {
    $page->assign('erreur','Impossible de prendre un lock sur le fichier de lock, pas de fichier ?');
    $page->run('errlock');
}

$contenu = (isset($_REQUEST['contenu']) ? $_REQUEST['contenu'] : "");
$sujet = (isset($_REQUEST['sujet']) ? $_REQUEST['sujet'] : "");

$nb = fscanf($lock, "%d %s",$date, $id_lock);
$is_lock = ($nb != 0);
$own_lock = false;
if($is_lock) {
	$own_lock = ($id_lock == $_SESSION['forlife']);
}

// action si on recoit un formulaire
$res = true;
$envoi = false;

if (isset($_REQUEST['submit'])) {
    if($_REQUEST['submit'] == "Prendre un verrou") {
        if($is_lock) {
            $page->assign('action_msg', 'Verrou déjà pris');
        } else {
            fputs($lock,time()." ".$_SESSION['forlife']);
            $is_lock = $own_lock = true;
        } // pas de lock
    } elseif ($_REQUEST['submit'] == "Relacher quand meme" 
            || $_REQUEST['submit'] == "Ne pas sauver et relacher le verrou") {
        ftruncate($lock, 0);
        $is_lock = $own_lock = false;
    } elseif ($_REQUEST['submit'] == "Envoi Test") {
        $envoi = true;
        $to = $_REQUEST['test_to'];
    } elseif ($_REQUEST['submit'] == "Envoi Definitif") {
        $envoi = true;
        $to = "newsletter@polytechnique.org";
    } elseif (isset($_REQUEST['contenu'])) {
        $res = false;
        if (!$own_lock) {
            $page->assign('action_msg', 'Pas de verrou, on ne peut pas enregistrer,
                                         sauvegarde tes modifications avec un copier-coller,
                                         prends un verrou si possible puis remets tes modifs
                                         sur la nouvelle version et enregistre');
        } else {
            if (get_magic_quotes_gpc()) {
                $contenu = stripslashes($contenu);
                $contenu = str_replace('','',$contenu);
                $sujet = rtrim(stripslashes($sujet));
            }
            if (($fp = fopen($filename, "w")) == -1) {
                $page->assign('action_msg', "Impossible d'ouvrir le fichier $filename");
            } elseif (fwrite($fp, "$sujet\n")) {
                if (fwrite($fp, $contenu)) {
                    $res = true;
                } else {
                    $page->assign('action_msg', "Impossible d'écrire dans le fichier $filename");
                }
                fclose($fp);
            } else {
                $page->assign('action_msg', "Impossible d'écrire dans le fichier $filename");
                fclose($fp);
            }
            if($_REQUEST['submit'] == "Sauver et relacher le verrou"){
                ftruncate($lock, 0);
                $is_lock = $own_lock = false;
            }
        } // else (!$own_lock)
    }
} // if ifdef(sumbit)

flock($lock, 3); // relache le verrou

if (!isset($_REQUEST['submit']) or $res) {
    // il n'y a pas eu de submit ou il y a eu un submit et 
    // l'ecriture c'est bien passee, on relit le fichier
    $c=file($filename);
    $contenu = '';
    reset($c);
    $sujet = rtrim(current($c));
    while ($line = next($c)) {
        $contenu .= $line;
    }
}

if ($envoi) {
    require("diogenes.mailer.inc.php");
    $FROM = "From: $from";
    $mymail = new DiogenesMailer($from_error, $to, $sujet, false);
    $mymail->addHeader($FROM);
    $mymail->setBody($contenu);
    $mymail->send();
}

$page->assign('date_lock',$date);
$page->assign('own_lock',$own_lock);
$page->assign('id_lock',$id_lock);
$page->assign('is_lock',$is_lock);

$page->assign('contenu', $contenu);
$page->assign('sujet', $sujet);
$page->run();

?>

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

if (Env::has('n')) {
    $sql    = "SELECT * FROM envoidirect WHERE uid='".Env::get('n')."'";
    $result = $globals->db->query($sql);
    if ($ligne  = mysql_fetch_assoc($result)) {

        if (!Env::has('charte')) {
            new_skinned_page('inscription/step1a.tpl', AUTH_PUBLIC);
            $page->run();
        }

        // il faut remettre le matricule dans son format de saisie

        $year = intval(substr($ligne['matricule'],0,4));
        $rang = intval(substr($ligne['matricule'],4,4));
        if($year<1996) {
            $_REQUEST['matricule'] = '';
        } elseif($year<2000) {
            $_REQUEST['matricule'] = sprintf('%02u0%03u',$year % 100,$rang);
        } elseif($year<2100) {
            $_REQUEST['matricule'] = sprintf('1%02u%03u',$year % 100,$rang);
        }
        $_REQUEST['promo']  = $ligne['promo'];
        $_REQUEST['nom']    = $ligne['nom'];
        $_REQUEST['prenom'] = $ligne['prenom'];
        $_REQUEST['email']  = $ligne['email'];

        new_skinned_page('inscription/step2.tpl', AUTH_PUBLIC);
        require_once("identification.inc.php");
        require_once("applis.func.inc.php");

        $page->assign('homonyme', $homonyme);
        $page->assign('forlife',  $forlife);
        $page->assign('mailorg',  $mailorg);
        $page->assign('prenom',   $prenom);
        $page->assign('nom',      $nom);

        $page->assign('envoidirect', Env::get('n'));
        $page->run();
    }
}

new_skinned_page('inscription/maj.tpl', AUTH_PUBLIC);
$page->run();
?>

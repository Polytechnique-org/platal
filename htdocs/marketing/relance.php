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
new_admin_page('marketing/relance.tpl');

/* une relance a été demandée - on envoit les mails correspondants */
if (Post::has('relancer')) {
    require_once("xorg.mailer.inc.php");
    
    $res   = $globals->xdb->query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
    $nbdix = $res->fetchOneCell();
    $res   = $globals->xdb->iterRow(
            "SELECT  r.date, u.promo, u.nom, u.prenom, r.user_id, r.email, r.bestalias
               FROM  register_pending AS r
         INNER JOIN  auth_user_md5    AS u ON u.user_id = e.uid");

    $sent = Array();

    while (list($ldate, $lpromo, $lnom, $lprenom, $uid, $lemail, $lusername) = $res->next()) {
        if (Post::get($uid) == "1") {
            $hash     = rand_url_id(12);
            $pass     = rand_pass();
            $pass_md5 = md5($nveau_pass);
            $fdate    = substr($ldate, 8, 2)."/".substr($ldate, 5, 2)."/".substr($ldate, 0, 4);
            
            $mymail = new XOrgMailer('marketing.relance.tpl');
            $mymail->assign('nbdix',      $nbdix);
            $mymail->assign('fdate',      $fdate);
            $mymail->assign('lusername',  $lusername);
            $mymail->assign('nveau_pass', $pass);
            $mymail->assign('baseurl',    $globals->baseurl);
            $mymail->assign('lins_id',    $hash);
            $mymail->assign('lemail',     $lemail);
            $mymail->assign('subj',       $lusername."@polytechnique.org");

            $globals->xdb->execute("UPDATE register_pending SET hash={?}, password={?}, relance=NOW() WHERE uid={?}",
                    $lins_id, $lpass, $uid);
            $mymail->send();

            $sent[] = "$lprenom $lnom ($lpromo) a été relancé !";
        }
    }
    $page->assign_by_ref('sent', $sent);
}

$sql = "SELECT  r.date, r.relance, r.uid, u.promo, u.nom, u.prenom
          FROM  register_pending AS r
    INNER JOIN  auth_user_md5    AS u ON r. uid = u.user_id
      ORDER BY  date DESC";
$page->assign('relance', $globals->xdb->iterator($sql));

$page->run();
?>

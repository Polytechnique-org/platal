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
        $Id: relance.php,v 1.2 2004-08-31 10:03:30 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('marketing/relance.tpl');


/* une relance a été demandée - on envoit les mails correspondants */
if (isset($_POST["relancer"]) && isset($_POST["relancer"]) != "") {
    require("tpl.mailer.inc.php");
    
    
    $res=mysql_query("SELECT COUNT(*) FROM auth_user_md5");
    list($nbdix) = mysql_fetch_row($res);
    mysql_free_result($res);

    $res = mysql_query("SELECT  e.date,e.promo,e.nom,e.prenom,e.matricule,e.email,e.username
                          FROM  en_cours      AS e
                     LEFT JOIN  auth_user_md5 AS a ON e.matricule=a.matricule
                         WHERE  a.nom IS null");

    $sent = Array();

    while (list($ldate, $lpromo, $lnom, $lprenom, $lmatricule, $lemail, $lusername) = mysql_fetch_row($res)) {
        if (isset($_POST[$lmatricule]) && $_POST[$lmatricule] == "1") {
            $lins_id = rand_url_id(12);
            $nveau_pass = rand_pass();
            $lpass = md5($nveau_pass);
            $fdate = substr($ldate, 8, 2)."/".substr($ldate, 5, 2)."/".substr($ldate, 0, 4);
            
            $mymail = new TplMailer('marketing.relance.tpl');
            $mymail->assign('nbdix',$nbdix);
            $mymail->assign('fdate',$fdate);
            $mymail->assign('lusername',$lusername);
            $mymail->assign('nveau_pass',$nveau_pass);
            $mymail->assign('baseurl',$baseurl);
            $mymail->assign('lins_id',$lins_id);
            
            $mymail->assign('lemail',$lemail);
            $mymail->assign('subj',$lusername."@polytechnique.org");

            mysql_query("UPDATE  en_cours
                            SET  ins_id='$lins_id',password='$lpass',relance='".date("Y-m-j")."'
                          WHERE  matricule = '$lmatricule'");
            // envoi du mail à l'utilisateur

            $mymail->send();

            $sent[] = "$lprenom $lnom ($lpromo) a été relancé !";
        }
    }
    $page->assign_by_ref('sent', $sent);

/* pas d'action particulière => on affiche la liste des relançables... */
}

$sql = "SELECT  e.date,e.relance,e.promo,e.nom,e.prenom,e.matricule
          FROM  en_cours      AS e
     LEFT JOIN  auth_user_md5 AS a ON e.matricule=a.matricule
         WHERE  a.nom IS null
      ORDER BY  date DESC";

$page ->mysql_assign($sql, 'relance','nb');

$page->run();
?>

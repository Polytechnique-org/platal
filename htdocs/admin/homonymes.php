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
        $Id: homonymes.php,v 1.2 2004-08-31 10:03:29 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/homonymes.tpl',true);
require("diogenes.mailer.inc.php");

$op =  isset($_REQUEST['op']) ? $_REQUEST['op'] : 'list';


$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : 0;
if ($target) {
    $res = $globals->db->query("SELECT prenom,username,loginbis FROM auth_user_md5 WHERE user_id='$target'");
    if (! list($prenom,$username,$loginbis) = mysql_fetch_row($res)) {
        $target=0;
    } else {
        $page->assign('prenom',$prenom);
        $page->assign('username',$username);
        $page->assign('loginbis',$loginbis);
    }
}

$page->assign('op',$op);
$page->assign('target',$target);
$page->assign('baseurl',$baseurl);

// on a un $target valide, on prepare les mails
if ($target) {
  // from
  $cc = "support+homonyme@polytechnique.org";
  $FROM = "From: Support Polytechnique.org <$cc>";
  
  // on examine l'op a effectuer
  switch ($op) {
      case 'mail':
          $mymail = new DiogenesMailer($cc,$username,"Dans 2 semaines, suppression de $loginbis@polytechnique.org",false,$cc);
          $mymail->addHeader($FROM);
          $mymail->setBody(stripslashes($_REQUEST['mailbody']));
          $mymail->send();
          $op = 'list';
          break;
      case 'correct':
          $globals->db->query("update auth_user_md5 set alias='' where user_id=$target");
          $mymail = new DiogenesMailer($cc,$username,"Mise en place du robot $loginbis@polytechnique.org",false,$cc);
          $mymail->addHeader($FROM);
          $mymail->setBody(stripslashes($_REQUEST['mailbody']));
          $mymail->send(); 
          $op = 'list';
          break;
  }
}
if ($op == 'list') {
    $res = $globals->db->query("SELECT loginbis FROM auth_user_md5 WHERE loginbis!='' GROUP BY loginbis ORDER BY loginbis");
    $hnymes = Array();
    while (list($loginbis) = mysql_fetch_row($res)) $hnymes[$loginbis] = Array();
    mysql_free_result($res);

    $res = $globals->db->query("SELECT loginbis,user_id,username,promo,prenom,nom,alias,date_mise_alias_temp AS date FROM auth_user_md5 WHERE loginbis!='' ORDER BY promo");
    while ($tab = mysql_fetch_assoc($res)) $hnymes[$tab['loginbis']][] = $tab;
    mysql_free_result($res);

    $page->assign_by_ref('hnymes',$hnymes);
}

$page->run();
?>

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
        $Id: homonymes.php,v 1.6 2004-11-13 14:16:16 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/homonymes.tpl');
require("diogenes.mailer.inc.php");

$op =  isset($_REQUEST['op']) ? $_REQUEST['op'] : 'list';


$target = isset($_REQUEST['target']) ? $_REQUEST['target'] : 0;
if ($target) {
    $res = $globals->db->query("SELECT  prenom,a.alias AS forlife,h.alias AS loginbis
                                  FROM  auth_user_md5 AS u
			    INNER JOIN  aliases       AS a ON (a.id=u.user_id AND a.type='a_vie')
			    INNER JOIN  aliases       AS h ON (h.id=u.user_id AND h.expire!='')
			         WHERE  user_id='$target'");
    if (! list($prenom,$forlife,$loginbis) = mysql_fetch_row($res)) {
        $target=0;
    } else {
        $page->assign('prenom',$prenom);
        $page->assign('forlife',$forlife);
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
          $mymail = new DiogenesMailer($cc,$forlife,"Dans 2 semaines, suppression de $loginbis@polytechnique.org",false,$cc);
          $mymail->addHeader($FROM);
          $mymail->setBody(stripslashes($_REQUEST['mailbody']));
          $mymail->send();
          $op = 'list';
          break;
      case 'correct':
          $globals->db->query("UPDATE aliases SET type='homonyme',expire=NOW() WHERE alias='$loginbis'");
          $globals->db->query("REPLACE INTO homonymes (homonyme_id,user_id) VALUES('$target','$target')");
          $mymail = new DiogenesMailer($cc,$forlife,"Mise en place du robot $loginbis@polytechnique.org",false,$cc);
          $mymail->addHeader($FROM);
          $mymail->setBody(stripslashes($_REQUEST['mailbody']));
          $mymail->send(); 
          $op = 'list';
          break;
  }
}

if ($op == 'list') {
    $res = $globals->db->query("SELECT  a.alias AS homonyme,s.id AS user_id,s.alias AS forlife,
					promo,prenom,nom,
					IF(h.homonyme_id=s.id, a.expire, NULL) AS expire,
					IF(h.homonyme_id=s.id, a.type, NULL) AS type
				  FROM  aliases       AS a
			     LEFT JOIN  homonymes     AS h ON (h.homonyme_id = a.id)
			    INNER JOIN  aliases       AS s ON (s.id = h.user_id AND s.type='a_vie')
			    INNER JOIN  auth_user_md5 AS u ON (s.id=u.user_id)
			         WHERE  a.type='homonyme' OR a.expire!=''
			      ORDER BY  a.alias,promo");
    $hnymes = Array();
    while ($tab = mysql_fetch_assoc($res)) {
	$hnymes[$tab['homonyme']][] = $tab;
    }
    mysql_free_result($res);

    $page->assign_by_ref('hnymes',$hnymes);
}

$page->run();
?>

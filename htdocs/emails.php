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
new_skinned_page('emails.tpl',AUTH_COOKIE);

$uid = Session::getInt('uid');

if (Post::has('best')) {
    $globals->db->query("UPDATE  aliases SET flags='' WHERE flags='bestalias' AND id=$uid");
    $globals->db->query("UPDATE  aliases SET flags='epouse' WHERE flags='epouse,bestalias' AND id=$uid");
    $globals->db->query("UPDATE  aliases
		            SET  flags=CONCAT(flags,',','bestalias')
			  WHERE  id=$uid AND alias='".Post::get('best')."'");
}

// on regarde si on a affaire à un homonyme
$sql = "SELECT  alias, (type='a_vie') AS a_vie, FIND_IN_SET('bestalias',flags) AS best, expire
          FROM  aliases
         WHERE  id=$uid AND type!='homonyme'
      ORDER BY  LENGTH(alias)";
$page->mysql_assign($sql, 'aliases');

$sql = "SELECT email
        FROM emails
        WHERE uid = $uid AND FIND_IN_SET('active', flags)";
$page->mysql_assign($sql, 'mails', 'nb_mails');


// on regarde si l'utilisateur a un alias et si oui on l'affiche !
$forlife = Session::get('forlife');
$sql = "SELECT  alias
          FROM  virtual          AS v
    INNER JOIN  virtual_redirect AS vr USING(vid)
         WHERE  (  redirect='$forlife@{$globals->mail->domain}'
                OR redirect='$forlife@{$globals->mail->domain2}' )
                AND alias LIKE '%@{$globals->mail->alias_dom}'";
$result = $globals->db->query($sql);
if ($result && list($aliases) = mysql_fetch_row($result)) {
    list($melix) = split('@', $aliases);
    $page->assign('melix', $melix);
}
mysql_free_result($result);

$page->run();
?> 

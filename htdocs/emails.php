<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
$page->assign('xorg_title','Polytechnique.org - Mes emails');

$uid = Session::getInt('uid');

if (Post::has('best')) {
    // bestalias is the first bit : 1
    // there will be maximum 8 bits in flags : 255
    $globals->xdb->execute("UPDATE  aliases SET flags=flags & (255 - 1) WHERE id={?}", $uid);
    $globals->xdb->execute("UPDATE  aliases SET flags=flags | 1 WHERE id={?} AND alias={?}", $uid, Post::get('best'));
}

// on regarde si on a affaire à un homonyme
$sql = "SELECT  alias, (type='a_vie') AS a_vie, (alias REGEXP '\\\\.[0-9]{2}$') AS cent_ans, FIND_IN_SET('bestalias',flags) AS best, expire
          FROM  aliases
         WHERE  id = {?} AND type!='homonyme'
      ORDER BY  LENGTH(alias)";
$page->assign('aliases', $globals->xdb->iterator($sql, $uid));

$sql = "SELECT email
        FROM emails
        WHERE uid = {?} AND FIND_IN_SET('active', flags)";
$page->assign('mails', $globals->xdb->iterator($sql, $uid));


// on regarde si l'utilisateur a un alias et si oui on l'affiche !
$forlife = Session::get('forlife');
$res = $globals->xdb->query(
        "SELECT  alias
           FROM  virtual          AS v
     INNER JOIN  virtual_redirect AS vr USING(vid)
          WHERE  (redirect={?} OR redirect={?}) 
                 AND alias LIKE '%@{$globals->mail->alias_dom}'",
        $forlife.'@'.$globals->mail->domain, $forlife.'@'.$globals->mail->domain2);
$page->assign('melix', $res->fetchOneCell());

$page->run();
?> 

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

function select_if_homonyme($uid) {
    $res = XDB::query("SELECT  prenom,nom,a.alias AS forlife,h.alias AS loginbis
                                   FROM  auth_user_md5 AS u
                             INNER JOIN  aliases       AS a ON (a.id=u.user_id AND a.type='a_vie')
                             INNER JOIN  aliases       AS h ON (h.id=u.user_id AND h.expire!='')
                                  WHERE  user_id = {?}", $uid);
    return $res->fetchOneRow();
}

function send_warning_homonyme($prenom, $nom, $forlife, $loginbis) {
    require_once("diogenes/diogenes.hermes.inc.php");
    $cc = "support+homonyme@polytechnique.org";
    $FROM = "\"Support Polytechnique.org\" <$cc>";
    $mymail = new HermesMailer();
    $mymail->setFrom($FROM);
    $mymail->setSubject("Dans 2 semaines, suppression de $loginbis@polytechnique.org");
    $mymail->addTo("$prenom $nom <$forlife@polytechnique.org>");
    $mymail->addCc($cc);
    $mymail->setTxtBody(Env::get('mailbody'));
    $mymail->send();
}

function send_robot_homonyme($prenom, $nom, $forlife, $loginbis) {
    require_once("diogenes/diogenes.hermes.inc.php");
    $cc = "support+homonyme@polytechnique.org";
    $FROM = "\"Support Polytechnique.org\" <$cc>";
    $mymail = new HermesMailer();
    $mymail->setFrom($FROM);
    $mymail->setSubject("Mise en place du robot $loginbis@polytechnique.org");
    $mymail->addTo("$prenom $nom <$forlife@polytechnique.org>");
    $mymail->addCc($cc);
    $mymail->setTxtBody(Env::get('mailbody'));
    $mymail->send();
}

function switch_bestalias($uid, $loginbis) {
    // check if loginbis was the bestalias
    $res = XDB::query("SELECT alias FROM aliases WHERE id = {?} AND FIND_IN_SET('bestalias', flags)", $uid);
    $bestalias = $res->fetchOneCell();
    if ($bestalias && $bestalias != $loginbis) return false;
    
    // select the shortest alias still alive
    $res = XDB::query("SELECT alias FROM aliases WHERE id = {?} AND alias != {?} AND expire IS NULL ORDER BY LENGTH(alias) LIMIT 1", $uid, $loginbis);
    $newbest = $res->fetchOneCell();
    // change the bestalias flag
    XDB::execute("UPDATE aliases SET flags = (flags & (255 - 1)) | IF(alias = {?}, 1, 0) WHERE id = {?}", $newbest, $uid);

    return $newbest;
}

?>

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
new_skinned_page('stats/coupure.tpl',AUTH_PUBLIC);

function serv_to_str($params) {
    $flags = explode(',',$params);
    $trad = Array('web' => 'site web', 'mail'=> 'redirection mail',
                  'smtp' => 'serveur sécurisé d\'envoi de mails',
                  'nntp' => 'serveur des forums de discussion');
    $ret = Array();
    foreach ($flags as $flag) {
        $ret[] = $trad[$flag];
    }
    return implode(', ',$ret);
}

if (Env::has('cp_id')) {
    $res = $globals->db->query("SELECT  UNIX_TIMESTAMP(debut) AS debut,
                                        TIME_FORMAT(duree,'%kh%i') AS duree,
                                        resume, description, services
                                  FROM  coupures
                                 WHERE  id = ".Env::getInt('cp_id'));
    $cp = @mysql_fetch_assoc($res);
}

if($cp) {
    $cp['lg_services'] = serv_to_str($cp['services']);
    $page->assign_by_ref('cp',$cp);
} else {
    $beginning_date = date("Ymd", time() - 3600*24*21) . "000000";
    $sql = "select id, UNIX_TIMESTAMP(debut) as debut, resume, services from coupures where debut > '" . $beginning_date
        .  "' order by debut desc";
    $page->mysql_assign($sql, 'coupures');
}

$page->run();
?>

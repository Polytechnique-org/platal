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

$sql = "DELETE FROM perte_pass WHERE DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created";
$globals->db->query($sql);

$certificat = isset($_REQUEST['certificat']) ? $_REQUEST['certificat'] : "";
$sql = "SELECT uid FROM perte_pass WHERE certificat='$certificat'";
$result = $globals->db->query($sql);

if ($ligne = mysql_fetch_array($result))  {
    $uid=$ligne["uid"];
    if (!empty($_POST['response2']))  {             // la variable $response existe-t-elle ?
    // OUI, alors changeons le mot de passe
        $password = $_POST['response2'];
        $sql = "UPDATE auth_user_md5 SET password='$password' WHERE user_id='$uid' AND perms IN('admin','user')";
        $globals->db->query($sql);
        $logger = new DiogenesCoreLogger($uid);
        $logger->log("passwd","");
        $sql = "DELETE FROM perte_pass WHERE certificat='$certificat'";
        $globals->db->query($sql);
        new_skinned_page('tmpPWD.success.tpl', AUTH_PUBLIC);
        $page->run();
    } else {
        new_skinned_page('motdepassemd5.tpl', AUTH_PUBLIC, 'motdepassemd5.head.tpl');
        $page->run();
    }
} else {
    new_skinned_page('index.tpl', AUTH_PUBLIC);
    $page->kill("Cette adresse n'existe pas ou n'existe plus sur le serveur.");
}

?>

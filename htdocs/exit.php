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
        $Id: exit.php,v 1.5 2004-09-02 18:37:14 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_MDP);

if (isset($_SESSION['suid'])) {
    $res = @$globals->db->query( "SELECT prenom,nom,promo,perms FROM auth_user_md5 WHERE user_id='{$_SESSION['suid']}'");
    if(@mysql_num_rows($res) != 0) {
        list($prenom,$nom,$promo,$perms)=mysql_fetch_row($res);
        // on rétablit les loggers
        // on loggue la fermeture de la session de su
        $log_data = "{$_SESSION['prenom']} {$_SESSION['nom']} {$_SESSION['promo']} by $prenom $nom $promo";
        $_SESSION['log']->log("suid_stop",$log_data);
        $_SESSION['log'] = $_SESSION['slog'];
        unset($_SESSION['slog']);
        $_SESSION['log']->log("suid_stop",$log_data);
        // on remet en place les variables de sessions modifiées par le su
        $_SESSION['uid']  = $_SESSION['suid'];
        unset($_SESSION['suid']);
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['promo'] = $promo;
        $_SESSION['perms'] = $perms;
    }
}

header("Location: login.php");
?>

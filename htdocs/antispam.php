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
        $Id: antispam.php,v 1.7 2004-08-31 22:01:30 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('antispam.tpl', AUTH_MDP);

require("mtic.inc.php");

if (isset($_REQUEST['filtre']) and isset($_REQUEST['statut_filtre'])) {
    // mise à jour du filtre
    $result = $globals->db->query("select find_in_set('drop', flags) from emails where uid = {$_SESSION['uid']} and num = 0 and find_in_set('active', flags)");
    list($filtre) = mysql_fetch_row($result);
    mysql_free_result($result);
    $new_filtre = (integer)$_REQUEST['statut_filtre'];
    if ($new_filtre == 0 and isset($filtre)) {
        // désactive le filtre
        // échange les flags active et filtre d'un seul coup (de manière atomique)
        $globals->db->query("UPDATE emails SET flags=IF(num=0, REPLACE(flags,'active','filtre'), REPLACE(flags,'filtre','active'))
                     WHERE uid={$_SESSION['uid']} AND (find_in_set('active',flags) OR FIND_IN_SET('filtre',flags))");
        // supprime la ligne num=0
        $globals->db->query("delete from emails where uid={$_SESSION['uid']} and num = 0");
    } elseif ($new_filtre != 0) {
        // active le filtre
        // ajoute la ligne num=0 avec le bon pipe et un flag filtre et pas de flag active
        //  si le filtre n'est pas déjà actif et directement en actif si le filtre est déjà actif.
        $globals->db->query("replace into emails set uid = {$_SESSION['uid']}, num = 0,
                     email = '\"|maildrop /var/mail/.maildrop_filters/"
                    .($new_filtre == 2 ? 'drop_spams':'tag_spams')." {$_SESSION['uid']}\"',
                     flags = '".(isset($filtre) ? 'active' : 'filtre')
                    .($new_filtre == 2 ? ',drop' : '')."'");
        // échange les flags active et filtre d'un seul coup (de manière atomique) si le filtre n'est pas déjà actif
        if (!isset($filtre))
            $globals->db->query("UPDATE emails
                         SET flags=IF(FIND_IN_SET('active',flags), REPLACE(flags,'active','filtre'), REPLACE(flags,'filtre','active'))
                         WHERE uid={$_SESSION['uid']} AND (FIND_IN_SET('active',flags) OR FIND_IN_SET('filtre',flags))");
    }
}

$result = $globals->db->query("SELECT FIND_IN_SET('drop', flags)!=0
				 FROM emails
				 WHERE uid = {$_SESSION['uid']} AND num = 0 AND find_in_set('active', flags)");
list($filtre) = mysql_fetch_row($result);
$filtre += mysql_num_rows($result);
mysql_free_result($result);

$page->assign('filtre',$filtre);

$page->run();
?>

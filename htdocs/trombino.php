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

require_once('xorg.inc.php');
new_skinned_page('trombino.tpl', AUTH_MDP);

require_once('validations.inc.php');

if (Env::has('ordi') and
        isset($_FILES['userfile']) and isset($_FILES['userfile']['tmp_name'])) {
    //Fichier en local
    $myphoto = new PhotoReq(Session::getInt('uid'), $_FILES['userfile']['tmp_name']);
    $myphoto->submit();
} elseif (Env::has('web') and Env::has('photo')) {
    // net
    $fp = fopen(Env::get('photo'), 'r');
    if (!$fp) {
        $page->trig('Fichier inexistant');
    } else {
        $attach = fread($fp, 35000);
        fclose($fp);
        $file = tempnam('/tmp','photo_');
        $fp   = fopen($file,'w');
        fwrite($fp, $attach);
        fclose($fp);

        $myphoto = new PhotoReq(Session::getInt('uid'), $file);
        $myphoto->submit();
    }
} elseif (Env::has('trombi')) {
    // Fichier à récupérer dans les archives trombi + commit immédiat
    $file = '/home/web/trombino/photos'.Session::get('promo').'/'.Session::get('forlife').'.jpg';
    $myphoto = new PhotoReq(Session::getInt('uid'), $file);
    if($myphoto){// There was no errors, we can go on
        $myphoto->commit();
        $myphoto->clean();
    }
} elseif (Env::get('suppr')) {
    // effacement de la photo
    $globals->xdb->execute('DELETE FROM photo WHERE uid = {?}', Session::getInt('uid'));
    $globals->xdb->execute('DELETE FROM requests WHERE user_id = {?} AND type="photo"', Session::getInt('uid'));
}

$sql = $globals->xdb->query('SELECT COUNT(*) FROM requests WHERE user_id={?} AND type="photo"', Session::getInt('uid'));
$page->assign('submited', $sql->fetchOneCell());

$page->run();

// Affichage de la page principale
?>

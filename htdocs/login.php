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
new_skinned_page('login.tpl', AUTH_COOKIE);

$res = $globals->xdb->query('SELECT date FROM auth_user_md5 WHERE user_id={?}', Session::getInt('uid'));
list($date) = $res->fetchOneRow();

// incitation à mettre à jour la fiche

$d2  = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
if( (time() - $d2) > 60 * 60 * 24 * 400 ) {
    // si fiche date de + de 400j;
    $page->assign('fiche_incitation', $date);
}

// incitation à mettre une photo

$res = $globals->xdb->query('SELECT COUNT(*) FROM photo WHERE uid={?}', Session::getInt('uid'));
$page->assign('photo_incitation', $res->fetchOneCell() == 0);

// affichage de la boîte avec quelques liens

require_once('login.conf.php') ;
$pub_nbElem = $pub_nbLig * $pub_nbCol ;
if (count($pub_tjs) <= $pub_nbElem)
    $publicite = array_slice ($pub_tjs,0,$pub_nbElem) ;
else
    $publicite = $pub_tjs ;
$nbAlea = $pub_nbElem - count($publicite) ;
if ($nbAlea > 0) {
    $choix = array_rand($pub_rnd,$nbAlea) ;
    foreach ($choix as $url)
        $publicite[$url] = $pub_rnd[$url] ;
    }
$publicite = array_chunk( $publicite , $pub_nbLig , true ) ;
$page->assign_by_ref('publicite', $publicite);

// affichage des evenements
// annonces promos triées par présence d'une limite sur les promos
// puis par dates croissantes d'expiration
$promo = Session::getInt('promo');
$sql = "SELECT  e.id,e.titre,e.texte,a.user_id,a.nom,a.prenom,a.promo,l.alias AS forlife
          FROM  evenements    AS e
    INNER JOIN  auth_user_md5 AS a ON e.user_id=a.user_id
    INNER JOIN  aliases       AS l ON ( a.user_id=l.id AND l.type='a_vie' )
         WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
		AND (e.promo_min = 0 || e.promo_min <= {?})
		AND (e.promo_max = 0 || e.promo_max >= {?})
      ORDER BY  (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
$page->assign('evenement', $globals->xdb->iterator($sql, $promo, $promo));

$page->assign('toto',"");
$page->assign('tata',"1");

$page->run();
?>

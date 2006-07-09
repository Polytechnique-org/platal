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

require_once('xorg.inc.php');
new_skinned_page('login.tpl', AUTH_COOKIE);

$res = $globals->xdb->query('SELECT date, naissance FROM auth_user_md5 WHERE user_id={?}', Session::getInt('uid'));
list($date, $naissance) = $res->fetchOneRow();

// incitation à mettre à jour la fiche

$d2  = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
if( (time() - $d2) > 60 * 60 * 24 * 400 ) {
    // si fiche date de + de 400j;
    $page->assign('fiche_incitation', $date);
}

// Souhaite bon anniversaire

if (substr($naissance, 5) == date("m-d")) {
	$page->assign('birthday', date("Y") - substr($naissance, 0, 4));
}

// incitation à mettre une photo

$res = $globals->xdb->query('SELECT COUNT(*) FROM photo WHERE uid={?}', Session::getInt('uid'));
$page->assign('photo_incitation', $res->fetchOneCell() == 0);

// Incitation à se géolocaliser
require_once('geoloc.inc.php');
$res = localize_addresses(Session::getInt('uid', -1));
$page->assign('geoloc_incitation', count($res));

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

// ajout du lien RSS

if (Session::has('core_rss_hash')) {
    $page->assign('xorg_rss',
                  array("title" => "Polytechnique.org :: News",
                        "href" => "/rss/".Session::get('forlife')."/".Session::get('core_rss_hash')."/rss.xml"));
}

// cache les evenements lus et raffiche les evenements a relire
if (Env::has('lu')){
  $globals->xdb->execute('DELETE FROM evenements_vus AS ev INNER JOIN evenements AS e ON e.id = ev.evt_id WHERE peremption < NOW)');
  $globals->xdb->execute('REPLACE INTO evenements_vus VALUES({?},{?})', Env::get('lu'), Session::getInt('uid'));
}
if (Env::has('nonlu')){
  $globals->xdb->execute('DELETE FROM evenements_vus WHERE evt_id = {?} AND user_id = {?}', Env::get('nonlu'), Session::getInt('uid'));
}

// affichage des evenements
// annonces promos triées par présence d'une limite sur les promos
// puis par dates croissantes d'expiration
$promo = Session::getInt('promo');
$sql = "SELECT  e.id,e.titre,e.texte,a.user_id,a.nom,a.prenom,a.promo,l.alias AS forlife
          FROM  evenements    AS e
    INNER JOIN  auth_user_md5 AS a ON e.user_id=a.user_id
    INNER JOIN  aliases       AS l ON ( a.user_id=l.id AND l.type='a_vie' )
    LEFT JOIN   evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
         WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
		AND (e.promo_min = 0 || e.promo_min <= {?})
		AND (e.promo_max = 0 || e.promo_max >= {?})
		AND ev.user_id IS NULL
      ORDER BY  (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
$page->assign('evenement', $globals->xdb->iterator($sql, Session::getInt('uid'), $promo, $promo));

$sql = "SELECT  e.id,e.titre, ev.user_id IS NULL AS nonlu
          FROM  evenements    AS e
    LEFT JOIN   evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
         WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
		AND (e.promo_min = 0 || e.promo_min <= {?})
		AND (e.promo_max = 0 || e.promo_max >= {?})
      ORDER BY  (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
$page->assign('evenement_summary', $globals->xdb->iterator($sql, Session::getInt('uid'), $promo, $promo));

$page->assign('refe',$_SERVER['PHP_SELF']);

$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>

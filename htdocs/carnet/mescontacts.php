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
new_skinned_page("carnet/mescontacts.tpl",AUTH_COOKIE);
require_once("applis.func.inc.php");
$page->assign('xorg_title','Polytechnique.org - Mes contacts');
        
$uid  = Session::getInt('uid');
$user = Env::get('user');

switch (Env::get('action')) {
    case 'retirer':
	if (preg_match('/^\d+$/', $user)) {
	    if ($globals->xdb->execute('DELETE FROM contacts WHERE uid = {?} AND contact = {?}', $uid, $user))
            {
		$page->trig("Contact retiré !");
            }
	} else {
	    if ($globals->xdb->execute(
                        'DELETE FROM  contacts
                               USING  contacts AS c
                          INNER JOIN  aliases  AS a ON (c.contact=a.id and a.type!="homonyme")
                               WHERE  c.uid = {?} AND a.alias={?}', $uid, $user))
            {
		$page->trig("Contact retiré !");
            }
	}
        break;

    case 'ajouter':
        require_once('user.func.inc.php');
        if (($login = get_user_login($user)) !== false) {
            if ($globals->xdb->execute(
                        'INSERT INTO  contacts (uid, contact)
                              SELECT  {?}, id
                                FROM  aliases
                               WHERE  alias = {?}', $uid, $login))
            {
                $page->trig('Contact ajouté !');
            } else {
                $page->trig('Contact déjà dans la liste !');
            }
        }
}

if(Get::get('trombi')) {
    require_once('trombi.inc.php');
    function getList($offset,$limit) {
	global $globals;
        $uid   = Session::getInt('uid');
	$res   = $globals->xdb->query("SELECT COUNT(*) FROM contacts WHERE uid = {?}", $uid);
	$total = $res->fetchOneCell();

        $order = Get::get('order');
        $orders = Array(
            'nom'     => 'nom DESC, u.prenom, u.promo',
            'promo'   => 'promo DESC, nom, u.prenom',
            'last'    => 'u.date DESC, nom, u.prenom, promo');
        if ($order != 'promo' && $order != 'last')
            $order = 'nom';
        $order = $orders[$order];
        if (Get::get('inv') == '')
            $order = str_replace(" DESC,", ",", $order);
            
	$res   = $globals->xdb->query("
	    	SELECT  u.prenom, IF(u.nom_usage='',u.nom,u.nom_usage) AS nom, a.alias AS forlife, u.promo
		  FROM  contacts       AS c
	    INNER JOIN  auth_user_md5  AS u   ON (u.user_id = c.contact)
	    INNER JOIN  aliases        AS a   ON (u.user_id = a.id AND a.type='a_vie')
		 WHERE  c.uid = {?}
	      ORDER BY  $order
		 LIMIT  {?}, {?}", $uid, $offset*$limit, $limit);
        $list  = $res->fetchAllAssoc();

	return Array($total, $list);
    }
    
    $trombi = new Trombi('getList');
    $trombi->setNbRows(4);
    $page->assign_by_ref('trombi',$trombi);

    $order = Get::get('order');
    if ($order != 'promo' && $order != 'last')
        $order = 'nom';
    $page->assign('order', $order);
    $page->assign('inv', Get::get('inv'));
} else {

    $order = Get::get('order');
    $orders = Array(
        'nom'     => 'sortkey DESC, a.prenom, a.promo',
        'promo'   => 'promo DESC, sortkey, a.prenom',
        'last'    => 'a.date DESC, sortkey, a.prenom, promo');
    if ($order != 'promo' && $order != 'last')
        $order = 'nom';
    $page->assign('order', $order);
    $page->assign('inv', Get::get('inv'));
    $order = $orders[$order];
    if (Get::get('inv') == '')
        $order = str_replace(" DESC,", ",", $order);
    
    $sql = "SELECT  contact AS id,
		    a.*, l.alias AS forlife,
		    1 AS inscrit,
		    a.perms != 'pending' AS wasinscrit,
		    a.deces != 0 AS dcd, a.deces, a.matricule_ax, FIND_IN_SET('femme', a.flags) AS sexe,
		    e.entreprise, es.label AS secteur, ef.fonction_fr AS fonction,
		    IF(n.nat='',n.pays,n.nat) AS nat, n.a2 AS iso3166,
		    ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
		    ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
		    adr.city, gp.a2, gp.pays AS countrytxt, gr.name AS region,
		    IF(a.nom_usage<>'',a.nom_usage,a.nom) AS sortkey
	      FROM  contacts       AS c
        INNER JOIN  auth_user_md5  AS a   ON (a.user_id = c.contact)
        INNER JOIN  aliases        AS l   ON (a.user_id = l.id AND l.type='a_vie')
         LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = a.user_id)
         LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
         LEFT JOIN  fonctions_def  AS ef  ON (e.fonction = ef.id)
         LEFT JOIN  geoloc_pays    AS n   ON (a.nationalite = n.a2)
         LEFT JOIN  applis_ins     AS ai0 ON (a.user_id = ai0.uid AND ai0.ordre = 0)
         LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
         LEFT JOIN  applis_ins     AS ai1 ON (a.user_id = ai1.uid AND ai1.ordre = 1)
         LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
         LEFT JOIN  adresses       AS adr ON (a.user_id = adr.uid AND FIND_IN_SET('active', adr.statut))
         LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
         LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
             WHERE  c.uid = $uid
          ORDER BY  ".$order;
    
    $page->assign_by_ref('citer', $globals->xdb->iterator($sql));
}

$page->run();

// vim:set et sw=4 sts=4 sws=4:
?>

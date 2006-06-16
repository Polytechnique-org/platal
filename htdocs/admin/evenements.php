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
new_admin_page('admin/evenements.tpl');
$page->assign('xorg_title','Polytechnique.org - Administration - Evenements');

$arch = Env::get('arch', 0);
$evid = Post::getInt('evt_id');
$page->assign('arch', $arch);

switch(Post::get('action')) {
    case "Proposer":
        $globals->xdb->execute('UPDATE evenements SET titre={?}, texte={?}, peremption={?}, promo_min={?}, promo_max={?} WHERE id = {?}', 
                Post::get('titre'), Post::get('texte'), Post::get('peremption'), Post::get('promo_min'), Post::get('promo_max'), $evid);
        break;

    case "Valider":
        $globals->xdb->execute('UPDATE evenements SET creation_date = creation_date, flags = CONCAT(flags,",valide") WHERE id = {?}', $evid);
        break;

    case "Invalider":
        $globals->xdb->execute('UPDATE evenements SET creation_date = creation_date, flags = REPLACE(flags,"valide", "") WHERE id = {?}', $evid);
        break;

    case "Supprimer":
        $globals->xdb->execute('DELETE from evenements WHERE id = {?}', $evid);
        break;

    case "Archiver":
        $globals->xdb->execute('UPDATE evenements SET creation_date = creation_date, flags = CONCAT(flags,",archive") WHERE id = {?}', $evid);
        break;

    case "Desarchiver":
        $globals->xdb->execute('UPDATE evenements SET creation_date = creation_date, flags = REPLACE(flags,"archive","") WHERE id = {?}', $evid);
        break;

    case "Editer":
        $res = $globals->xdb->query('SELECT titre, texte, peremption, promo_min, promo_max FROM evenements WHERE id={?}', $evid);
        list($titre, $texte, $peremption, $promo_min, $promo_max) = $res->fetchOneRow();
        $page->assign('mode', 'edit');
        $page->assign('titre',$titre);
        $page->assign('texte',$texte);
        $page->assign('promo_min',$promo_min);
        $page->assign('promo_max',$promo_max);
        $page->assign('peremption',$peremption);

        $select = "";
        for ($i = 1 ; $i < 30 ; $i++) {
            $p_stamp=date("Ymd",time()+3600*24*$i);
            $year=substr($p_stamp,0,4);
            $month=substr($p_stamp,4,2);
            $day=substr($p_stamp,6,2);

            $select .= "<option value=\"$p_stamp\"" . (($p_stamp == strtr($peremption, array("-" => ""))) ? " selected" : "")."> $day / $month / $year</option>\n";
        }
        $page->assign('select',$select);

        break;
}

if ($action != "Editer") {

    $sql = "SELECT  e.id, e.titre, e.texte,
                    DATE_FORMAT(e.creation_date,'%d/%m/%Y %T') AS creation_date,
                    DATE_FORMAT(e.peremption,'%d/%m/%Y') AS peremption,
                    e.promo_min, e.promo_max,
                    FIND_IN_SET('valide', e.flags) AS fvalide,
                    FIND_IN_SET('archive', e.flags) AS farch,
                    u.promo, u.nom, u.prenom, a.alias AS forlife
              FROM  evenements    AS e
        INNER JOIN  auth_user_md5 AS u ON(e.user_id = u.user_id)
        INNER JOIN  aliases AS a ON (u.user_id = a.id AND a.type='a_vie')
             WHERE  ".($arch ? "" : "!")."FIND_IN_SET('archive',e.flags)
          ORDER BY  FIND_IN_SET('valide',e.flags), peremption";
    $page->assign('evs', $globals->xdb->iterator($sql));
}

$page->run();
?>

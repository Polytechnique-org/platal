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

class ProfileModule extends PLModule
{
    function handlers()
    {
        return array(
            'photo'  => $this->make_hook('photo',  AUTH_PUBLIC),
            'trombi' => $this->make_hook('trombi', AUTH_COOKIE),
        );
    }

    function _trombi_getlist($offset, $limit)
    {
        global $globals;

        $where  = ( $this->promo > 0 ? "WHERE promo='{$this->promo}'" : "" );

        $res = $globals->xdb->query(
                "SELECT  COUNT(*)
                   FROM  auth_user_md5 AS u
             RIGHT JOIN  photo         AS p ON u.user_id=p.uid
             $where");
        $pnb = $res->fetchOneCell();

        $res = $globals->xdb->query(
                "SELECT  promo,user_id,a.alias AS forlife,IF(nom_usage='', nom, nom_usage) AS nom,prenom
                   FROM  photo         AS p
             INNER JOIN  auth_user_md5 AS u ON u.user_id=p.uid
             INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND a.type='a_vie' )
                  $where
               ORDER BY  promo,nom,prenom LIMIT {?}, {?}", $offset*$limit, $limit);

        return array($pnb, $res->fetchAllAssoc());
    }

    function handler_photo(&$page, $x = null, $req = null)
    {
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        global $globals;

        $res = $globals->xdb->query("SELECT id, pub FROM aliases
                                  LEFT JOIN photo ON(id = uid)
                                      WHERE alias = {?}", $x);
        list($uid, $photo_pub) = $res->fetchOneRow();

        if ($req && logged()) {
            include 'validations.inc.php';
            $myphoto = PhotoReq::get_request($uid);
            Header('Content-type: image/'.$myphoto->mimetype);
            echo $myphoto->data;
        } else {
            $res = $globals->xdb->query(
                    "SELECT  attachmime, attach
                       FROM  photo
                      WHERE  uid={?}", $uid);

            if ((list($type,$data) = $res->fetchOneRow()) && ($photo_pub == 'public' || logged())) {
                Header("Content-type: image/$type");
                echo $data;
            } else {
                Header('Content-type: image/png');
                echo file_get_contents(dirname(__FILE__).'../htdocs/images/none.png');
            }
        }
        exit;
    }

    function handler_trombi(&$page, $promo = null)
    {
        require_once 'trombi.inc.php';

        $page->changeTpl('trombipromo.tpl');
        $page->assign('xorg_title','Polytechnique.org - Trombi Promo');

        if (is_null($promo)) {
            return PL_OK;
        }

        $this->promo = $promo = intval($promo);

        if ($promo >= 1900 && $promo < intval(date('Y'))
        || ($promo == -1 && has_perms()))
        {
            $trombi = new Trombi(array($this, '_trombi_getlist'));
            $trombi->hidePromo();
            $trombi->setAdmin();
            $page->assign_by_ref('trombi', $trombi);
        } else {
            $page->trig('Promotion incorrecte (saisir au format YYYY). Recommence.');
        }

        return PL_OK;
    }
}

?>

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
            'photo'           => $this->make_hook('photo',        AUTH_PUBLIC),
            'photo/change'    => $this->make_hook('photo_change', AUTH_MDP),
            'trombi'          => $this->make_hook('trombi',       AUTH_COOKIE),
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
                echo file_get_contents(dirname(__FILE__).'/../htdocs/images/none.png');
            }
        }
        exit;
    }

    function handler_photo_change(&$page)
    {
        global $globals;

        $page->changeTpl('trombino.tpl');

        require_once('validations.inc.php');

        $trombi_x = '/home/web/trombino/photos'.Session::get('promo')
                    .'/'.Session::get('forlife').'.jpg';

        if (Env::has('upload')) {
            $file = isset($_FILES['userfile']['tmp_name'])
                    ? $_FILES['userfile']['tmp_name']
                    : Env::get('photo');
            if ($data = file_get_contents($file)) {
                if ($myphoto = new PhotoReq(Session::getInt('uid'), $data)) {
                    $myphoto->submit();
                }
            } else {
                $page->trig('Fichier inexistant ou vide');
            }
        } elseif (Env::has('trombi')) {
            $myphoto = new PhotoReq(Session::getInt('uid'),
                                    file_get_contents($trombi_x));
            if ($myphoto) {
                $myphoto->commit();
                $myphoto->clean();
            }
        } elseif (Env::get('suppr')) {
            $globals->xdb->execute('DELETE FROM photo WHERE uid = {?}',
                                   Session::getInt('uid'));
            $globals->xdb->execute('DELETE FROM requests
                                     WHERE user_id = {?} AND type="photo"',
                                   Session::getInt('uid'));
        } elseif (Env::get('cancel')) {
            $sql = $globals->xdb->query('DELETE FROM requests 
                                        WHERE user_id={?} AND type="photo"',
                                        Session::getInt('uid'));
        }

        $sql = $globals->xdb->query('SELECT COUNT(*) FROM requests
                                      WHERE user_id={?} AND type="photo"',
                                    Session::getInt('uid'));
        $page->assign('submited', $sql->fetchOneCell());
        $page->assign('has_trombi_x', file_exists($trombi_x));

        return PL_OK;
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

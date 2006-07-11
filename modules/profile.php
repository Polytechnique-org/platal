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
            'photo'        => $this->make_hook('photo',        AUTH_PUBLIC),
            'photo/change' => $this->make_hook('photo_change', AUTH_MDP),

            'profile/orange'   => $this->make_hook('p_orange',   AUTH_MDP),
            'profile/referent' => $this->make_hook('p_referent', AUTH_MDP),
            'profile/usage'    => $this->make_hook('p_usage',    AUTH_MDP),

            'trombi'  => $this->make_hook('trombi', AUTH_COOKIE),

            'vcard'   => $this->make_hook('vcard',  AUTH_COOKIE),
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

    function handler_p_orange(&$page)
    {
        global $globals;

        $page->changeTpl('orange.tpl');

        require_once 'validations.inc.php';
        require_once 'xorg.misc.inc.php';

        $res = $globals->xdb->query(
                "SELECT  u.promo,u.promo_sortie
                   FROM  auth_user_md5  AS u
                  WHERE  user_id={?}", Session::getInt('uid'));

        list($promo,$promo_sortie_old) = $res->fetchOneRow();
        $page->assign('promo_sortie_old', $promo_sortie_old);
        $page->assign('promo',  $promo);

        if (!Env::has('promo_sortie')) {
            return PL_OK;
        }

        $promo_sortie = Env::getInt('promo_sortie');

        if ($promo_sortie < 1000 || $promo_sortie > 9999) {
            $page->trig('L\'année de sortie doit être un nombre de quatre chiffres');
        }
        elseif ($promo_sortie < $promo + 3) {
            $page->trig('Trop tôt');
        }
        elseif ($promo_sortie == $promo_sortie_old) {
            $page->trig('Tu appartiens déjà à la promotion correspondante à cette année de sortie.');
        }
        elseif ($promo_sortie == $promo + 3) {
            $globals->xdb->execute(
                "UPDATE  auth_user_md5 set promo_sortie={?} 
                  WHERE  user_id={?}",$promo_sortie,Session::getInt('uid'));
                $page->trig('Ton statut "orange" a été supprimé.');
                $page->assign('promo_sortie_old', $promo_sortie);
        }
        else {
            $page->assign('promo_sortie', $promo_sortie);

            if (Env::has('submit')) {
                $myorange = new OrangeReq(Session::getInt('uid'),
                                          $promo_sortie);
                $myorange->submit();
                $page->assign('myorange', $myorange);
            }
        }

        return PL_OK;
    }

    function handler_p_referent(&$page, $x = null)
    {
        global $globals;

        require_once 'user.func.inc.php';

        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('fiche_referent.tpl');
        $page->assign('simple', true);

        $res = $globals->xdb->query(
                "SELECT  prenom, nom, user_id, promo, cv, a.alias AS bestalias
                  FROM  auth_user_md5 AS u
            INNER JOIN  aliases       AS a ON (u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
            INNER JOIN  aliases       AS a1 ON (u.user_id=a1.id
                                                AND a1.alias = {?}
                                                AND a1.type!='homonyme')", $x);

        if ($res->numRows() != 1) {
            return PL_NOT_FOUND;
        }

        list($prenom, $nom, $user_id, $promo, $cv, $bestalias) = $res->fetchOneRow();

        $page->assign('prenom', $prenom);
        $page->assign('nom',    $nom);
        $page->assign('promo',  $promo);
        $page->assign('cv',     $cv);
        $page->assign('bestalias', $bestalias);
        $page->assign('adr_pro', get_user_details_pro($user_id));

        /////  recuperations infos referent

        //expertise
        $res = $globals->xdb->query("SELECT expertise FROM mentor WHERE uid = {?}", $user_id);
        $page->assign('expertise', $res->fetchOneCell());

        //secteurs
        $secteurs = $ss_secteurs = Array();
        $res = $globals->xdb->iterRow(
                "SELECT  s.label, ss.label
                   FROM  mentor_secteurs AS m
              LEFT JOIN  emploi_secteur AS s ON(m.secteur = s.id)
              LEFT JOIN  emploi_ss_secteur AS ss ON(m.secteur = ss.secteur AND m.ss_secteur = ss.id)
                  WHERE  uid = {?}", $user_id);
        while (list($sec, $ssec) = $res->next()) {
            $secteurs[]    = $sec;
            $ss_secteurs[] = $ssec;
        }
        $page->assign_by_ref('secteurs', $secteurs);
        $page->assign_by_ref('ss_secteurs', $ss_secteurs);

        //pays
        $res = $globals->xdb->query(
                "SELECT  gp.pays
                   FROM  mentor_pays AS m
              LEFT JOIN  geoloc_pays AS gp ON(m.pid = gp.a2)
                  WHERE  uid = {?}", $user_id);
        $page->assign('pays', $res->fetchColumn());

        $page->addJsLink('javascript/close_on_esc.js');
        return PL_OK;
    }

    function handler_p_usage(&$page)
    {
        global $globals;

        $page->changeTpl('nomusage.tpl');

        require_once 'validations.inc.php';
        require_once 'xorg.misc.inc.php';

        $res = $globals->xdb->query(
                "SELECT  u.nom,u.nom_usage,u.flags,e.alias
                   FROM  auth_user_md5  AS u
              LEFT JOIN  aliases        AS e ON(u.user_id = e.id AND FIND_IN_SET('usage',e.flags))
                  WHERE  user_id={?}", Session::getInt('uid'));

        list($nom,$usage_old,$flags,$alias_old) = $res->fetchOneRow();
        $flags = new flagset($flags);
        $page->assign('usage_old', $usage_old);
        $page->assign('alias_old',  $alias_old);

        $nom_usage = replace_accent(trim(Env::get('nom_usage'))); 
        $nom_usage = strtoupper($nom_usage);
        $page->assign('usage_req', $nom_usage);

        if (Env::has('submit') && ($nom_usage != $usage_old)) {
            // on vient de recevoir une requete, differente de l'ancien nom d'usage
            if ($nom_usage == $nom) {
                $page->assign('same', true);
            } else { // le nom de mariage est distinct du nom à l'X
                // on calcule l'alias pour l'afficher
                $reason = Env::get('reason');
                if ($reason == 'other') {
                    $reason = Env::get('other_reason');
                }
                $myusage = new UsageReq(Session::getInt('uid'), $nom_usage, $reason);
                $myusage->submit();
                $page->assign('myusage', $myusage);
            }
        }

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

    function format_adr($params, &$smarty)
    {
        // $adr1, $adr2, $adr3, $postcode, $city, $region, $country
        extract($params['adr']);
        $adr = $adr1;
        $adr = trim("$adr\n$adr2");
        $adr = trim("$adr\n$adr3");
        return quoted_printable_encode(";;$adr;$city;$region;$postcode;$country");
    }

    function handler_vcard(&$page, $x = null)
    {
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        global $globals;

        if (substr($x, -4) == '.vcf') {
            $x = substr($x, 0, strlen($x) - 4);
        }

        new_nonhtml_page('vcard.tpl', AUTH_COOKIE);
        require_once 'xorg.misc.inc.php';
        require_once 'user.func.inc.php';

        $page->register_modifier('qp_enc', 'quoted_printable_encode');
        $page->register_function('format_adr', array($this, 'format_adr'));

        $login = get_user_forlife($x);
        $user  = get_user_details($login);

        // alias virtual
        $res = $globals->xdb->query(
                "SELECT alias
                   FROM virtual
             INNER JOIN virtual_redirect USING(vid)
             INNER JOIN auth_user_quick  ON ( user_id = {?} AND emails_alias_pub = 'public' )
                  WHERE ( redirect={?} OR redirect={?} )
                        AND alias LIKE '%@{$globals->mail->alias_dom}'",
                Session::getInt('uid'),
                $user['forlife'].'@'.$globals->mail->domain,
                $user['forlife'].'@'.$globals->mail->domain2);

        $user['virtualalias'] = $res->fetchOneCell();

        $page->assign_by_ref('vcard', $user);

        header("Pragma: ");
        header("Cache-Control: ");
        header("Content-type: text/x-vcard\n");
        header("Content-Transfer-Encoding: Quoted-Printable\n");

        return PL_OK;
    }
}

?>

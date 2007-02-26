<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class EventsModule extends PLModule
{
    function handlers()
    {
        return array(
            'events'         => $this->make_hook('ev',        AUTH_COOKIE),
            'rss'            => $this->make_hook('rss', AUTH_PUBLIC),
            'events/preview' => $this->make_hook('preview', AUTH_PUBLIC, '', NO_AUTH),
            'events/submit'  => $this->make_hook('ev_submit', AUTH_MDP),
            'admin/events'   => $this->make_hook('admin_events',     AUTH_MDP, 'admin'),

            'ajax/tips'      => $this->make_hook('tips',      AUTH_COOKIE, '', NO_AUTH),
            'admin/tips'     => $this->make_hook('admin_tips', AUTH_MDP, 'admin'),
        );
    }

    function get_tips($exclude = null)
    {
        $exclude  = is_null($exclude) ? '' : ' AND id != ' . $exclude . ' ';
        $priority = rand(0, 510);
        do {
            $priority = (int)($priority/2);
            $res = XDB::query("SELECT  *
                                 FROM  tips
                                WHERE  (peremption = '0000-00-00' OR peremption > CURDATE())
                                       AND (promo_min = 0 OR promo_min <= {?})
                                       AND (promo_max = 0 OR promo_max >= {?})
                                       AND (priorite >= {?})
                                       AND (state = 'active')
                                       $exclude
                             ORDER BY  RAND()
                                LIMIT  1",
                              S::i('promo'), S::i('promo'), $priority);
        } while ($priority && !$res->numRows());
        if (!$res->numRows()) {
            return null;
        } 
        return $res->fetchOneAssoc();
    }

    function get_events($where, $order, array &$array, $name)
    {
        // affichage des evenements
        // annonces promos triées par présence d'une limite sur les promos
        // puis par dates croissantes d'expiration
        $promo = S::v('promo');
        $uid   = S::i('uid'); 
        $sql = "SELECT  e.id,e.titre, ev.user_id IS NULL AS nonlu
                  FROM  evenements    AS e
            LEFT JOIN   evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
                 WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
                        AND (e.promo_min = 0 || e.promo_min <= {?})
                        AND (e.promo_max = 0 || e.promo_max >= {?})
                        AND $where
              ORDER BY  $order";
        $sum = XDB::iterator($sql, $uid, $promo, $promo);
        if (!$sum->total()) {
            return false;
        }
        $sql = "SELECT  e.id,e.titre,e.texte,a.user_id,a.nom,a.prenom,a.promo,l.alias AS forlife
                  FROM  evenements     AS e
            INNER JOIN  auth_user_md5  AS a ON e.user_id=a.user_id
            INNER JOIN  aliases        AS l ON ( a.user_id=l.id AND l.type='a_vie' )
             LEFT JOIN  evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
                 WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
                        AND (e.promo_min = 0 || e.promo_min <= {?})
                        AND (e.promo_max = 0 || e.promo_max >= {?})
                        AND ev.user_id IS NULL
                        AND $where
              ORDER BY  $order";
        $evt = XDB::iterator($sql, $uid, $promo, $promo);
        $array[$name] = array('events' => $evt, 'summary' => $sum);
        return true;
    }

    function handler_ev(&$page, $action = 'list', $eid = null, $pound = null)
    {
        $page->changeTpl('events/index.tpl');
        $page->addJsLink('ajax.js');
        $page->assign('tips', $this->get_tips());

        $res = XDB::query('SELECT date, naissance FROM auth_user_md5
                            WHERE user_id={?}', S::v('uid'));
        list($date, $naissance) = $res->fetchOneRow();

        // incitation à mettre à jour la fiche

        $d2  = mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2),
                      substr($date, 0, 4));
        if( (time() - $d2) > 60 * 60 * 24 * 400 ) {
            // si fiche date de + de 400j;
            $page->assign('fiche_incitation', $date);
        }

        // Souhaite bon anniversaire

        if (substr($naissance, 5) == date('m-d')) {
            $page->assign('birthday', date('Y') - substr($naissance, 0, 4));
        }

        // incitation à mettre une photo

        $res = XDB::query('SELECT COUNT(*) FROM photo
                            WHERE uid={?}', S::v('uid'));
        $page->assign('photo_incitation', $res->fetchOneCell() == 0);

        // Incitation à se géolocaliser
        require_once 'geoloc.inc.php';
        $res = localize_addresses(S::v('uid', -1));
        $page->assign('geoloc_incitation', count($res));

        // ajout du lien RSS
        if (S::has('core_rss_hash')) {
            $page->setRssLink('Polytechnique.org :: News',
                              '/rss/'.S::v('forlife') .'/'.S::v('core_rss_hash').'/rss.xml');
        }

        // cache les evenements lus et raffiche les evenements a relire
        if ($action == 'read' && $eid) {
            XDB::execute('DELETE evenements_vus.*
                            FROM evenements_vus AS ev 
                      INNER JOIN evenements AS e ON e.id = ev.evt_id
                           WHERE peremption < NOW()');
            XDB::execute('REPLACE INTO evenements_vus VALUES({?},{?})',
                $eid, S::v('uid'));
            pl_redirect('events#'.$pound);
        }

        if ($action == 'unread' && $eid) {
            XDB::execute('DELETE FROM evenements_vus
                           WHERE evt_id = {?} AND user_id = {?}',
                                   $eid, S::v('uid'));
            pl_redirect('events#newsid'.$eid);
        }

        $array = array();
        $this->get_events('e.creation_date > DATE_SUB(CURDATE(), INTERVAL 2 DAY)',
                          'e.creation_date DESC', $array, 'news');
        $this->get_events('e.peremption < DATE_ADD(CURDATE(), INTERVAL 2 DAY)
                          AND e.creation_date <= DATE_SUB(CURDATE(), INTERVAL 2 DAY)',
                          'e.peremption, e.creation_date DESC', $array, 'end');
        $this->get_events('e.peremption >= DATE_ADD(CURDATE(), INTERVAL 2 DAY)
                          AND e.creation_date <= DATE_SUB(CURDATE(), INTERVAL 2 DAY)',
                          'e.peremption, e.creation_date DESC', $array, 'body');
        $page->assign_by_ref('events', $array);
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {       
        require_once 'rss.inc.php';
            
        $uid = init_rss('events/rss.tpl', $user, $hash);
            
        $rss = XDB::iterator(
                'SELECT  e.id, e.titre, e.texte, e.creation_date,
                         IF(u2.nom_usage = "", u2.nom, u2.nom_usage) AS nom, u2.prenom, u2.promo
                   FROM  auth_user_md5   AS u
             INNER JOIN  evenements      AS e ON ( (e.promo_min = 0 || e.promo_min <= u.promo)
                                                 AND (e.promo_max = 0 || e.promo_max >= u.promo) )
             INNER JOIN  auth_user_md5   AS u2 ON (u2.user_id = e.user_id)
                  WHERE  u.user_id = {?} AND FIND_IN_SET(e.flags, "valide")
                                         AND peremption >= NOW()', $uid);
        $page->assign('rss', $rss);
    }

    function handler_preview(&$page)
    {
        require_once('url_catcher.inc.php');
        $page->changeTpl('events/preview.tpl', NO_SKIN);
        $texte = Get::v('texte');
        if (!is_utf8($texte)) {
            $texte = utf8_encode($texte);
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'admin') === false) {
            $texte = url_catcher(pl_entities($texte));
        }
        $titre = Get::v('titre');
        if (!is_utf8($titre)) {
            $titre = utf8_encode($titre);
        }
        $page->assign('texte_html', $texte);
        $page->assign('titre', $titre);
        header('Content-Type: text/html; charset=utf-8');
    }

    function handler_ev_submit(&$page)
    {
        $page->changeTpl('events/submit.tpl');
        $page->addJsLink('ajax.js');
        
        require_once('wiki.inc.php');
        wiki_require_page('Xorg.Annonce');

        $titre      = Post::v('titre');
        $texte      = Post::v('texte');
        $promo_min  = Post::i('promo_min');
        $promo_max  = Post::i('promo_max');
        $peremption = Post::i('peremption');
        $valid_mesg = Post::v('valid_mesg');
        $action     = Post::v('action');

        if (($promo_min > $promo_max && $promo_max != 0)||
            ($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
            ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020)))
        {
            $page->trig("L'intervalle de promotions n'est pas valide");
            $action = null;
        }

    	require_once('url_catcher.inc.php');
		$texte_catch_url = url_catcher($texte);
		
        $page->assign('titre', $titre);
        $page->assign('texte', $texte);
        $page->assign('texte_html', $texte_catch_url);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('peremption', $peremption);
        $page->assign('valid_mesg', $valid_mesg);
        $page->assign('action', strtolower($action));

        if ($action && (!trim($texte) || !trim($titre))) {
            $page->trig("L'article doit avoir un titre et un contenu");
        } elseif ($action) {
        	$texte = $texte_catch_url;
            require_once 'validations.inc.php';
            $evtreq = new EvtReq($titre, $texte, $promo_min, $promo_max,
                                 $peremption, $valid_mesg, S::v('uid'));
            $evtreq->submit();
            $page->assign('ok', true);
        }

        $select = '';
        for ($i = 1 ; $i < 30 ; $i++) {
            $time    = time() + 3600 * 24 * $i;
            $p_stamp = date('Ymd', $time);
            $year    = date('Y',   $time);
            $month   = date('m',   $time);
            $day     = date('d',   $time);

            $select .= "<option value=\"$p_stamp\"";
            if ($p_stamp == strtr($peremption, array("-" => ""))) {
                $select .= " selected='selected'";
            }
            $select .= "> $day / $month / $year</option>\n";
        }
        $page->assign('select',$select);
    }

    function handler_tips(&$page, $tips = null)
    {
        header('Content-Type: text/html; charset="UTF-8"');
        $page->changeTpl('include/tips.tpl', NO_SKIN);
        $page->assign('tips', $this->get_tips($tips));
    }

    function handler_admin_tips(&$page, $action = 'list', $id = null)
    {
        $page->assign('xorg_title', 'Polytechnique.org - Administration - Astuces');
        $page->assign('title', 'Gestion des Astuces');
        $table_editor = new PLTableEditor('admin/tips', 'tips', 'id');
        $table_editor->describe('peremption', 'date de péremption', true);
        $table_editor->describe('promo_min', 'promo. min (0 aucune)', false);
        $table_editor->describe('promo_max', 'promo. max (0 aucune)', false);
        $table_editor->describe('titre', 'titre', true);
        $table_editor->describe('state', 'actif', true);
        $table_editor->describe('text', 'texte (html) de l\'astuce', false);
        $table_editor->describe('priorite', '0<=priorité<=255', true);
        $table_editor->list_on_edit(false);
        $table_editor->apply($page, $action, $id);
        if (($action == 'edit' && !is_null($id)) || $action == 'update') {
            $page->changeTpl('events/admin_tips.tpl');
        }
    }

    function handler_admin_events(&$page, $action = 'list', $eid = null) 
    {
        $page->changeTpl('events/admin.tpl');
        $page->addJsLink('ajax.js');
        $page->assign('xorg_title','Polytechnique.org - Administration - Evenements');
        $page->register_modifier('hde', 'html_entity_decode');

        $arch = $action == 'archives';
        $page->assign('action', $action);
 
        if (Post::v('action') == "Proposer" && $eid) {
            $promo_min = Post::i('promo_min');
            $promo_max = Post::i('promo_max');
            if ($promo_min > $promo_max ||
                ($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
                ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020)))
            {
                $page->trig("L'intervalle de promotions $promo_min -> $promo_max n'est pas valide");
                $action = 'edit';
            } else {
                XDB::execute('UPDATE evenements
                                 SET creation_date = creation_date, 
                                     titre={?}, texte={?}, peremption={?}, promo_min={?}, promo_max={?}
                               WHERE id = {?}', 
                              Post::v('titre'), Post::v('texte'), Post::v('peremption'),
                              Post::v('promo_min'), Post::v('promo_max'), $eid);
            }    
        }

        if ($action == 'edit') {
            $res = XDB::query('SELECT titre, texte, peremption, promo_min, promo_max
                                 FROM evenements
                                WHERE id={?}', $eid);
            list($titre, $texte, $peremption, $promo_min, $promo_max) = $res->fetchOneRow();
            $page->assign('titre',$titre);
            $page->assign('texte',$texte);
            $page->assign('texte_html', pl_entity_decode($texte));
            $page->assign('promo_min',$promo_min);
            $page->assign('promo_max',$promo_max);
            $page->assign('peremption',$peremption);

            $select = "";
            for ($i = 1 ; $i < 30 ; $i++) {
                $p_stamp=date("Ymd",time()+3600*24*$i);
                $year=substr($p_stamp,0,4);
                $month=substr($p_stamp,4,2);
                $day=substr($p_stamp,6,2);

                $select .= "<option value=\"$p_stamp\"" 
                        . (($p_stamp == strtr($peremption, array("-" => ""))) ? " selected" : "")
                        . "> $day / $month / $year</option>\n";
            }
            $page->assign('select',$select);
        } else {
            switch ($action) {
                case 'delete':
                    XDB::execute('DELETE from evenements
                                   WHERE id = {?}', $eid);
                    break;

                case "archive":
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = CONCAT(flags,",archive")
                                   WHERE id = {?}', $eid);
                    break;

                case "unarchive":
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = REPLACE(flags,"archive","")
                                   WHERE id = {?}', $eid);
                    $action = 'archives';
                    $arch   = true;
                    break;

                case "valid":
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = CONCAT(flags,",valide")
                                   WHERE id = {?}', $eid);
                    break;

                case "unvalid":
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = REPLACE(flags,"valide", "")
                                   WHERE id = {?}', $eid);
                    break;
            }

            $pid = ($eid && $action == 'preview') ? $eid : -1;
            $sql = "SELECT  e.id, e.titre, e.texte,e.id = $pid AS preview,
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
                  ORDER BY  FIND_IN_SET('valide',e.flags), e.peremption DESC";
            $page->assign('evs', XDB::iterator($sql));
        }
        $page->assign('arch', $arch);
    }   
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

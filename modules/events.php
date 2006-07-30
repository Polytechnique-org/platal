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

class EventsModule extends PLModule
{
    function handlers()
    {
        return array(
            'events'         => $this->make_hook('ev',        AUTH_COOKIE),
            'events/submit'  => $this->make_hook('ev_submit', AUTH_MDP),
            'admin/events'   => $this->make_hook('admin_events',     AUTH_MDP, 'admin'),

            'nl'             => $this->make_hook('nl',        AUTH_COOKIE),
            'nl/show'        => $this->make_hook('nl_show',   AUTH_COOKIE),
            'nl/submit'      => $this->make_hook('nl_submit', AUTH_COOKIE),
            'admin/newsletter'             => $this->make_hook('admin_nl', AUTH_MDP, 'admin'),
            'admin/newsletter/categories'  => $this->make_hook('admin_nl_cat', AUTH_MDP, 'admin'),
            'admin/newsletter/edit'        => $this->make_hook('admin_nl_edit', AUTH_MDP, 'admin'),
        );
    }

    function on_subscribe($forlife, $uid, $promo, $password)
    {
        require_once 'newsletter.inc.php';
        subscribe_nl($uid);
    }

    function handler_ev(&$page, $action = 'list', $eid = null)
    {
        $page->changeTpl('login.tpl');

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

        // affichage de la boîte avec quelques liens
        require_once 'login.conf.php';
        $pub_nbElem = $pub_nbLig * $pub_nbCol ;
        if (count($pub_tjs) <= $pub_nbElem) {
            $publicite = array_slice($pub_tjs, 0, $pub_nbElem);
        } else {
            $publicite = $pub_tjs ;
        }

        $nbAlea = $pub_nbElem - count($publicite) ;
        if ($nbAlea > 0) {
            $choix = array_rand($pub_rnd,$nbAlea) ;
            foreach ($choix as $url) {
                $publicite[$url] = $pub_rnd[$url] ;
            }
        }
        $publicite = array_chunk( $publicite , $pub_nbLig , true ) ;
        $page->assign_by_ref('publicite', $publicite);

        // ajout du lien RSS

        if (S::has('core_rss_hash')) {
            $page->assign('xorg_rss',
                          array('title' => 'Polytechnique.org :: News',
                                'href' => '/rss/'.S::v('forlife')
                                         .'/'.S::v('core_rss_hash').'/rss.xml')
            );
        }

        // cache les evenements lus et raffiche les evenements a relire
        if ($action == 'read' && $eid) {
            XDB::execute('DELETE FROM evenements_vus AS ev 
                                     INNER JOIN evenements AS e ON e.id = ev.evt_id
                                          WHERE peremption < NOW)');
            XDB::execute('REPLACE INTO evenements_vus VALUES({?},{?})',
                                   $eid, S::v('uid'));
        }

        if ($action == 'unread' && $eid) {
            XDB::execute('DELETE FROM evenements_vus
                                          WHERE evt_id = {?} AND user_id = {?}',
                                   $eid, S::v('uid'));
        }

        // affichage des evenements
        // annonces promos triées par présence d'une limite sur les promos
        // puis par dates croissantes d'expiration
        $promo = S::v('promo');
        $sql = "SELECT  e.id,e.titre,e.texte,a.user_id,a.nom,a.prenom,a.promo,l.alias AS forlife
                  FROM  evenements     AS e
            INNER JOIN  auth_user_md5  AS a ON e.user_id=a.user_id
            INNER JOIN  aliases        AS l ON ( a.user_id=l.id AND l.type='a_vie' )
             LEFT JOIN  evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
                 WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
                        AND (e.promo_min = 0 || e.promo_min <= {?})
                        AND (e.promo_max = 0 || e.promo_max >= {?})
                        AND ev.user_id IS NULL
              ORDER BY  (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
        $page->assign('evenement',
                      XDB::iterator($sql, S::v('uid'),
                                              $promo, $promo)
                      );

        $sql = "SELECT  e.id,e.titre, ev.user_id IS NULL AS nonlu
                  FROM  evenements    AS e
            LEFT JOIN   evenements_vus AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
                 WHERE  FIND_IN_SET(e.flags, 'valide') AND peremption >= NOW()
                        AND (e.promo_min = 0 || e.promo_min <= {?})
                        AND (e.promo_max = 0 || e.promo_max >= {?})
              ORDER BY  (e.promo_min != 0 AND  e.promo_max != 0) DESC,  e.peremption";
        $page->assign('evenement_summary',
                      XDB::iterator($sql, S::v('uid'),
                                              $promo, $promo)
                     );
    }

    function handler_ev_submit(&$page)
    {
        $page->changeTpl('evenements.tpl');

        $titre      = Post::v('titre');
        $texte      = Post::v('texte');
        $promo_min  = Post::i('promo_min');
        $promo_max  = Post::i('promo_max');
        $peremption = Post::i('peremption');
        $valid_mesg = Post::v('valid_mesg');
        $action     = Post::v('action');

        $page->assign('titre', $titre);
        $page->assign('texte', $texte);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('peremption', $peremption);
        $page->assign('valid_mesg', $valid_mesg);
        $page->assign('action', strtolower($action));

        if ($action == 'Confirmer') {
            $texte = preg_replace('/((http|ftp)+(s)?:\/\/[^<>\s]+)/i',
                                  '<a href=\"\\0\">\\0</a>', $texte);
            $texte = preg_replace('/([^,\s]+@[^,\s]+)/i',
                                  '<a href=\"mailto:\\0\">\\0</a>', $texte);
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

    function handler_nl(&$page, $action = null)
    {
        require_once 'newsletter.inc.php';

        $page->changeTpl('newsletter/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Lettres mensuelles');

        switch ($action) {
          case 'out': unsubscribe_nl(); break;
          case 'in':  subscribe_nl(); break;
          default: ;
        }

        $page->assign('nls', get_nl_state());
        $page->assign_by_ref('nl_list', get_nl_list());
    }

    function handler_nl_show(&$page, $nid = 'last')
    {
        $page->changeTpl('newsletter/show.tpl');

        require_once 'newsletter.inc.php';

        $nl  = new NewsLetter($nid);
        $page->assign_by_ref('nl', $nl);

        if (Post::has('send')) {
            $nl->sendTo(S::v('prenom'), S::v('nom'),
                        S::v('bestalias'), S::v('femme'),
                        S::v('mail_fmt') != 'text');
        }
    }

    function handler_nl_submit(&$page)
    {
        $page->changeTpl('newsletter/submit.tpl');

        require_once 'newsletter.inc.php';

        if (Post::has('see')) {
            $art = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'));
            $page->assign('art', $art);
        } elseif (Post::has('valid')) {
            require_once('validations.inc.php');
            $art = new NLReq(S::v('uid'), Post::v('title'),
                             Post::v('body'), Post::v('append'));
            $art->submit();
            $page->assign('submited', true);
        }
    }

    function handler_admin_events(&$page, $action = 'list', $eid = null) 
    {
        $page->changeTpl('admin/evenements.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Evenements');
        $page->register_modifier('hde', 'html_entity_decode');

        $arch = $action == 'archives';
        $page->assign('action', $action);
 
        if (Post::v('action') == "Proposer") {
            XDB::execute('UPDATE evenements
                             SET titre={?}, texte={?}, peremption={?}, promo_min={?}, promo_max={?}
                           WHERE id = {?}', 
                          Post::v('titre'), Post::v('texte'), Post::v('peremption'),
                          Post::v('promo_min'), Post::v('promo_max'), Post::i('evt_id'));
        }

        if ($action == 'edit') {
            $res = XDB::query('SELECT titre, texte, peremption, promo_min, promo_max
                                 FROM evenements
                                WHERE id={?}', $eid);
            list($titre, $texte, $peremption, $promo_min, $promo_max) = $res->fetchOneRow();
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

    function handler_admin_nl(&$page, $new = false) {
        $page->changeTpl('newsletter/admin.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : liste');
        require_once("newsletter.inc.php");
        
        if($new) {
           insert_new_nl();
           pl_redirect("admin/newsletter");
        }
        
        $page->assign_by_ref('nl_list', get_nl_slist());
    }
    
    function handler_admin_nl_edit(&$page, $nid = 'last', $aid = null, $action = 'edit') {
        $page->changeTpl('newsletter/edit.tpl');
        $page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : Edition'); 
        require_once("newsletter.inc.php");
        
        $nl  = new NewsLetter($nid);
        
        if($action == 'delete') {
            $nl->delArticle($aid);
            pl_redirect("admin/newsletter/edit/$nid");
        }
        
        if($aid == 'update') {
            $nl->_title = Post::v('title');
            $nl->_date  = Post::v('date');
            $nl->_head  = Post::v('head');
            $nl->save();
        }
        
        if(Post::v('save')) {
            $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                    $aid, Post::v('cid'), Post::v('pos'));
            $nl->saveArticle($art);
            pl_redirect("admin/newsletter/edit/$nid");
        }
        
        if($action == 'edit') {
            $eaid = $aid;
            if(Post::has('title')) {
                $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                        $eaid, Post::v('cid'), Post::v('pos'));
            } else {
        	   $art = ($eaid == 'new') ? new NLArticle() : $nl->getArt($eaid);
            }
            $page->assign('art', $art);
        }
        
        $page->assign_by_ref('nl',$nl);
    }
    function handler_admin_nl_cat(&$page, $action = 'list', $id = null) {
        require_once('../classes/PLTableEditor.php');
        $page->assign('xorg_title','Polytechnique.org - Administration - Newsletter : Catégories');
        $page->assign('title', 'Gestion des catégories de la newsletter');
        $table_editor = new PLTableEditor('admin/newsletter/categories','newsletter_cat','cid');
        $table_editor->describe('titre','intitulé',true);
        $table_editor->describe('pos','position',true);
        $table_editor->apply($page, $action, $id);
    }    
    
}

?>

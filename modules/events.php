<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
            'events'         => $this->make_hook('ev',           AUTH_COOKIE),
            'rss'            => $this->make_hook('rss',          AUTH_PUBLIC, 'user', NO_HTTPS),
            'events/preview' => $this->make_hook('preview',      AUTH_PUBLIC, 'user', NO_AUTH),
            'events/photo'   => $this->make_hook('photo',        AUTH_PUBLIC),
            'events/submit'  => $this->make_hook('ev_submit',    AUTH_MDP),
            'admin/events'   => $this->make_hook('admin_events', AUTH_MDP,    'admin'),

            'ajax/tips'      => $this->make_hook('tips',         AUTH_COOKIE, 'user', NO_AUTH),
            'admin/tips'     => $this->make_hook('admin_tips',   AUTH_MDP,    'admin'),
        );
    }

    private function get_tips($exclude = null)
    {
        global $globals;
        // Add a new special tip when changing plat/al version
        if ($globals->version != S::v('last_version') && is_null($exclude)) {
            XDB::execute('UPDATE accounts
                             SET last_version = {?}
                           WHERE uid = {?}',
                           $globals->version, S::i('uid'));
            return array('id' => 0,
                         'titre' => 'Bienvenue sur la nouvelle version du site !',
                         'text' => 'Le site a été mis à jour depuis ta dernière visite vers la version ' . $globals->version
                                   . '.<br /> Nous t\'invitons à <a href="review">faire un tour d\'horizon des '
                                   . 'nouveautés</a>.<br /><br />'
                                   . 'Tu peux également retrouver ces informations sur <a href="banana/xorg.m4x.innovation">'
                                   . 'les forums</a>, ou sur <a href="changelog">la liste exhaustive des modifications</a>.',
                         'priorite' => 255,
                         'promo_min' => 0,
                         'promo_max' => 0,
                         'state'     => 'active',
                         'special'   => true);
        }

        $exclude  = is_null($exclude) ? '' : ' AND id != ' . intval($exclude) . ' ';
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

    private function upload_image(PlPage &$page, PlUpload &$upload)
    {
        if (@!$_FILES['image']['tmp_name'] && !Env::v('image_url')) {
            return true;
        }
        if (!$upload->upload($_FILES['image'])  && !$upload->download(Env::v('image_url'))) {
            $page->trigError('Impossible de télécharger l\'image');
            return false;
        } elseif (!$upload->isType('image')) {
            $page->trigError('Le fichier n\'est pas une image valide au format JPEG, GIF ou PNG.');
            $upload->rm();
            return false;
        } elseif (!$upload->resizeImage(200, 300, 100, 100, 32284)) {
            $page->trigError('Impossible de retraiter l\'image');
            return false;
        }
        return true;
    }

    function handler_ev(&$page, $action = 'list', $eid = null, $pound = null)
    {
        $page->changeTpl('events/index.tpl');
        $page->addJsLink('ajax.js');
        $page->assign('tips', $this->get_tips());

        // Adds a reminder onebox to the page.
        $user = S::user();
        require_once 'reminder.inc.php';
        if (($reminder = Reminder::GetCandidateReminder($user))) {
            $reminder->Prepare($page);
        }

        // Wishes "Happy birthday" when required
        $profile = $user->profile();
        if (!is_null($profile)) {
            if ($profile->next_birthday == date('Y-m-d')) {
                $birthyear = (int)date('Y', strtotime($profile->birthdate));
                $curyear   = (int)date('Y');
                $page->assign('birthday', $curyear - $birthyear);
            }
        }

        // Direct link to the RSS feed, when available.
        if (S::hasAuthToken()) {
            $page->setRssLink('Polytechnique.org :: News',
                              '/rss/'.S::v('hruid') .'/'.S::v('token').'/rss.xml');
        }

        // Hide the read event, and reload the page to get to the next event.
        if ($action == 'read' && $eid) {
            XDB::execute('DELETE ev.*
                            FROM evenements_vus AS ev
                      INNER JOIN evenements AS e ON e.id = ev.evt_id
                           WHERE peremption < NOW()');
            XDB::execute('REPLACE INTO evenements_vus VALUES({?},{?})',
                $eid, S::v('uid'));
            pl_redirect('events#'.$pound);
        }

        // Unhide the requested event, and reload the page to display it.
        if ($action == 'unread' && $eid) {
            XDB::execute('DELETE FROM evenements_vus
                           WHERE evt_id = {?} AND user_id = {?}',
                                   $eid, S::v('uid'));
            pl_redirect('events#newsid'.$eid);
        }

        // Fetch the events to display, along with their metadata.
        $array = array();
        $it = XDB::iterator("SELECT  e.id, e.titre, e.texte, e.post_id, e.user_id,
                                     p.x, p.y, p.attach IS NOT NULL AS img, FIND_IN_SET('wiki', e.flags) AS wiki,
                                     FIND_IN_SET('important', e.flags) AS important,
                                     e.creation_date > DATE_SUB(CURDATE(), INTERVAL 2 DAY) AS news,
                                     e.peremption < DATE_ADD(CURDATE(), INTERVAL 2 DAY) AS end,
                                     ev.user_id IS NULL AS nonlu, e.promo_min, e.promo_max
                               FROM  evenements       AS e
                          LEFT JOIN  evenements_photo AS p  ON (e.id = p.eid)
                          LEFT JOIN  evenements_vus   AS ev ON (e.id = ev.evt_id AND ev.user_id = {?})
                              WHERE  FIND_IN_SET('valide', e.flags) AND peremption >= NOW()
                           ORDER BY  important DESC, news DESC, end DESC, e.peremption, e.creation_date DESC",
                            S::i('uid'));
        $cats = array('important', 'news', 'end', 'body');

        $this->load('feed.inc.php');
        $user = S::user();
        $body  = EventFeed::nextEvent($it, $user);
        foreach ($cats as $cat) {
            $data = array();
            if (!$body) {
                continue;
            }
            do {
                if ($cat == 'body' || $body[$cat]) {
                    $data[] = $body;
                } else {
                    break;
                }
                $body = EventFeed::nextEvent($it, $user);
            } while ($body);
            if (!empty($data)) {
                $array[$cat] = $data;
            }
        }

        $page->assign_by_ref('events', $array);
    }

    function handler_photo(&$page, $eid = null, $valid = null)
    {
        if ($eid && $eid != 'valid') {
            $res = XDB::query("SELECT * FROM evenements_photo WHERE eid = {?}", $eid);
            if ($res->numRows()) {
                $photo = $res->fetchOneAssoc();
                pl_cached_dynamic_content_headers("image/" . $photo['attachmime']);
                echo $photo['attach'];
                exit;
            }
        } elseif ($eid == 'valid') {
            require_once 'validations.inc.php';
            $valid = Validate::get_request_by_id($valid);
            if ($valid && $valid->img) {
                pl_cached_dynamic_content_headers("image/" . $valid->imgtype);
                echo $valid->img;
                exit;
            }
        } else {
            $upload = new PlUpload(S::user()->login(), 'event');
            if ($upload->exists() && $upload->isType('image')) {
                pl_cached_dynamic_content_headers($upload->contentType());
                echo $upload->getContents();
                exit;
            }
        }
        global $globals;
        pl_cached_dynamic_content_headers("image/png");
        echo file_get_contents($globals->spoolroot . '/htdocs/images/logo.png');
        exit;
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        $this->load('feed.inc.php');
        $feed = new EventFeed();
        return $feed->run($page, $user, $hash);
    }

    function handler_preview(&$page)
    {
        $page->changeTpl('events/preview.tpl', NO_SKIN);
        $texte = Get::v('texte');
        if (!is_utf8($texte)) {
            $texte = utf8_encode($texte);
        }
        $titre = Get::v('titre');
        if (!is_utf8($titre)) {
            $titre = utf8_encode($titre);
        }
        $page->assign('texte', $texte);
        $page->assign('titre', $titre);
        pl_content_headers("text/html");
    }

    function handler_ev_submit(&$page)
    {
        $page->changeTpl('events/submit.tpl');
        $page->addJsLink('ajax.js');

        $wp = new PlWikiPage('Xorg.Annonce');
        $wp->buildCache();

        $titre      = Post::v('titre');
        $texte      = Post::v('texte');
        $promo_min  = Post::i('promo_min');
        $promo_max  = Post::i('promo_max');
        $peremption = Post::i('peremption');
        $valid_mesg = Post::v('valid_mesg');
        $action     = Post::v('action');
        $upload     = new PlUpload(S::user()->login(), 'event');
        $this->upload_image($page, $upload);

        if (($promo_min > $promo_max && $promo_max != 0)||
            ($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
            ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020)))
        {
            $page->trigError("L'intervalle de promotions n'est pas valide");
            $action = null;
        }

        $page->assign('titre', $titre);
        $page->assign('texte', $texte);
        $page->assign('promo_min', $promo_min);
        $page->assign('promo_max', $promo_max);
        $page->assign('peremption', $peremption);
        $page->assign('valid_mesg', $valid_mesg);
        $page->assign('action', strtolower($action));
        $page->assign_by_ref('upload', $upload);

        if ($action == 'Supprimer l\'image') {
            $upload->rm();
            $page->assign('action', false);
        } elseif ($action && (!trim($texte) || !trim($titre))) {
            $page->trigError("L'article doit avoir un titre et un contenu");
        } elseif ($action) {
            S::assert_xsrf_token();

            require_once 'validations.inc.php';
            $evtreq = new EvtReq($titre, $texte, $promo_min, $promo_max,
                                 $peremption, $valid_mesg, S::user(), $upload);
            $evtreq->submit();
            $page->assign('ok', true);
        } elseif (!Env::v('preview')) {
            $upload->rm();
        }
    }

    function handler_tips(&$page, $tips = null)
    {
        pl_content_headers("text/html");
        $page->changeTpl('include/tips.tpl', NO_SKIN);
        $page->assign('tips', $this->get_tips($tips));
    }

    function handler_admin_tips(&$page, $action = 'list', $id = null)
    {
        $page->setTitle('Administration - Astuces');
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
        $page->setTitle('Administration - Evenements');
        $page->register_modifier('hde', 'html_entity_decode');

        $arch = $action == 'archives';
        $page->assign('action', $action);

        $upload = new PlUpload(S::user()->login(), 'event');
        if ((Env::has('preview') || Post::v('action') == "Proposer") && $eid) {
            $action = 'edit';
            $this->upload_image($page, $upload);
        }

        if (Post::v('action') == 'Pas d\'image' && $eid) {
            S::assert_xsrf_token();
            $upload->rm();
            XDB::execute("DELETE FROM evenements_photo WHERE eid = {?}", $eid);
            $action = 'edit';
        } elseif (Post::v('action') == 'Supprimer l\'image' && $eid) {
            S::assert_xsrf_token();
            $upload->rm();
            $action = 'edit';
        } elseif (Post::v('action') == "Proposer" && $eid) {
            S::assert_xsrf_token();
            $promo_min = Post::i('promo_min');
            $promo_max = Post::i('promo_max');
            if (($promo_min != 0 && ($promo_min <= 1900 || $promo_min >= 2020)) ||
                ($promo_max != 0 && ($promo_max <= 1900 || $promo_max >= 2020 || $promo_max < $promo_min)))
            {
                $page->trigError("L'intervalle de promotions $promo_min -> $promo_max n'est pas valide");
                $action = 'edit';
            } else {
                $res = XDB::query('SELECT flags FROM evenements WHERE id = {?}', $eid);
                $flags = new PlFlagSet($res->fetchOneCell());
                $flags->addFlag('wiki');
                if (Post::v('important')) {
                    $flags->addFlag('important');
                } else {
                    $flags->rmFlag('important');
                }

                XDB::execute('UPDATE evenements
                                 SET creation_date = creation_date,
                                     titre={?}, texte={?}, peremption={?}, promo_min={?}, promo_max={?},
                                     flags = {?}
                               WHERE id = {?}',
                              Post::v('titre'), Post::v('texte'), Post::v('peremption'),
                              Post::v('promo_min'), Post::v('promo_max'),
                              $flags, $eid);
                if ($upload->exists() && list($x, $y, $type) = $upload->imageInfo()) {
                    XDB::execute('REPLACE INTO  evenements_photo
                                           SET  eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}',
                                 $eid, $type, $x, $y, $upload->getContents());
                    $upload->rm();
                }
            }
        }

        if ($action == 'edit') {
            $res = XDB::query('SELECT titre, texte, peremption, promo_min, promo_max, FIND_IN_SET(\'important\', flags),
                                      attach IS NOT NULL
                                 FROM evenements       AS e
                            LEFT JOIN evenements_photo AS p ON(e.id = p.eid)
                                WHERE id={?}', $eid);
            list($titre, $texte, $peremption, $promo_min, $promo_max, $important, $img) = $res->fetchOneRow();
            $page->assign('titre',$titre);
            $page->assign('texte',$texte);
            $page->assign('promo_min',$promo_min);
            $page->assign('promo_max',$promo_max);
            $page->assign('peremption',$peremption);
            $page->assign('important', $important);
            $page->assign('eid', $eid);
            $page->assign('img', $img);
            $page->assign_by_ref('upload', $upload);

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
                    S::assert_xsrf_token();
                    XDB::execute('DELETE from evenements
                                   WHERE id = {?}', $eid);
                    break;

                case "archive":
                    S::assert_xsrf_token();
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = CONCAT(flags,",archive")
                                   WHERE id = {?}', $eid);
                    break;

                case "unarchive":
                    S::assert_xsrf_token();
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = REPLACE(flags,"archive","")
                                   WHERE id = {?}', $eid);
                    $action = 'archives';
                    $arch   = true;
                    break;

                case "valid":
                    S::assert_xsrf_token();
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = CONCAT(flags,",valide")
                                   WHERE id = {?}', $eid);
                    break;

                case "unvalid":
                    S::assert_xsrf_token();
                    XDB::execute('UPDATE evenements
                                     SET creation_date = creation_date, flags = REPLACE(flags,"valide", "")
                                   WHERE id = {?}', $eid);
                    break;
            }

            $pid = ($eid && $action == 'preview') ? $eid : -1;
            $sql = "SELECT  e.id, e.titre, e.texte,e.id = $pid AS preview, e.user_id,
                            DATE_FORMAT(e.creation_date,'%d/%m/%Y %T') AS creation_date,
                            DATE_FORMAT(e.peremption,'%d/%m/%Y') AS peremption,
                            e.promo_min, e.promo_max,
                            FIND_IN_SET('valide', e.flags) AS fvalide,
                            FIND_IN_SET('archive', e.flags) AS farch,
                            FIND_IN_SET('wiki', e.flags) AS wiki
                      FROM  evenements    AS e
                     WHERE  ".($arch ? "" : "!")."FIND_IN_SET('archive',e.flags)
                  ORDER BY  FIND_IN_SET('valide',e.flags), e.peremption DESC";
            $page->assign('evs', XDB::iterator($sql));
        }
        $page->assign('arch', $arch);
        $page->assign('admin_evts', true);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

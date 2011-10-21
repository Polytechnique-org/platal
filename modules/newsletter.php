<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class NewsletterModule extends PLModule
{
    function handlers()
    {
        return array(
            'nl'                           => $this->make_hook('nl',              AUTH_COOKIE, 'user'),
            'nl/show'                      => $this->make_hook('nl_show',         AUTH_COOKIE, 'user'),
            'nl/search'                    => $this->make_hook('nl_search',       AUTH_COOKIE, 'user'),
            'nl/submit'                    => $this->make_hook('nl_submit',       AUTH_PASSWD, 'user'),
            'nl/remaining'                 => $this->make_hook('nl_remaining',    AUTH_PASSWD, 'user'),
            'admin/nls'                    => $this->make_hook('admin_nl_groups', AUTH_PASSWD, 'admin'),
            'admin/newsletter'             => $this->make_hook('admin_nl',        AUTH_PASSWD, 'admin'),
            'admin/newsletter/categories'  => $this->make_hook('admin_nl_cat',    AUTH_PASSWD, 'admin'),
            'admin/newsletter/edit'        => $this->make_hook('admin_nl_edit',   AUTH_PASSWD, 'admin'),
            'admin/newsletter/edit/delete' => $this->make_hook('admin_nl_delete', AUTH_PASSWD, 'admin'),
            // Automatic mailing is disabled for X.org NL
//            'admin/newsletter/edit/cancel' => $this->make_hook('cancel', AUTH_PASSWD, 'admin'),
//            'admin/newsletter/edit/valid'  => $this->make_hook('valid',  AUTH_PASSWD, 'admin'),
        );
    }

    /** This function should return the adequate NewsLetter object for the current module.
     */
    protected function getNl()
    {
        require_once 'newsletter.inc.php';
        return NewsLetter::forGroup(NewsLetter::GROUP_XORG);
    }

    function handler_nl($page, $action = null, $hash = null, $issue_id = null)
    {
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        $hash = ($hash == 'nohash') ? null : $hash;
        switch ($action) {
          case 'out':
            $success = $nl->unsubscribe($issue_id, $hash, $hash != null);
            if (!is_null($hash)) {
                if ($success) {
                    $page->trigSuccess('La désinscription a été effectuée avec succès.');
                } else {
                    $page->trigError("La désinscription n'a été pas pu être effectuée.");
                }
                return;
            }
            break;
          case 'in':  $nl->subscribe(); break;
          default: ;
        }

        $page->changeTpl('newsletter/index.tpl');
        $page->setTitle('Lettres mensuelles');

        $page->assign_by_ref('nl', $nl);
        $page->assign('nls', $nl->subscriptionState());
        $page->assign('nl_list', $nl->listSentIssues(true));
    }

    function handler_nl_show($page, $nid = 'last')
    {
        $page->changeTpl('newsletter/show.tpl');
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        try {
            $issue = $nl->getIssue($nid);
            $user =& S::user();
            if (Get::has('text')) {
                $issue->toText($page, $user);
            } else {
                $issue->toHtml($page, $user);
            }
            if (Post::has('send')) {
                $issue->sendTo($user);
            }
        } catch (MailNotFound $e) {
            return PL_NOT_FOUND;
        }
    }

    function handler_nl_search($page)
    {
        S::assert_xsrf_token();
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if (!Post::has('nl_search')) {
            pl_redirect($nl->prefix(true, false));
        }

        $nl_search = Post::t('nl_search');
        $nl_search_type = Post::t('nl_search_type');
        if (!$nl_search || !($nl_search_type > 0 && $nl_search_type < 10)) {
            $page->trigErrorRedirect('La recherche est vide ou erronée.', $nl->prefix());
        }

        $page->changeTpl('newsletter/search.tpl');
        $user = S::user();
        $fields = array(1 => 'all', 2 => 'all', 3 => 'title', 4 => 'body', 5 => 'append', 6 => 'all', 7 => 'title', 8 => 'head', 9 => 'signature');
        $res_articles = $res_issues = array();
        if ($nl_search_type < 6) {
            $res_articles = $nl->articleSearch($nl_search, $fields[$nl_search_type], $user);
        }
        if ($nl_search_type > 5 || $nl_search_type == 1) {
            $res_issues = $nl->issueSearch($nl_search, $fields[$nl_search_type], $user);
        }

        $articles_count = count($res_articles);
        $issues_count = count($res_issues);
        $results_count = $articles_count + $issues_count;
        if ($results_count > 200) {
            $page->trigError('Recherche trop générale.');
        } elseif ($results_count == 0) {
            $page->trigWarning('Aucun résultat pour cette recherche.');
        } else {
            $page->assign('res_articles', $res_articles);
            $page->assign('res_issues', $res_issues);
            $page->assign('articles_count', $articles_count);
            $page->assign('issues_count', $issues_count);
        }

        $page->assign_by_ref('nl', $nl);
        $page->assign('nl_search', $nl_search);
        $page->assign('nl_search_type', $nl_search_type);
        $page->assign('results_count', $results_count);
    }

    function handler_nl_submit($page)
    {
        $page->changeTpl('newsletter/submit.tpl');

        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        $wp = new PlWikiPage('Xorg.LettreMensuelle');
        $wp->buildCache();

        if (Post::has('see') || (Post::has('valid') && (!trim(Post::v('title')) || !trim(Post::v('body'))))) {
            if (!Post::has('see')) {
                $page->trigError("L'article doit avoir un titre et un contenu");
            }
            $art = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'));
            $page->assign('art', $art);
        } elseif (Post::has('valid')) {
            $art = new NLReq(S::user(), Post::v('title'),
                             Post::v('body'), Post::v('append'));
            $art->submit();
            $page->assign('submited', true);
        }
        $page->addCssLink($nl->cssFile());
    }

    function handler_nl_remaining($page)
    {
        require_once 'newsletter.inc.php';

        pl_content_headers('text/html');
        $page->changeTpl('newsletter/remaining.tpl', NO_SKIN);

        $article = new NLArticle('', Post::t('body'), '');
        $rest = $article->remain();

        $page->assign('too_long', $rest['remaining_lines'] < 0);
        $page->assign('last_line', ($rest['remaining_lines'] == 0));
        $page->assign('remaining', ($rest['remaining_lines'] == 0) ? $rest['remaining_characters_for_last_line'] : $rest['remaining_lines']);
    }

    function handler_admin_nl($page, $new = false) {
        $page->changeTpl('newsletter/admin.tpl');
        $page->setTitle('Administration - Newsletter : liste');

        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if ($new == 'new') {
            // Logs NL creation.
            S::logger()->log('nl_issue_create', $nid);

            $id = $nl->createPending();
            pl_redirect($nl->adminPrefix(true, false) . '/edit/' . $id);
        }

        $page->assign_by_ref('nl', $nl);
        $page->assign('nl_list', $nl->listAllIssues());
    }

    function handler_admin_nl_groups($page, $sort = 'id', $order = 'ASC')
    {
        require_once 'newsletter.inc.php';

        static $titles = array(
            'id'         => 'Id',
            'group_name' => 'Groupe',
            'name'       => 'Titre',
            'custom_css' => 'CSS spécifique',
            'criteria'   => 'Critères actifs'
        );
        static $next_orders = array(
            'ASC'  => 'DESC',
            'DESC' => 'ASC'
        );

        if (!array_key_exists($sort, $titles)) {
            $sort = 'id';
        }
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'ASC';
        }

        $page->changeTpl('newsletter/admin_all.tpl');
        $page->setTitle('Administration - Newsletters : Liste des Newsletters');
        $page->assign('nls', Newsletter::getAll($sort, $order));
        $page->assign('sort', $sort);
        $page->assign('order', $order);
        $page->assign('next_order', $next_orders[$order]);
        $page->assign('titles', $titles);
    }

    function handler_admin_nl_edit($page, $nid = 'last', $aid = null, $action = 'edit') {
        $page->changeTpl('newsletter/edit.tpl');
        $page->addCssLink('nl.Polytechnique.org.css');
        $page->setTitle('Administration - Newsletter : Édition');

        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        try {
            $issue = $nl->getIssue($nid, false);
        } catch (MailNotFound $e) {
            return PL_NOT_FOUND;
        }

        $ufb = $nl->getSubscribersUFB();
        $ufb_keepenv = false;  // Will be set to True if there were invalid modification to the UFB.

        // Convert NLIssue error messages to human-readable errors
        $error_msgs = array(
            NLIssue::ERROR_INVALID_REPLY_TO => "L'adresse de réponse est invalide.",
            NLIssue::ERROR_INVALID_SHORTNAME => "Le nom court est invalide ou vide.",
            NLIssue::ERROR_INVALID_UFC => "Le filtre des destinataires est invalide.",
            NLIssue::ERROR_TOO_LONG_UFC => "Le nombre de matricules AX renseigné est trop élevé.",
            NLIssue::ERROR_SQL_SAVE => "Une erreur est survenue en tentant de sauvegarder la lettre, merci de réessayer.",
        );

        // Update the current issue
        if($aid == 'update' && Post::has('submit')) {

            // Save common fields
            $issue->title      = Post::s('title');
            $issue->title_mail = Post::s('title_mail');
            $issue->head       = Post::s('head');
            $issue->signature  = Post::s('signature');
            $issue->reply_to   = Post::s('reply_to');

            if ($issue->isEditable()) {
                // Date and shortname may only be modified for pending NLs, otherwise all links get broken.
                $issue->date = Post::s('date');
                $issue->shortname = strlen(Post::blank('shortname')) ? null : Post::s('shortname');
                $issue->sufb->updateFromEnv($ufb->getEnv());

                if ($nl->automaticMailingEnabled()) {
                    $issue->send_before = preg_replace('/^(\d\d\d\d)(\d\d)(\d\d)$/', '\1-\2-\3', Post::v('send_before_date')) . ' ' . Post::i('send_before_time_Hour') . ':00:00';
                }
            }
            $errors = $issue->save();
            if (count($errors)) {
                foreach ($errors as $error_code) {
                    $page->trigError($error_msgs[$error_code]);
                }
            }
        }

        // Delete an article
        if($action == 'delete') {
            $issue->delArticle($aid);
            pl_redirect($nl->adminPrefix(true, false) . "/edit/$nid");
        }

        // Save an article
        if(Post::v('save')) {
            $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                                  $aid, Post::v('cid'), Post::v('pos'));
            $issue->saveArticle($art);
            pl_redirect($nl->adminPrefix(true, false) . "/edit/$nid");
        }

        // Edit an article
        if ($action == 'edit' && $aid != 'update') {
            $eaid = $aid;
            if (Post::has('title')) {
                $art  = new NLArticle(Post::v('title'), Post::v('body'), Post::v('append'),
                                      $eaid, Post::v('cid'), Post::v('pos'));
            } else {
                $art = ($eaid == 'new') ? new NLArticle() : $issue->getArt($eaid);
            }
            if ($art && !$art->check()) {
                $page->trigError("Cet article est trop long.");
            }
            $page->assign('art', $art);
        }

        // Check blacklisted IPs
        if ($aid == 'blacklist_check') {
            global $globals;
            $ips_to_check = array();
            $blacklist_host_resolution_count = 0;

            foreach ($issue->arts as $key => $articles) {
                foreach ($articles as $article) {
                    $article_ips = $article->getLinkIps($blacklist_host_resolution_count);
                    if (!empty($article_ips)) {
                        $ips_to_check[$article->title()] = $article_ips;
                    }
                }
            }

            $page->assign('ips_to_check', $ips_to_check);
            if ($blacklist_host_resolution_count >= $globals->mail->blacklist_host_resolution_limit) {
                $page->trigError("Toutes les url et adresses emails de la lettre"
                                . " n'ont pas été prises en compte car la"
                                . " limite du nombre de résolutions DNS"
                                . " autorisée a été atteinte.");
            }
        }

        if ($issue->state == NLIssue::STATE_SENT) {
            $page->trigWarning("Cette lettre a déjà été envoyée ; il est recommandé de limiter les modifications au maximum (orthographe, adresses web et mail).");
        }

        $ufb->setEnv($issue->sufb->getEnv());
        $page->assign_by_ref('nl', $nl);
        $page->assign_by_ref('issue', $issue);
    }

    /** This handler will cancel the sending of the currently pending issue
     * It is disabled for X.org mailings.
     */
    function handler_admin_nl_cancel($page, $nid, $force = null)
    {
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if (!$nl->mayEdit() || !S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        if (!$nid) {
            $page->kill("La lettre n'a pas été spécifiée");
        }

        $issue = $nl->getIssue($nid);
        if (!$issue) {
            $page->kill("La lettre {$nid} n'existe pas.");
        }
        if (!$issue->cancelMailing()) {
            $page->trigErrorRedirect("Une erreur est survenue lors de l'annulation de l'envoi.", $nl->adminPrefix());
        }

        // Logs NL cancelling.
        S::logger()->log('nl_mailing_cancel', $nid);

        $page->trigSuccessRedirect("L'envoi de l'annonce {$issue->title()} est annulé.", $nl->adminPrefix());
    }

    /** This handler will enable the sending of the currently pending issue
     * It is disabled for X.org mailings.
     */
    function handler_admin_nl_valid($page, $nid, $force = null)
    {
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if (!$nl->mayEdit() || !S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        if (!$nid) {
            $page->kill("La lettre n'a pas été spécifiée.");
        }

        $issue = $nl->getIssue($nid);
        if (!$issue) {
            $page->kill("La lettre {$nid} n'existe pas.");
        }
        if (!$issue->scheduleMailing()) {
            $page->trigErrorRedirect("Une erreur est survenue lors de la validation de l'envoi.", $nl->adminPrefix());
        }

        // Logs NL validation.
        S::logger()->log('nl_mailing_valid', $nid);

        $page->trigSuccessRedirect("L'envoi de la newsletter {$issue->title()} a été validé.", $nl->adminPrefix());
    }

    /** This handler will remove the given issue.
     */
    function handler_admin_nl_delete($page, $nid, $force = null)
    {
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if (!$nl->mayEdit() || !S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        if (!$nid) {
            $page->kill("La lettre n'a pas été spécifiée.");
        }

        $issue = $nl->getIssue($nid);
        if (!$issue) {
            $page->kill("La lettre {$nid} n'existe pas");
        }
        if (!$issue->isEditable()) {
            $page->trigErrorRedirect("La lette a été envoyée ou est en cours d'envoi, elle ne peut être supprimée.", $nl->adminPrefix());
        }
        if (!$issue->delete()) {
            $page->trigErrorRedirect("Une erreur est survenue lors de la suppression de la lettre.", $nl->adminPrefix());
        }

        // Logs NL deletion.
        S::logger()->log('nl_issue_delete', $nid);

        $page->trigSuccessRedirect("La lettre a bien été supprimée.", $nl->adminPrefix());
    }

    function handler_admin_nl_cat($page, $action = 'list', $id = null) {
        $nl = $this->getNl();
        if (!$nl) {
            return PL_NOT_FOUND;
        }

        if (!$nl->mayEdit()) {
            return PL_FORBIDDEN;
        }

        $page->setTitle('Administration - Newsletter : Catégories');
        $page->assign('title', 'Gestion des catégories de la newsletter');
        $table_editor = new PLTableEditor($nl->adminPrefix() . '/categories', 'newsletter_cat','cid');
        $table_editor->describe('title','intitulé',true);
        $table_editor->describe('pos','position',true);
        if ($nl->group == Newsletter::GROUP_XORG) {
            $table_editor->add_option_table('newsletters', 'newsletters.id = t.nlid');
            $table_editor->add_option_field('newsletters.name', 'newsletter_name', 'Newsletter', null, 'nlid');
            $table_editor->describe('nlid', 'ID NL', true);
        } else {
            $table_editor->force_field_value('nlid', $nl->id);
            $table_editor->describe('nlid', 'nlid', false, false);
        }
        // Prevent deletion.
        $table_editor->on_delete(null, null);
        $table_editor->apply($page, $action, $id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

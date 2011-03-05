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

// {{{ class MailNotFound

class MailNotFound extends Exception {
}

// }}}

// {{{ class NewsLetter

class NewsLetter
{
    public $id;  // ID of the NL (in table newsletters)
    public $group;  // Short name of the group corresponding to the NL
    public $group_id;  // ID of that group
    public $name;  // Name of the NL (e.g "Lettre de Polytechnique.org", ...)
    public $cats;  // List of all categories for this NL
    public $criteria;  // PlFlagSet of allowed filters for recipient selection

    protected $custom_css = false;

    // Base name to use instead of the group short name for NLs without a custom CSS
    const FORMAT_DEFAULT_GROUP = 'default';

    // Diminutif of X.net groups with a specific NL view
    const GROUP_XORG = 'Polytechnique.org';
    const GROUP_AX = 'AX';
    const GROUP_EP = 'Ecole';

    // {{{ Constructor, NewsLetter retrieval (forGroup, getAll)

    public function __construct($id)
    {
        // Load NL data
        $res = XDB::query('SELECT  nls.group_id, g.diminutif AS group_name,
                                   nls.name AS nl_name, nls.custom_css, nls.criteria
                             FROM  newsletters AS nls
                        LEFT JOIN  groups AS g ON (nls.group_id = g.id)
                            WHERE  nls.id = {?}',
                            $id);
        if (!$res->numRows()) {
            throw new MailNotFound();
        }

        $data = $res->fetchOneAssoc();
        $this->id = $id;
        $this->group_id = $data['group_id'];
        $this->group = $data['group_name'];
        $this->name = $data['nl_name'];
        $this->custom_css = $data['custom_css'];
        $this->criteria = new PlFlagSet($data['criteria']);

        // Load the categories
        $res = XDB::iterRow(
            'SELECT  cid, title
               FROM  newsletter_cat
              WHERE  nlid = {?}
           ORDER BY  pos', $id);
        while (list($cid, $title) = $res->next()) {
            $this->cats[$cid] = $title;
        }
    }

    /** Retrieve the NL associated with a given group.
     * @p $group Short name of the group
     * @return A NewsLetter object, or null if the group doesn't have a NL.
     */
    public static function forGroup($group)
    {
        $res = XDB::query('SELECT  nls.id
                             FROM  newsletters AS nls
                        LEFT JOIN  groups AS g ON (nls.group_id = g.id)
                            WHERE  g.diminutif = {?}', $group);
        if (!$res->numRows()) {
            return null;
        }
        return new NewsLetter($res->fetchOneCell());
    }

    /** Retrieve all newsletters
     * @return An array of $id => NewsLetter objects
     */
    public static function getAll()
    {
        $res = XDB::query('SELECT  id
                             FROM  newsletters');
        $nls = array();
        foreach ($res->fetchColumn() as $id) {
            $nls[$id] = new NewsLetter($id);
        }
        return $nls;
    }

    // }}}
    // {{{ Issue retrieval

    /** Retrieve all issues which should be sent
     * @return An array of NLIssue objects to send (i.e state = 'new' and send_before <= today)
     */
    public static function getIssuesToSend()
    {
        $res = XDB::query('SELECT  id
                             FROM  newsletter_issues
                            WHERE  state = \'pending\' AND send_before <= NOW()');
        $issues = array();
        foreach ($res->fetchColumn() as $id) {
            $issues[$id] = new NLIssue($id);
        }
        return $issues;
    }

    /** Retrieve a given issue of this NewsLetter
     * @p $name Name or ID of the issue to retrieve.
     * @return A NLIssue object.
     *
     * $name may be either a short_name, an ID or the special value 'last' which
     * selects the latest sent NL.
     * If $name is null, this will retrieve the current pending NL.
     */
    public function getIssue($name = null, $only_sent = true)
    {
        if ($name) {
            if ($name == 'last') {
                if ($only_sent) {
                    $where = 'state = \'sent\' AND ';
                } else {
                    $where = '';
                }
                $res = XDB::query('SELECT  MAX(id)
                                     FROM  newsletter_issues
                                    WHERE  ' . $where . ' nlid = {?}',
                                   $this->id);
            } else {
                $res = XDB::query('SELECT  id
                                     FROM  newsletter_issues
                                    WHERE  nlid = {?} AND (id = {?} OR short_name = {?})',
                                  $this->id, $name, $name);
            }
            if (!$res->numRows()) {
                throw new MailNotFound();
            }
            $id = $res->fetchOneCell();
        } else {
            $query = XDB::format('SELECT  id
                                    FROM  newsletter_issues
                                   WHERE  nlid = {?} AND state = \'new\'
                                ORDER BY  id DESC', $this->id);
            $res = XDB::query($query);
            if ($res->numRows()) {
                $id = $res->fetchOneCell();
            } else {
                // Create a new, empty issue, and return it
                $id = $this->createPending();
            }
        }

        return new NLIssue($id, $this);
    }

    /** Create a new, empty, pending newsletter issue
     * @p $nlid The id of the NL for which a new pending issue should be created.
     * @return Id of the newly created issue.
     */
    public function createPending()
    {
        XDB::execute('INSERT INTO  newsletter_issues
                              SET  nlid = {?}, state=\'new\', date=NOW(),
                                   title=\'to be continued\',
                                   mail_title=\'to be continued\'',
                                   $this->id);
        return XDB::insertId();
    }

    /** Return all sent issues of this newsletter.
     * @return An array of (id => NLIssue)
     */
    public function listSentIssues($check_user = false, $user = null)
    {
        if ($check_user && $user == null) {
            $user = S::user();
        }

        $res = XDB::query('SELECT  id
                             FROM  newsletter_issues
                            WHERE  nlid = {?} AND state = \'sent\'
                         ORDER BY  date DESC', $this->id);
        $issues = array();
        foreach ($res->fetchColumn() as $id) {
            $issue = new NLIssue($id, $this, false);
            if (!$check_user || $issue->checkUser($user)) {
                $issues[$id] = $issue;
            }
        }
        return $issues;
    }

    /** Return all issues of this newsletter, including invalid and sent.
     * @return An array of (id => NLIssue)
     */
    public function listAllIssues()
    {
        $res = XDB::query('SELECT  id
                             FROM  newsletter_issues
                            WHERE  nlid = {?}
                         ORDER BY  FIELD(state, \'pending\', \'new\') DESC, date DESC', $this->id);
        $issues = array();
        foreach ($res->fetchColumn() as $id) {
            $issues[$id] = new NLIssue($id, $this, false);
        }
        return $issues;
    }

    /** Return the latest pending issue of the newsletter.
     * @p $create Whether to create an empty issue if no pending issue exist.
     * @return Either null, or a NL object.
     */
    public function getPendingIssue($create = false)
    {
        $res = XDB::query('SELECT  MAX(id)
                             FROM  newsletter_issues
                            WHERE  nlid = {?} AND state = \'new\'',
                            $this->id);
        $id = $res->fetchOneCell();
        if ($id != null) {
            return new NLIssue($id, $this);
        } else if ($create) {
            $id = $this->createPending();
            return new NLIssue($id, $this);
        } else {
            return null;
        }
    }

    // }}}
    // {{{ Subscription related function

    /** Unsubscribe a user from this newsletter
     * @p $uid UID to unsubscribe from the newsletter; if null, use current user.
     * @p $hash True if the uid is actually a hash.
     * @return True if the user was successfully unsubscribed.
     */
    public function unsubscribe($uid = null, $hash = false)
    {
        if (is_null($uid) && $hash) {
            // Unable to unsubscribe from an empty hash
            return false;
        }
        $user = is_null($uid) ? S::user()->id() : $uid;
        $field = $hash ? 'hash' : 'uid';
        $res = XDB::query('SELECT  uid
                             FROM  newsletter_ins
                            WHERE  nlid = {?} AND ' . $field . ' = {?}',
                            $this->id, $user);
        if (!$res->numRows()) {
            // No subscribed user with that UID/hash
            return false;
        }
        $user = $res->fetchOneCell();

        XDB::execute('DELETE FROM  newsletter_ins
                            WHERE  nlid = {?} AND uid = {?}',
                            $this->id, $user);
        return true;
    }

    /** Subscribe a user to a newsletter
     * @p $user User to subscribe to the newsletter; if null, use current user.
     */
    public function subscribe($user = null)
    {
        if (is_null($user)) {
            $user = S::user();
        }
        if (self::maySubscribe($user)) {
            XDB::execute('INSERT IGNORE INTO  newsletter_ins (nlid, uid, last, hash)
                                      VALUES  ({?}, {?}, NULL, hash)',
                         $this->id, $user->id());
        }
    }

    /** Retrieve subscription state of a user
     * @p $user Target user; if null, use current user.
     * @return Boolean: true if the user has subscribed to the NL.
     */
    public function subscriptionState($user = null)
    {
        if (is_null($user)) {
            $user = S::user();
        }
        $res = XDB::query('SELECT  1
                             FROM  newsletter_ins
                            WHERE  nlid = {?} AND uid = {?}',
                          $this->id, $user->id());
        return ($res->numRows() == 1);
    }

    /** Get the count of subscribers to the NL.
     * @return Number of subscribers.
     */
    public function subscriberCount()
    {
        return XDB::fetchOneCell('SELECT  COUNT(uid)
                                    FROM  newsletter_ins
                                   WHERE  nlid = {?}', $this->id);
    }

    /** Get the number of subscribers to the NL whose last received mailing was $last.
     * @p $last ID of the issue for which subscribers should be counted.
     * @return Number of subscribers
     */
    public function subscriberCountForLast($last)
    {
        return XDB::fetchOneCell('SELECT  COUNT(uid)
                                    FROM  newsletter_ins
                                   WHERE  nlid = {?} AND last = {?}', $this->id, $last);
    }

    /** Retrieve the list of newsletters a user has subscribed to
     * @p $user User whose subscriptions should be retrieved (if null, use session user).
     * @return Array of newsletter IDs
     */
    public static function getUserSubscriptions($user = null)
    {
        if (is_null($user)) {
            $user = S::user();
        }
        $res = XDB::query('SELECT  nlid
                             FROM  newsletter_ins
                            WHERE  uid = {?}',
                          $user->id());
        return $res->fetchColumn();
    }

    /** Retrieve the UserFilterBuilder for subscribers to this NL.
     * This is the place where NL-specific filters may be allowed or prevented.
     * @p $envprefix Prefix to use for env fields (cf. UserFilterBuilder)
     * @return A UserFilterBuilder object using the given env prefix
     */
    public function getSubscribersUFB($envprefix = '')
    {
        require_once 'ufbuilder.inc.php';
        return new UFB_NewsLetter($this->criteria, $envprefix);
    }

    // }}}
    // {{{ Permissions related functions

    /** For later use: check whether a given user may subscribe to this newsletter.
     * @p $user User whose access should be checked
     * @return Boolean: whether the user may subscribe to the NL.
     */
    public function maySubscribe($user = null)
    {
        return true;
    }

    /** Whether a given user may edit this newsletter
     * @p $uid UID of the user whose perms should be checked (if null, use current user)
     * @return Boolean: whether the user may edit the NL
     */
    public function mayEdit($user = null)
    {
        if (is_null($user)) {
            $user = S::user();
        }
        if ($user->checkPerms('admin')) {
            return true;
        }
        $res = XDB::query('SELECT  perms
                             FROM  group_members
                            WHERE  asso_id = {?} AND uid = {?}',
                            $this->group_id, $user->id());
        return ($res->numRows() && $res->fetchOneCell() == 'admin');
    }

    /** Whether a given user may submit articles to this newsletter using X.org validation system
     * @p $user User whose access should be checked (if null, use current user)
     * @return Boolean: whether the user may submit articles
     */
    public function maySubmit($user = null)
    {
        // Submission of new articles is only enabled for the X.org NL (and forbidden when viewing issues on X.net)
        return ($this->group == self::GROUP_XORG && !isset($GLOBALS['IS_XNET_SITE']));
    }

    // }}}
    // {{{ Display-related functions: cssFile, tplFile, prefix, admin_prefix, admin_links_enabled, automatic_mailings_enabled

    /** Get the name of the css file used to display this newsletter.
     */
    public function cssFile()
    {
        if ($this->custom_css) {
            $base = $this->group;
        } else {
            $base = self::FORMAT_DEFAULT_GROUP;
        }
        return 'nl.' . $base . '.css';
    }

    /** Get the name of the template file used to display this newsletter.
     */
    public function tplFile()
    {
        if ($this->custom_css) {
            $base = $this->group;
        } else {
            $base = self::FORMAT_DEFAULT_GROUP;
        }
        return 'newsletter/nl.' . $base . '.mail.tpl';
    }

    /** Get the prefix leading to the page for this NL
     * Only X.org / AX / X groups may be seen on X.org.
     */
    public function prefix($enforce_xnet=true)
    {
        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            return $this->group . '/nl';
        }
        switch ($this->group) {
        case self::GROUP_XORG:
            return 'nl';
        case self::GROUP_AX:
            return 'ax';
        case self::GROUP_EP:
            return 'epletter';
        default:
            // Don't display groups NLs on X.org
            assert(!$enforce_xnet);
        }
    }

    /** Get the prefix to use for all 'admin' pages of this NL.
     */
    public function adminPrefix($enforce_xnet=true)
    {
        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            return $this->group . '/admin/nl';
        }
        switch ($this->group) {
        case self::GROUP_XORG:
            return 'admin/newsletter';
        case self::GROUP_AX:
            return 'ax/admin';
        case self::GROUP_EP:
            return 'epletter/admin';
        default:
            // Don't display groups NLs on X.org
            assert(!$enforce_xnet);
        }
    }

    /** Hack used to remove "admin" links on X.org page on X.net
     * The 'admin' links are enabled for all pages, except for X.org when accessing NL through X.net
     */
    public function adminLinksEnabled()
    {
        return ($this->group != self::GROUP_XORG || !isset($GLOBALS['IS_XNET_SITE']));
    }

    /** Automatic mailings are disabled for X.org NL.
     */
    public function automaticMailingEnabled()
    {
        return $this->group != self::GROUP_XORG;
    }

    public function hasCustomCss()
    {
        return $this->custom_css;
    }

    // }}}
}

// }}}

// {{{ class NLIssue

// A NLIssue is an issue of a given NewsLetter
class NLIssue
{
    protected $nlid;  // Id of the newsletter

    const STATE_NEW = 'new';  // New, currently being edited
    const STATE_PENDING = 'pending';  // Ready for mailing
    const STATE_SENT = 'sent';  // Sent

    public $nl;  // Related NL

    public $id;  // Id of this issue of the newsletter
    public $shortname;  // Shortname for this issue
    public $title;  // Title of this issue
    public $title_mail;  // Title of the email
    public $state;  // State of the issue (one of the STATE_ values)
    public $sufb;  // Environment to use to generate the UFC through an UserFilterBuilder

    public $date;  // Date at which this issue was sent
    public $send_before;  // Date at which issue should be sent
    public $head;  // Foreword of the issue (or body for letters with no articles)
    public $signature;  // Signature of the letter
    public $arts = array();  // Articles of the issue

    const BATCH_SIZE = 60;  // Number of emails to send every minute.

    // {{{ Constructor, id-related functions

    /** Build a NewsLetter.
     * @p $id: ID of the issue (unique among all newsletters)
     * @p $nl: Optional argument containing an already built NewsLetter object.
     */
    function __construct($id, $nl = null, $fetch_articles = true)
    {
        return $this->fetch($id, $nl, $fetch_articles);
    }

    protected function refresh()
    {
        return $this->fetch($this->id, $this->nl, false);
    }

    protected function fetch($id, $nl = null, $fetch_articles = true)
    {
        // Load this issue
        $res = XDB::query('SELECT  nlid, short_name, date, send_before, state, sufb_json,
                                   title, mail_title, head, signature
                             FROM  newsletter_issues
                            WHERE  id = {?}',
                          $id);
        if (!$res->numRows()) {
            throw new MailNotFound();
        }
        $issue = $res->fetchOneAssoc();
        if ($nl && $nl->id == $issue['nlid']) {
            $this->nl = $nl;
        } else {
            $this->nl = new NewsLetter($issue['nlid']);
        }
        $this->id = $id;
        $this->shortname   = $issue['short_name'];
        $this->date        = $issue['date'];
        $this->send_before = $issue['send_before'];
        $this->state       = $issue['state'];
        $this->title       = $issue['title'];
        $this->title_mail  = $issue['mail_title'];
        $this->head        = $issue['head'];
        $this->signature   = $issue['signature'];
        $this->sufb        = $this->importJSonStoredUFB($issue['sufb_json']);

        if ($fetch_articles) {
            $this->fetchArticles();
        }
    }

    protected function fetchArticles($force = false)
    {
        if (count($this->arts) && !$force) {
            return;
        }

        // Load the articles
        $res = XDB::iterRow(
            'SELECT  a.title, a.body, a.append, a.aid, a.cid, a.pos
               FROM  newsletter_art AS a
         INNER JOIN  newsletter_issues AS ni USING(id)
         LEFT  JOIN  newsletter_cat AS c ON (a.cid = c.cid)
              WHERE  a.id = {?}
           ORDER BY  c.pos, a.pos',
           $this->id);
        while (list($title, $body, $append, $aid, $cid, $pos) = $res->next()) {
            $this->arts[$cid][$aid] = new NLArticle($title, $body, $append, $aid, $cid, $pos);
        }
    }

    protected function importJSonStoredUFB($json = null)
    {
        require_once 'ufbuilder.inc.php';
        $ufb = $this->nl->getSubscribersUFB();
        if (is_null($json)) {
            return new StoredUserFilterBuilder($ufb, new PFC_True());
        }
        $export = json_decode($json, true);
        if (is_null($export)) {
            PlErrorReport::report("Invalid json while reading NL {$this->nlid}, issue {$this->id}: failed to import '''{$json}'''.");
            return new StoredUserFilterBuilder($ufb, new PFC_True());
        }
        $sufb = new StoredUserFilterBuilder($ufb);
        $sufb->fillFromExport($export);
        return $sufb;
    }

    protected function exportStoredUFBAsJSon()
    {
        return json_encode($this->sufb->export());
    }

    public function id()
    {
        return is_null($this->shortname) ? $this->id : $this->shortname;
    }

    protected function selectId($where)
    {
        $res = XDB::query("SELECT  IFNULL(ni.short_name, ni.id)
                             FROM  newsletter_issues AS ni
                            WHERE  ni.state != 'new' AND ni.nlid = {?} AND ${where}
                            LIMIT  1", $this->nl->id);
        if ($res->numRows() != 1) {
            return null;
        }
        return $res->fetchOneCell();
    }

    /** Delete this issue
     * @return True if the issue could be deleted, false otherwise.
     * Related articles will be deleted through cascading FKs.
     * If this issue was the last issue for at least one subscriber, the deletion will be aborted.
     */
    public function delete()
    {
        if ($this->state == self::STATE_NEW) {
            $res = XDB::query('SELECT  COUNT(*)
                                 FROM  newsletter_ins
                                WHERE  last = {?}', $this->id);
            if ($res->fetchOneCell() > 0) {
                return false;
            }

            return XDB::execute('DELETE FROM  newsletter_issues
                                       WHERE  id = {?}', $this->id);
        } else {
            return false;
        }
    }

    /** Schedule a mailing of this NL
     * If the 'send_before' field was NULL, it is set to the current time.
     * @return Boolean Whether the date could be set (false if trying to schedule an already sent NL)
     */
    public function scheduleMailing()
    {
        if ($this->state == self::STATE_NEW) {
            $success = XDB::execute('UPDATE  newsletter_issues
                                        SET  state = \'pending\', send_before = IFNULL(send_before, NOW())
                                      WHERE  id = {?}',
                                      $this->id);
            if ($success) {
                global $globals;
                $mailer = new PlMailer('newsletter/notify_scheduled.mail.tpl');
                $mailer->assign('issue', $this);
                $mailer->assign('base', $globals->baseurl);
                $mailer->send();
                $this->refresh();
            }
            return $success;
        } else {
            return false;
        }
    }

    /** Cancel the scheduled mailing of this NL
     * @return Boolean: whether the mailing could be cancelled.
     */
    public function cancelMailing()
    {
        if ($this->state == self::STATE_PENDING) {
            $success = XDB::execute('UPDATE  newsletter_issues
                                        SET  state = \'new\'
                                      WHERE  id = {?}', $this->id);
            if ($success) {
                $this->refresh();
            }
            return $success;
        } else {
            return false;
        }
    }

    /** Helper function for smarty templates: is this issue editable ?
     */
    public function isEditable()
    {
        return $this->state == self::STATE_NEW;
    }

    /** Helper function for smarty templates: is the mailing of this issue scheduled ?
     */
    public function isPending()
    {
        return $this->state == self::STATE_PENDING;
    }

    /** Helper function for smarty templates: has this issue been sent ?
     */
    public function isSent()
    {
        return $this->state == self::STATE_SENT;
    }

    // }}}
    // {{{ Navigation

    private $id_prev = null;
    private $id_next = null;
    private $id_last = null;

    /** Retrieve ID of the previous issue
     * That value, once fetched, is cached in the private $id_prev variable.
     * @return ID of the previous issue.
     */
    public function prev()
    {
        if (is_null($this->id_prev)) {
            $this->id_prev = $this->selectId(XDB::format("ni.id < {?} ORDER BY ni.id DESC", $this->id));
        }
        return $this->id_prev;
    }

    /** Retrieve ID of the following issue
     * That value, once fetched, is cached in the private $id_next variable.
     * @return ID of the following issue.
     */
    public function next()
    {
        if (is_null($this->id_next)) {
            $this->id_next = $this->selectId(XDB::format("ni.id > {?} ORDER BY ni.id", $this->id));
        }
        return $this->id_next;
    }

    /** Retrieve ID of the last issue
     * That value, once fetched, is cached in the private $id_last variable.
     * @return ID of the last issue.
     */
    public function last()
    {
        if (is_null($this->id_last)) {
            $this->id_last = $this->nl->getIssue('last')->id;
        }
        return $this->id_last;
    }

    // }}}
    // {{{ Edition, articles

    const ERROR_INVALID_SHORTNAME = 'invalid_shortname';
    const ERROR_INVALID_UFC = 'invalid_ufc';
    const ERROR_SQL_SAVE = 'sql_error';

    /** Save the global properties of this NL issue (title&co).
     */
    public function save()
    {
        $errors = array();

        // Fill the list of fields to update
        $fields = array(
            'title' => $this->title,
            'mail_title' => $this->title_mail,
            'head' => $this->head,
            'signature' => $this->signature,
        );

        if ($this->isEditable()) {
            $fields['date'] = $this->date;
            if (!preg_match('/^[-a-z0-9]+$/i', $this->shortname) || is_numeric($this->shortname)) {
                $errors[] = self::ERROR_INVALID_SHORTNAME;
            } else {
                $fields['short_name'] = $this->shortname;
            }
            if ($this->sufb->isValid() || $this->sufb->isEmpty()) {
                $fields['sufb_json'] = json_encode($this->sufb->export()->dict());
            } else {
                $errors[] = self::ERROR_INVALID_UFC;
            }

            if ($this->nl->automaticMailingEnabled()) {
                $fields['send_before'] = ($this->send_before ? $this->send_before : null);
            }
        }

        if (count($errors)) {
            return $errors;
        }
        $field_sets = array();
        foreach ($fields as $key => $value) {
            $field_sets[] = XDB::format($key . ' = {?}', $value);
        }
        XDB::execute('UPDATE  newsletter_issues
                         SET  ' . implode(', ', $field_sets) . '
                       WHERE  id={?}',
                       $this->id);
        if (XDB::affectedRows()) {
            $this->refresh();
        } else {
            $errors[] = self::ERROR_SQL_SAVE;
        }
        return $errors;
    }

    /** Get an article by number
     * @p $aid Article ID (among articles of the issue)
     * @return A NLArticle object, or null if there is no article by that number
     */
    public function getArt($aid)
    {
        $this->fetchArticles();

        foreach ($this->arts as $category => $artlist) {
            if (isset($artlist[$aid])) {
                return $artlist[$aid];
            }
        }
        return null;
    }

    /** Save an article
     * @p $a A reference to a NLArticle object (will be modified once saved)
     */
    public function saveArticle($a)
    {
        $this->fetchArticles();

        // Prevent cid to be 0 (use NULL instead)
        $a->cid = ($a->cid == 0) ? null : $a->cid;
        if ($a->aid >= 0) {
            // Article already exists in DB
            XDB::execute('UPDATE  newsletter_art
                             SET  cid = {?}, pos = {?}, title = {?}, body = {?}, append = {?}
                           WHERE  id = {?} AND aid = {?}',
                         $a->cid, $a->pos, $a->title, $a->body, $a->append, $this->id, $a->aid);
        } else {
            // New article
            XDB::startTransaction();
            list($aid, $pos) = XDB::fetchOneRow('SELECT  MAX(aid) AS aid, MAX(pos) AS pos
                                                   FROM  newsletter_art AS a
                                                  WHERE  a.id = {?}',
                                                $this->id);
            $a->aid = ++$aid;
            $a->pos = ($a->pos ? $a->pos : ++$pos);
            XDB::execute('INSERT INTO  newsletter_art (id, aid, cid, pos, title, body, append)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?})',
                         $this->id, $a->aid, $a->cid, $a->pos,
                         $a->title, $a->body, $a->append);
            XDB::commit();
        }
        // Update local ID of article
        $this->arts[$a->aid] = $a;
    }

    /** Delete an article by its ID
     * @p $aid ID of the article to delete
     */
    public function delArticle($aid)
    {
        $this->fetchArticles();

        XDB::execute('DELETE FROM newsletter_art WHERE id={?} AND aid={?}', $this->id, $aid);
        foreach ($this->arts as $key=>$art) {
            unset($this->arts[$key][$aid]);
        }
    }

    // }}}
    // {{{ Display

    /** Retrieve the title of this issue
     * @p $mail Whether we want the normal title or the email subject
     * @return Title of the issue
     */
    public function title($mail = false)
    {
        return $mail ? $this->title_mail : $this->title;
    }

    /** Retrieve the head of this issue
     * @p $user User for <dear> customization (may be null: no customization)
     * @p $type Either 'text' or 'html'
     * @return Formatted head of the issue.
     */
    public function head($user = null, $type = 'text')
    {
        if (is_null($user)) {
            return $this->head;
        } else {
            $head = $this->head;
            $head = str_replace(array('<cher>', '<prenom>', '<nom>'),
                                array(($user->isFemale() ? 'Chère' : 'Cher'), $user->displayName(), ''),
                                $head);
            return format_text($head, $type, 2, 64);
        }
    }

    /** Retrieve the formatted signature of this issue.
     */
    public function signature($type = 'text')
    {
        return format_text($this->signature, $type, 2, 64);
    }

    /** Get the title of a given category
     * @p $cid ID of the category to retrieve
     * @return Name of the category
     */
    public function category($cid)
    {
        return $this->nl->cats[$cid];
    }

    /** Add required data to the given $page for proper CSS display
     * @p $page Smarty object
     * @return Either 'true' (if CSS was added to a page) or the raw CSS to add (when $page is null)
     */
    public function css($page = null)
    {
        if (!is_null($page)) {
            $page->addCssLink($this->nl->cssFile());
            return true;
        } else {
            $css = file_get_contents(dirname(__FILE__) . '/../htdocs/css/' . $this->nl->cssFile());
            return preg_replace('@/\*.*?\*/@us', '', $css);
        }
    }

    /** Set up a smarty page for a 'text' mode render of the issue
     * @p $page Smarty object (using the $this->nl->tplFile() template)
     * @p $user User to use when rendering the template
     */
    public function toText($page, $user)
    {
        $this->fetchArticles();

        $this->css($page);
        $page->assign('prefix', null);
        $page->assign('is_mail', false);
        $page->assign('mail_part', 'text');
        $page->assign('user', $user);
        $page->assign('hash', null);
        $this->assignData($page);
    }

    /** Set up a smarty page for a 'html' mode render of the issue
     * @p $page Smarty object (using the $this->nl->tplFile() template)
     * @p $user User to use when rendering the template
     */
    public function toHtml($page, $user)
    {
        $this->fetchArticles();

        $this->css($page);
        $page->assign('prefix', $this->nl->prefix() . '/show/' . $this->id());
        $page->assign('is_mail', false);
        $page->assign('mail_part', 'html');
        $page->assign('user', $user);
        $page->assign('hash', null);
        $this->assignData($page);
    }

    /** Set all 'common' data for the page (those which are required for both web and email rendering)
     * @p $smarty Smarty object (e.g page) which should be filled
     */
    protected function assignData($smarty)
    {
        $this->fetchArticles();

        $smarty->assign_by_ref('issue', $this);
        $smarty->assign_by_ref('nl', $this->nl);
    }

    // }}}
    // {{{ Mailing

    /** Check whether this issue is empty
     * An issue is empty if the email has no title (or the default one), or no articles and an empty head.
     */
    public function isEmpty()
    {
        return $this->title_mail == '' || $this->title_mail == 'to be continued' || (count($this->arts) == 0 && strlen($this->head) == 0);
    }

    /** Retrieve the 'Send before' date, in a clean format.
     */
    public function getSendBeforeDate()
    {
        return strftime('%Y-%m-%d', strtotime($this->send_before));
    }

    /** Retrieve the 'Send before' time (i.e hour), in a clean format.
     */
    public function getSendBeforeTime()
    {
        return strtotime($this->send_before);
    }

    /** Create a hash based on some additional data
     * $line Line-specific data (to prevent two hashes generated at the same time to be the same)
     */
    protected static function createHash($line)
    {
        $hash = implode(time(), $line) . rand();
        $hash = md5($hash);
        return $hash;
    }

    /** Send this issue to the given user, reusing an existing hash if provided.
     * @p $user User to whom the issue should be mailed
     * @p $hash Optional hash to use in the 'unsubscribe' link; if null, another one will be generated.
     */
    public function sendTo($user, $hash = null)
    {
        $this->fetchArticles();

        if (is_null($hash)) {
            $hash = XDB::fetchOneCell("SELECT  hash
                                         FROM  newsletter_ins
                                        WHERE  uid = {?} AND nlid = {?}",
                                      $user->id(), $this->nl->id);
        }
        if (is_null($hash)) {
            $hash = self::createHash(array($user->displayName(), $user->fullName(),
                                       $user->isFemale(), $user->isEmailFormatHtml(),
                                       rand(), "X.org rulez"));
            XDB::execute("UPDATE  newsletter_ins as ni
                             SET  ni.hash = {?}
                           WHERE  ni.uid = {?} AND ni.nlid = {?}",
                         $hash, $user->id(), $this->nl->id);
        }

        $mailer = new PlMailer($this->nl->tplFile());
        $this->assignData($mailer);
        $mailer->assign('is_mail', true);
        $mailer->assign('user', $user);
        $mailer->assign('prefix',  null);
        $mailer->assign('hash',    $hash);
        $mailer->sendTo($user);
    }

    /** Select a subset of subscribers which should receive the newsletter.
     * NL-Specific selections (not yet received, is subscribed) are done when sending.
     * @return A PlFilterCondition.
     */
    protected function getRecipientsUFC()
    {
        return $this->sufb->getUFC();
    }

    /** Check whether a given user may see this issue.
     * @p $user User whose access should be checked
     * @return Whether he may access the issue
     */
    public function checkUser($user = null)
    {
        if ($user == null) {
            $user = S::user();
        }
        $uf = new UserFilter($this->getRecipientsUFC());
        return $uf->checkUser($user);
    }

    /** Sent this issue to all valid recipients
     * @return Number of issues sent
     */
    public function sendToAll()
    {
        $this->fetchArticles();

        XDB::execute('UPDATE  newsletter_issues
                         SET  state = \'sent\', date=CURDATE()
                       WHERE  id = {?}',
                       $this->id);

        $ufc = new PFC_And($this->getRecipientsUFC(), new UFC_NLSubscribed($this->nl->id, $this->id), new UFC_HasValidEmail());
        $emailsCount = 0;
        $uf = new UserFilter($ufc, array(new UFO_IsAdmin(), new UFO_Uid()));
        $limit = new PlLimit(self::BATCH_SIZE);

        while (true) {
            $sent = array();
            $users = $uf->getUsers($limit);
            if (count($users) == 0) {
                return $emailsCount;
            }
            foreach ($users as $user) {
                $sent[] = $user->id();
                $this->sendTo($user, $hash);
                ++$emailsCount;
            }
            XDB::execute("UPDATE  newsletter_ins
                             SET  last = {?}
                           WHERE  nlid = {?} AND uid IN {?}", $this->id, $this->nl->id, $sent);

            sleep(60);
        }
        return $emailsCount;
    }

    // }}}
}

// }}}
// {{{ class NLArticle

class NLArticle
{
    // Maximum number of lines per article
    const MAX_LINES_PER_ARTICLE = 9;

    // {{{ properties

    public $aid;
    public $cid;
    public $pos;
    public $title;
    public $body;
    public $append;

    // }}}
    // {{{ constructor

    function __construct($title='', $body='', $append='', $aid=-1, $cid=0, $pos=0)
    {
        $this->body   = $body;
        $this->title  = $title;
        $this->append = $append;
        $this->aid    = $aid;
        $this->cid    = $cid;
        $this->pos    = $pos;
    }

    // }}}
    // {{{ function title()

    public function title()
    { return trim($this->title); }

    // }}}
    // {{{ function body()

    public function body()
    { return trim($this->body); }

    // }}}
    // {{{ function append()

    public function append()
    { return trim($this->append); }

    // }}}
    // {{{ function toText()

    public function toText($hash = null, $login = null)
    {
        $title = '*'.$this->title().'*';
        $body = MiniWiki::WikiToText($this->body, true);
        $app = MiniWiki::WikiToText($this->append, false, 4);
        $text = trim("$title\n\n$body\n\n$app")."\n";
        if (!is_null($hash) && !is_null($login)) {
            $text = str_replace('%HASH%', "$hash/$login", $text);
        } else {
            $text = str_replace('%HASH%', '', $text);
        }
        return $text;
    }

    // }}}
    // {{{ function toHtml()

    public function toHtml($hash = null, $login = null)
    {
        $title = "<h2 class='xorg_nl'><a id='art{$this->aid}'></a>".pl_entities($this->title()).'</h2>';
        $body  = MiniWiki::WikiToHTML($this->body);
        $app   = MiniWiki::WikiToHTML($this->append);

        $art   = "$title\n";
        $art  .= "<div class='art'>\n$body\n";
        if ($app) {
            $art .= "<div class='app'>$app</div>";
        }
        $art  .= "</div>\n";
        if (!is_null($hash) && !is_null($login)) {
            $art = str_replace('%HASH%', "$hash/$login", $art);
        } else {
            $art = str_replace('%HASH%', '', $art);
        }

        return $art;
    }

    // }}}
    // {{{ function check()

    public function check()
    {
        $text = MiniWiki::WikiToText($this->body);
        $arr  = explode("\n",wordwrap($text,68));
        $c    = 0;
        foreach ($arr as $line) {
            if (trim($line)) {
                $c++;
            }
        }
        return $c < self::MAX_LINES_PER_ARTICLE;
    }

    // }}}
    // {{{ function parseUrlsFromArticle()

    protected function parseUrlsFromArticle()
    {
        $email_regex = '([a-z0-9.\-+_\$]+@([\-.+_]?[a-z0-9])+)';
        $url_regex = '((https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+)';
        $regex = '{' . $email_regex . '|' . $url_regex . '}i';

        $matches = array();
        $body_matches = array();
        if (preg_match_all($regex, $this->body(), $body_matches)) {
            $matches = array_merge($matches, $body_matches[0]);
        }

        $append_matches = array();
        if (preg_match_all($regex, $this->append(), $append_matches)) {
            $matches = array_merge($matches, $append_matches[0]);
        }

        return $matches;
    }

    // }}}
    // {{{ function getLinkIps()

    public function getLinkIps(&$blacklist_host_resolution_count)
    {
        $matches = $this->parseUrlsFromArticle();
        $article_ips = array();

        if (!empty($matches)) {
            global $globals;

            foreach ($matches as $match) {
                $host = parse_url($match, PHP_URL_HOST);
                if ($host == '') {
                    list(, $host) = explode('@', $match);
                }

                if ($blacklist_host_resolution_count >= $globals->mail->blacklist_host_resolution_limit) {
                   break;
                }

                if (!preg_match('/^(' . str_replace(' ', '|', $globals->mail->domain_whitelist) . ')$/i', $host)) {
                    $article_ips = array_merge($article_ips, array(gethostbyname($host) => $host));
                    ++$blacklist_host_resolution_count;
                }
            }
        }

        return $article_ips;
    }

    // }}}
}

// }}}

// {{{ Functions

function format_text($input, $format, $indent = 0, $width = 68)
{
    if ($format == 'text') {
        return MiniWiki::WikiToText($input, true, $indent, $width, "title");
    }
    return MiniWiki::WikiToHTML($input, "title");
}

// function enriched_to_text($input,$html=false,$just=false,$indent=0,$width=68)

// }}}

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>

<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

require_once dirname(__FILE__) . '/../../banana/banana/banana.inc.php';
require_once dirname(__FILE__) . '/../../banana/banana/message.func.inc.php';

function hook_formatDisplayHeader($_header, $_text, $in_spool = false)
{
    switch ($_header) {
      case 'from': case 'to': case 'cc': case 'reply-to':
        $addresses = preg_split("/ *, */", $_text);
        $text = '';
        foreach ($addresses as $address) {
            $address = BananaMessage::formatFrom(trim($address), Banana::$message->getHeaderValue('subject'));
            if ($_header == 'from') {
                if ($id = Banana::$message->getHeaderValue('x-org-id')) {
                    return $address . ' <a href="profile/' . $id . '" class="popup2" title="' . $id . '">'
                        . '<img src="images/icons/user_suit.gif" title="fiche" alt="" /></a>';
                } elseif ($id = Banana::$message->getHeaderValue('x-org-mail')) {
                    list($id, $domain) = explode('@', $id);
                    return $address . ' <a href="profile/' . $id . '" class="popup2" title="' . $id . '">'
                        . '<img src="images/icons/user_suit.gif" title="fiche" alt="" /></a>';
                } else {
                    return $address;
                }
            }
            if (!empty($text)) {
                $text .= ', ';
            }
            $text .= $address;
        }
        return $text;

      case 'subject':
        $link = null;
        $text = stripslashes($_text);
        if (preg_match('/^(.+?)\s*\[=> (.*?)\]\s*$/u', $text, $matches)) {
            $text = $matches[1];
            $group = $matches[2];
            if (Banana::$group == $group) {
                $link = ' [=>&nbsp;' . $group . ']';
            } else {
                $link = ' [=>&nbsp;' . Banana::$page->makeLink(array('group' => $group, 'text' => $group)) . ']';
            }
        }
        $text = banana_catchFormats(banana_htmlentities($text));
        if ($in_spool) {
            return array($text, $link);
        }
        return $text . $link;
    }
    return null;
}

function hook_platalRSS($group)
{
    if ($group) {
        $group .= '/';
    } else {
        $group = '';
    }
    return '/rss/' . $group . S::v('hruid') . '/' . S::user()->token . '/rss.xml';
}

function hook_platalMessageLink($params)
{
    $base = '';
    if (isset($params['first'])) {
        return $base . '/from/' . $params['first'];
    }
    if (isset($params['artid'])) {
        if (@$params['part'] == 'xface') {
            $base .= '/xface';
        } elseif (@$params['action'] == 'new') {
            $base .= '/reply';
        } elseif (@$params['action'] == 'cancel') {
            $base .= '/cancel';
        } elseif (@$params['part']) {
            if (strpos($params['part'], '.') !== false) {
                $params['artid'] .= '?part=' . urlencode($params['part']);
                $base = '/read';
            } else {
                $base .= '/' . str_replace('/', '.', $params['part']);
            }
        } else {
            $base .= '/read';
        }
        return $base . '/' . $params['artid'];
    }

    if (@$params['action'] == 'new') {
        return $base . '/new';
    }
    return $base;
}

function hook_makeImg($img, $alt, $height, $width)
{
    global $globals;
    $url = $globals->baseurl . '/images/banana/' . $img;

    if (!is_null($width)) {
        $width = ' width="' . $width . '"';
    }
    if (!is_null($height)) {
        $height = ' height="' . $height . '"';
    }

    return '<img src="' . $url . '"' . $height . $width . ' alt="' . $alt . '" />';
}

if (!function_exists('hook_makeLink')) {
function hook_makeLink($params)
{
    global $globals, $platal;
    $xnet = !empty($GLOBALS['IS_XNET_SITE']);
    $feed = (@$params['action'] == 'rss' || @$params['action'] == 'rss2' || @$params['action'] == 'atom');
    if (Banana::$protocole->name() == 'NNTP' && !$xnet) {
        $base = $globals->baseurl . '/banana';
        if ($feed) {
            return $base . hook_platalRSS(@$params['group']);
        }
        if (isset($params['page'])) {
            return $base . '/' . $params['page'];
        }
        if (@$params['action'] == 'subscribe') {
            return $base . '/subscribe';
        }

        if (!isset($params['group'])) {
            return $base;
        }
        $base .= '/' . $params['group'];
    } else if (Banana::$protocole->name() == 'NNTP' && $xnet) {
        if ($feed) {
            return $globals->baseurl . '/banana' . hook_platalRSS(@$params['group']);
        }
        $base = $globals->baseurl . '/' . $platal->ns . 'forum';
    } else if (Banana::$protocole->name() == 'MLArchives') {
        if ($feed) {
            return $globals->baseurl . '/' . $platal->ns . hook_platalRSS(MLBanana::$listname);
        } elseif (php_sapi_name() == 'cli') {
            $base = "http://listes.polytechnique.org/archives/" . str_replace('@', '_', $params['group']);
        } else {
            $base = $globals->baseurl . '/' . $platal->ns . 'lists/archives/' . MLBanana::$listname;
        }
    }
    $base = $base . hook_platalMessageLink($params);
    if (@$params['action'] == 'showext') {
        $base .= '?action=showext';
    }
    return $base;
}
}

function hook_hasXFace($headers)
{
    return isset($headers['x-org-id']) || isset($headers['x-org-mail']);
}

function hook_getXFace($headers)
{
    $login = null;
    foreach (array('x-org-id', 'x-org-mail') as $key) {
        if (isset($headers[$key])) {
            $login = $headers[$key];
            break;
        }
    }
    if (is_null($login)) {
        // No login, fallback to default handler
        return false;
    }
    if (isset($headers['x-face'])) {
        $user = User::getSilent($login);
        $res = XDB::query("SELECT  pf.uid
                             FROM  forum_profiles AS pf
                            WHERE  pf.uid = {?} AND FIND_IN_SET('xface', pf.flags)",
                          $user->id());
        if ($res->numRows()) {
            // User wants his xface to be showed, fallback to default handler
            return false;
        }
    }
    global $globals;
    http_redirect($global->baseurl . '/photo/' . $login);
}

function hook_makeJs($src)
{
    Platal::page()->addJsLink("$src.js");
    return ' ';
}

function make_Organization()
{
    global $globals;
    $perms = S::v('perms');
    $group = $globals->asso('nom');
    if (S::admin()) {
        return "Administrateur de Polytechnique.org";
    } else if ($group && $perms->hasFlag('groupadmin')) {
        return "Animateur de $group";
    } else if ($group && $perms->hasFlag('groupmember')) {
        return "Membre de $group";
    }
    return "Utilisateur de Polytechnique.org";
}

function get_banana_params(array &$get, $group = null, $action = null, $artid = null)
{
    if ($group == 'forums') {
        $group = null;
    } else if ($group == 'thread') {
        $group = S::v('banana_group');
    } else if ($group == 'message') {
        $action = 'read';
        $group  = S::v('banana_group');
        $artid  = S::i('banana_artid');
    } else if ($action == 'message') {
        $action = 'read';
        $artid  = S::i('banana_artid');
    } else if ($group == 'subscribe' || $group == 'subscription') {
        $group  = null;
        $action = null;
        $get['action'] = 'subscribe';
    } else if ($group == 'profile') {
        $group  = null;
        $action = null;
        $get['action'] = 'profile';
    }
    if (!is_null($group)) {
        $get['group'] = $group;
    }
    if (!is_null($action)) {
        if ($action == 'new') {
            $get['action'] = 'new';
        } elseif (!is_null($artid)) {
            $get['artid'] = $artid;
            if ($action == 'reply') {
                $get['action'] = 'new';
            } elseif ($action == 'cancel') {
                $get['action'] = $action;
            } elseif ($action == 'from') {
                $get['first'] = $artid;
                unset($get['artid']);
            } elseif ($action == 'read') {
                $get['part']  = @$_GET['part'];
            } elseif ($action == 'source') {
                $get['part'] = 'source';
            } elseif ($action == 'xface') {
                $get['part']  = 'xface';
            } elseif ($action) {
                $get['part'] = str_replace('.', '/', $action);
            }
            if (Get::v('action') == 'showext') {
                $get['action'] = 'showext';
            }
        }
    }
}

class PlatalBananaPage extends BananaPage
{
    protected $handler;
    protected $base;

    public function __construct()
    {
        global $platal;
        Banana::$withtabs = false;
        $this->handler = 'BananaHandler';
        $this->base    = $platal->pl_self(0);
        parent::__construct();
    }

    protected function prepare()
    {
        $tpl = parent::prepare();
        global $wiz;
        $wiz = new PlWizard('Banana', PlPage::getCoreTpl('plwizard.tpl'), true, false);
        foreach ($this->pages as $name=>&$mpage) {
            $wiz->addPage($this->handler, $mpage['text'], $name);
        }
        $wiz->apply(Platal::page(), $this->base, $this->page);
        return $tpl;
    }
}

class BananaHandler
{
    public function __construct(PlWizard $wiz)
    {
    }

    public function template()
    {
        return 'banana/index.tpl';
    }

    public function prepare(PlPage $page, $id)
    {
    }

    public function success() { }

    public function process(&$success)
    {
        return PlWizard::CURRENT_PAGE;
    }
}

function run_banana($page, $class, array $args)
{
    $user = S::user();
    $banana = new $class($user, $args);
    $page->assign('banana', $banana->run());
    $page->addCssInline($banana->css());
    $page->addCssLink('banana.css');
    $rss = $banana->feed();
    if ($rss) {
        if (Banana::$group) {
            $page->setRssLink('Banana :: ' . Banana::$group, $rss);
        } else {
            $page->setRssLink('Banana :: Abonnements', $rss);
        }
    }
    $bt = $banana->backtrace();
    if ($bt) {
        new PlBacktrace(Banana::$protocole->name(), $banana->backtrace(), 'response', 'time');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

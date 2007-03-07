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

require_once 'banana/banana.inc.php';
require_once 'banana/hooks.inc.php';

function hook_checkcancel($_headers)
{
    return ($_headers['x-org-id'] == S::v('forlife') or S::has_perms());
}

function hook_makeLink($params)
{
    global $platal, $globals;
    $base = $globals->baseurl . '/' . $platal->ns . 'lists/moderate/' . ModerationBanana::$listname . '?';
    $get = '';
    foreach ($params as $key=>$value) {
        if ($key == 'artid') {
            $key = 'mid';
        }
        if ($key == 'group') {
            continue;
        }
        if ($key == 'action' && $value != 'showext') {
            continue;
        }
        if (!empty($get)) {
            $get .= '&';
        }
        $get .= $key . '=' . $value;
    }
    return $base . $get;
}

class ModerationBanana extends Banana
{
    static public $listname;
    static public $domain;
    static public $client;

    function __construct($forlife, $params = null)
    {
        global $globals;
        ModerationBanana::$client = $params['client'];
        ModerationBanana::$listname = $params['listname'];
        ModerationBanana::$domain = isset($params['domain']) ? $params['domain'] : $globals->mail->domain;
        $params['group'] = ModerationBanana::$listname . '@' . ModerationBanana::$domain;
        Banana::$spool_root = $globals->banana->spool_root;
        Banana::$spool_boxlist = false;
        Banana::$msgshow_withthread = false;
        Banana::$withtabs      = false;
        Banana::$msgshow_externalimages = false;
        Banana::$msgshow_mimeparts[] = 'source';
        Banana::$feed_active = false;
        array_push(Banana::$msgparse_headers, 'x-org-id', 'x-org-mail');
        parent::__construct($params, 'MLInterface', 'ModerationPage');
    }
}

require_once('banana/page.inc.php');

class ModerationPage extends BananaPage
{
    protected function prepare()
    {
        $this->killPage('subscribe');
        $this->killPage('forums');
        $this->assign('noactions', true);
        return parent::prepare();
    }

    public function trig($msg)
    {
        global $page;
        if ($page) {
            $page->trig($msg);
        }
        return true;
    }
}

require_once('banana/protocoleinterface.inc.php');
require_once('banana/message.inc.php');

class BananaMLInterface implements BananaProtocoleInterface
{
    private $infos; //(list, addr, host, desc, info, diff, ins, priv, sub, own, nbsub)
    private $helds; //(id, sender, size, subj, date)

    public function __construct()
    {
        $this->infos = ModerationBanana::$client->get_members(ModerationBanana::$listname); 
        $this->infos = $this->infos[0];
        
        $mods = ModerationBanana::$client->get_pending_ops(ModerationBanana::$listname);
        $this->helds = $mods[1];
    }
    
    public function isValid()
    {
        return !is_null(ModerationBanana::$client);
    }
    
    public function lastErrNo()
    {
        return 0;
    }
    
    public function lastError()
    {
        return null;
    }

    public function getDescription()
    {
        return $this->infos['desc'];
    }

    public function getBoxList($mode = Banana::BOXES_ALL, $since = 0, $withstats = false)
    {
        return array(Banana::$group => Array(
                        'desc' => $this->infos['desc'],
                        'msgnum' => count($this->helds),
                        'unread' => count($this->helds)));
    }

    public function &getMessage($id)
    {
        $message = null;
        $msg = ModerationBanana::$client->get_pending_mail(ModerationBanana::$listname, $id, 1);
        if ($msg) {
            $message = new BananaMessage(html_entity_decode($msg));
        }
        return $message;
    }

    public function getMessageSource($id)
    {
        return ModerationBanana::$client->get_pending_mail(ModerationBanana::$listname, $id, 1);
    }

    public function getIndexes()
    {
        $ids = array();
        foreach ($this->helds as &$desc) {
            $ids[] = intval($desc['id']);
        }
        sort($ids);
        return array(count($ids), min($ids), max($ids));
    }

    public function &getMessageHeaders($firstid, $lastid, array $msg_headers = array())
    {
        $conv = array('from' => 'sender', 'subject' => 'subj', 'date' => 'stamp', 'message-id' => 'id');
        $headers = array();
        foreach ($msg_headers as $hdr) {
            $hdr = strtolower($hdr);
            $mlhdr = isset($conv[$hdr]) ? $conv[$hdr] : $hdr;
            foreach ($this->helds as &$desc) {
                $id = intval($desc['id']);
                if (!isset($headers[$id])) {
                    $headers[$id] = array();
                }
                if ($mlhdr == 'id') {
                    $headers[$id][$hdr] = $desc['stamp'] . '$' . $desc['id'] . '@' . Banana::$group;
                } else {
                    $headers[$id][$hdr] = isset($desc[$mlhdr]) ? banana_html_entity_decode($desc[$mlhdr]) : null;
                }
            }
        }
        return $headers;
    }

    public function updateSpool(array &$messages) { }

    public function getNewIndexes($since)
    {
        $ids = array();
        foreach ($this->helds as &$desc) {
            if ($desc['stamp'] > $since) {
                $ids[] = intval($desc['id']);
            }
        }
        sort($ids);
        return $ids;
    }

    public function canSend()
    {
        return false;
    }

    public function canCancel()
    {
        return false;
    }

    public function requestedHeaders()
    {
        return array();
    }

    public function send(BananaMessage &$message)
    {
        return true;
    }

    public function cancel(BananaMessage &$message)
    {
        return true;
    }

    public function name()
    {
        return 'MLModeration';
    }

    public function filename()
    {
        return ModerationBanana::$domain . '_' . ModerationBanana::$listname;
    }

    public function backtrace()
    {
        return null;
    }
}

// vim:set et sw=4 sts=4 ts=4 enc=utf-8:
?>

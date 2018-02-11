<?php
/*
 * Copyright (C) 2003-2018 Polytechnique.org
 * http://opensource.polytechnique.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class PlatalLogger extends PlLogger
{
    /** user id */
    public $uid;
    /** id of the session */
    private $session;
    /** list of available actions */
    private $actions;

    public $ip;
    public $host;
    public $proxy_ip;
    public $proxy_host;

    /** The constructor, creates a new entry in the sessions table
     *
     * @param $uid the id of the logged user
     * @param $suid the id of the administrator who has just su'd to the user
     * @return VOID
     */
    public function __construct($uid, $suid = 0)
    {
        // write the session entry
        $this->uid     = $uid;
        $this->session = $this->writeSession($uid, $suid);

        // retrieve available actions
        $this->actions = XDB::fetchAllAssoc('text', 'SELECT  id, text
                                                       FROM  log_actions');
    }

    /** Creates a new session entry in database and return its ID.
     *
     * @param $uid the id of the logged user
     * @param $suid the id of the administrator who has just su'd to the user
     * @return session the session id
     */
    private function writeSession($uid, $suid = null)
    {
        $ip      = $_SERVER['REMOTE_ADDR'];
        $host    = strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR']));
        $browser = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

        @list($forward_ip,) = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $forward_host = $forward_ip;
        if ($forward_host) {
            $forward_host = strtolower(gethostbyaddr($forward_host));
        }
        $proxy = '';
        if ($forward_ip || @$_SERVER['HTTP_VIA']) {
            $proxy = 'proxy';
        }

        $uid = ($uid == 0) ? null : $uid;
        $suid = ($suid == 0) ? null : $suid;
        XDB::execute("INSERT INTO  log_sessions
                              SET  uid={?}, host={?}, ip={?}, forward_ip={?}, forward_host={?}, browser={?}, suid={?}, flags={?}",
                     $uid, $host, ip_to_uint($ip), ip_to_uint($forward_ip), $forward_host, $browser, $suid, $proxy);
        if ($forward_ip) {
            $this->proxy_ip = $ip;
            $this->proxy_host = $host;
            $this->ip = $forward_ip;
            $this->host = $forward_host;
        } else {
            $this->ip = $ip;
            $this->host = $host;
        }

        return XDB::insertId();
    }

    public function saveLastSession() {
        XDB::execute('REPLACE INTO  log_last_sessions (uid, id)
                            VALUES  ({?}, {?})',
                     $this->uid, $this->session);
    }

    public function isValid($uid) {
        return $uid == $this->uid;
    }

    /** Logs an action and its related data.
     *
     * @param $action le type d'action
     * @param $data les données (id de liste, etc.)
     * @return VOID
     */
    public function log($action, $data = null)
    {
        if (isset($this->actions[$action])) {
            XDB::execute("INSERT INTO  log_events
                                  SET  session={?}, action={?}, data={?}",
                         $this->session, $this->actions[$action], $data);
        } else {
            trigger_error("PlLogger: unknown action, $action", E_USER_WARNING);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

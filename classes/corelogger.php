<?php
/*
 * Copyright (C) 2003-2004 Polytechnique.org
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


class CoreLogger {
    /** user id */
    var $uid;
    /** id of the session */
    var $session;
    /** list of available actions */
    var $actions;

    /** The constructor, creates a new entry in the sessions table
     *
     * @param $uid the id of the logged user
     * @param $suid the id of the administrator who has just su'd to the user
     * @return VOID
     */
    function CoreLogger($uid, $suid='') {
        // write the session entry
        $this->uid     = $uid;
        $this->session = $this->writeSession($uid, $suid);

        // retrieve available actions
        $res = XDB::iterRow("SELECT id, text FROM logger.actions");

        while (list($action_id, $action_text) = $res->next()) {
            $this->actions[$action_text] = $action_id;
        }
    }

    /** Creates a new session entry in database and return its ID.
     * 
     * @param $uid the id of the logged user
     * @param $suid the id of the administrator who has just su'd to the user
     * @return session the session id
     */
    function writeSession($uid, $suid = null)
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

        XDB::execute("INSERT INTO logger.sessions
                     SET uid={?}, host={?}, ip={?}, forward_ip={?}, forward_host={?}, browser={?}, suid={?}, flags={?}",
                     $uid, $host, $ip, $forward_ip, $forward_host, $browser, $suid, $proxy);

        return XDB::insertId();
    }


    /** Logs an action and its related data.
     *
     * @param $action le type d'action
     * @param $data les données (id de liste, etc.)
     * @return VOID
     */
    function log($action, $data = null) {
        if (isset($this->actions[$action])) {
            XDB::execute("INSERT INTO logger.events
                         SET session={?}, action={?}, data={?}",
                         $this->session, $this->actions[$action], $data);
        } else {
            echo "unknown action : $action<br />";
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

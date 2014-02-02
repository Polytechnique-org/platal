<?php
/*
 * Copyright (C) 2003-2011 Polytechnique.org
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

abstract class PlLogger
{
    /** The constructor, creates a new entry in the sessions table
     *
     * @param $uid the id of the logged user
     * @param $suid the id of the administrator who has just su'd to the user
     * @return VOID
     */
    abstract public function __construct($uid, $suid = 0);

    /** Logs an action and its related data.
     *
     * @param $action le type d'action
     * @param $data les données (id de liste, etc.)
     * @return VOID
     */
    abstract public function log($action, $data = null);

    /** Check validity of the logger.
     *
     * @param $uid the uid of the current session.
     * @return TRUE if the logger can still be used.
     */
    abstract public function isValid($uid);

    /** Return a dummy logger.
     */
    public static function dummy($uid, $suid = 0) {
        return new DummyLogger($uid, $suid);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

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

class MMList extends XmlrpcClient
{
    public function __construct($user, $pass = null, $fqdn = null)
    {
        global $globals;
        if ($user instanceof PlUser) {
            $fqdn = $pass;
            $uid  = $user->id();
            $pass = $user->password();
        } else {
            $uid = $user;
        }

        $dom = is_null($fqdn) ? $globals->mail->domain : $fqdn;
        $url = "http://$uid:$pass@{$globals->lists->rpchost}:{$globals->lists->rpcport}/$dom";
        parent::__construct($url);
        if ($globals->debug & DEBUG_BT) {
            $this->bt = new PlBacktrace('MMList');
        }
    }

    /**
     * Replace email in all lists where user has subscribe
     * @param $old_email old email address used in mailing lits
     * @param $new_email new email to use in place of the old one
     * @return number of mailing lists changed
     */
    public function replace_email_in_all($old_email, $new_email) {
        $all_lists = $this->get_lists($old_email);
        if (!$all_lists) {
            return 0;
        }
        $changed_lists = 0;
        foreach ($all_lists as $list) {
            if ($list->sub) {
                $this->replace_email($list->list, $old_email, $new_email);
                $changed_lists++;
            }
        }
        return $changed_lists;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

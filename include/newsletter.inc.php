<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
 ***************************************************************************
        $Id: newsletter.inc.php,v 1.2 2004-10-15 12:57:10 x2000habouzit Exp $
 ***************************************************************************/

class NewsLetter {
    function NewsLetter() { }
}

function get_nl_list() {
    global $globals;
    $res = $globals->db->query("SELECT id,date,titre FROM newsletter ORDER BY date DESC");
    $ans = Array();
    while($tmp = mysql_fetch_assoc($res)) $ans[] = $tmp;
    mysql_free_result($res);
    return $ans;
}

function get_nl_state() {
    global $globals;
    $res = $globals->db->query("SELECT COUNT(*)>0 FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
    list($b) = mysql_fetch_row($res);
    mysql_free_result($res);
    return $b;
}
 
function unsubscribe_nl() {
    global $globals;
    $globals->db->query("DELETE FROM newsletter_ins WHERE user_id={$_SESSION['uid']}");
}
 
function subscribe_nl() {
    global $globals;
    $globals->db->query("INSERT INTO newsletter_ins (user_id) VALUES ({$_SESSION['uid']})");
}
 
?>

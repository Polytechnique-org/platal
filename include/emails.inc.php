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
 ***************************************************************************/

require_once("xorg.misc.inc.php");

// {{{ defines

define("SUCCESS", 1);
define("ERROR_INACTIVE_REDIRECTION", 2);
define("ERROR_INVALID_EMAIL", 3);
define("ERROR_LOOP_EMAIL", 4);

// }}}
// {{{ function fix_bestalias()

function fix_bestalias($uid)
{
    global $globals;
    $res = $globals->xdb->query("SELECT COUNT(*) FROM aliases WHERE id={?} AND FIND_IN_SET('bestalias',flags) AND type!='homonyme'", $uid);
    if ($n = $res->fetchOneCell()) {
        return;
    }
    $globals->xdb->execute("UPDATE  aliases
                               SET  flags=CONCAT(flags,',','bestalias')
			     WHERE  id={?} AND type!='homonyme'
		          ORDER BY  !FIND_IN_SET('usage',flags),alias LIKE '%.%', LENGTH(alias)
		             LIMIT  1", $uid);
}

// }}}
// {{{ function valide_email()

function valide_email($str)
{
   $em = trim(rtrim($str));
   $em = str_replace('<', '', $em);
   $em = str_replace('>', '', $em);
   list($ident, $dom) = explode('@', $em);
   if ($dom == $globals->mail->domain or $dom == $globals->mail->domain2) {
       list($ident1) = explode('_', $ident);
       list($ident) = explode('+', $ident1);
   }
   return $ident . '@' . $dom;
}

// }}}
// {{{ class Bogo

class Bogo
{
    // {{{ properties
    
    var $state;
    var $_states = Array('let_spams', 'tag_spams', 'drop_spams');

    // }}}
    // {{{ constructor
    
    function Bogo($uid)
    {
	global $globals;
	$res = $globals->xdb->query('SELECT email FROM emails WHERE uid={?} AND flags="filter"', $uid);
	if ($res->numRows()) {
            $this->state = $res->fetchOneCell();
	} else {
	    $this->state = 'tag_spams';
	    $res = $globals->xdb->query("INSERT INTO emails (uid,email,rewrite,panne,flags)
					      VALUES ({?},'tag_spams','','0000-00-00','filter')", $uid);
	}
    }

    // }}}
    // {{{ function change()

    function change($uid, $state)
    {
	global $globals;
	$this->state = is_int($state) ? $this->_states[$state] : $state;
	$globals->xdb->execute('UPDATE emails SET email={?} WHERE uid={?} AND flags = "filter"', $this->state, $uid);
    }

    // }}}
    // {{{ function level()

    function level()
    { return array_search($this->state, $this->_states); }

    // }}}
}

// }}}
// {{{ class Email

class Email
{
    // {{{ properties
    
    var $email;
    var $active;
    var $rewrite;
    var $panne;

    // }}}
    // {{{ constructor

    function Email($row)
    {
        list($this->email, $this->active, $this->rewrite, $this->panne) = $row;
    }

    // }}}
    // {{{ function activate()

    function activate($uid)
    {
        global $globals;
        if (!$this->active) {
            $globals->xdb->execute("UPDATE  emails SET flags = 'active'
                                     WHERE  uid={?} AND email={?}", $uid, $this->email);
	    $_SESSION['log']->log("email_on", $this->email.($uid!=Session::getInt('uid') ? "(admin on $uid)" : ""));
            $this->active = true;
        }
    }

    // }}}
    // {{{ function deactivate()

    function deactivate($uid)
    {
        global $globals;
        if ($this->active) {
            $globals->xdb->execute("UPDATE  emails SET flags =''
				     WHERE  uid={?} AND email={?}", $uid, $this->email);
	    $_SESSION['log']->log("email_off",$this->email.($uid!=Session::getInt('uid') ? "(admin on $uid)" : "") );
            $this->active = false;
        }
    }
    
    // }}}
    // {{{ function rewrite()

    function rewrite($rew, $uid)
    {
        global $globals;
	if ($this->rewrite == $rew) {
            return;
        }
	$globals->xdb->execute('UPDATE emails SET rewrite={?} WHERE uid={?} AND email={?}', $rew, $uid, $this->email);
	$this->rewrite = $rew;
	return;
    }

    // }}}
}

// }}}
// {{{ class Redirect

class Redirect
{
    // {{{ properties
    
    var $flag_active = 'active';
    var $emails;
    var $bogo;
    var $uid;

    // }}}
    // {{{ function Redirect()

    function Redirect($_uid)
    {
        global $globals;
	$this->uid=$_uid;
        $res = $globals->xdb->iterRow("
	    SELECT email, flags='active', rewrite, panne
	      FROM emails WHERE uid = {?} AND flags != 'filter'", $_uid);
	$this->emails=Array();
        while ($row = $res->next()) {
	    $this->emails[] = new Email($row);
        }
	$this->bogo = new Bogo($_uid);
    }

    // }}}
    // {{{ function other_active()

    function other_active($email)
    {
        foreach ($this->emails as $mail) {
            if ($mail->email!=$email && $mail->active) {
                return true;
            }
        }
        return false;
    }

    // }}}
    // {{{ function delete_email()

    function delete_email($email)
    {
        global $globals;
        if (!$this->other_active($email)) {
            return ERROR_INACTIVE_REDIRECTION;
        }
        $globals->xdb->execute('DELETE FROM emails WHERE uid={?} AND email={?}', $this->uid, $email);
        $_SESSION['log']->log('email_del',$email.($this->uid!=Session::getInt('uid') ? " (admin on {$this->uid})" : ""));
	foreach ($this->emails as $i=>$mail) {
	    if ($email==$mail->email) {
                unset($this->emails[$i]);
            }
	}
        return SUCCESS;
    }

    // }}}
    // {{{ function add_email()
    
    function add_email($email)
    {
        global $globals;
        $email_stripped = strtolower(trim($email));
        if (!isvalid_email($email_stripped)) {
            return ERROR_INVALID_EMAIL;
        }
        if (!isvalid_email_redirection($email_stripped)) {
            return ERROR_LOOP_EMAIL;
        }
        $globals->xdb->execute('REPLACE INTO emails (uid,email,flags) VALUES({?},{?},"active")', $this->uid, $email);
	if ($logger = Session::getMixed('log', null)) { // may be absent --> step4.php
	    $logger->log('email_add',$email.($this->uid!=Session::getInt('uid') ? " (admin on {$this->uid})" : ""));
        }
	foreach ($this->emails as $mail) {
	    if ($mail->email == $email_stripped) {
                return SUCCESS;
            }
	}
        $this->emails[] = new Email(array($email,1,'','0000-00-00'));
        return SUCCESS;
    }

    // }}}
    // {{{ function modify_email()

    function modify_email($emails_actifs,$emails_rewrite)
    {
        global $globals;
	foreach ($this->emails as $i=>$mail) {
            if (in_array($mail->email,$emails_actifs)) {
                $this->emails[$i]->activate($this->uid);
	    } else {
                $this->emails[$i]->deactivate($this->uid);
	    }
	    $this->emails[$i]->rewrite($emails_rewrite[$mail->email], $this->uid);
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

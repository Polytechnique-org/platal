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
        $Id: email.classes.inc.php,v 1.5 2004-09-04 20:14:30 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.misc.inc.php");
define("SUCCESS", 1);
define("ERROR_INACTIVE_REDIRECTION", 2);
define("ERROR_INVALID_EMAIL", 3);
define("ERROR_LOOP_EMAIL", 4);

define("MTIC_DOMAINS", "/etc/postfix/forward-domaines.conf");

function check_mtic($email) {
    list($local,$domain) = explode("@",$email);
    // lecture du fichier de configuration
    $tab = file(MTIC_DOMAINS);
    foreach ($tab as $ligne) {
	if ($ligne{0} == '#') continue;           // on saute les commentaires
	// pour chaque ligne, on regarde si la première partie qui correspond au domaine du destinataire
	// matche le domaine de l'email donnée
	list($regexp) = explode(':',$ligne);
	if (eregi($regexp,$domain)) return true;  // c'est le cas, on revoie true
    }
    return false;
}

class Email {
    var $email;
    var $active;
    var $rewrite;
    var $mtic;
    var $panne;

    function Email($row) {
        list($this->email,$this->active,$this->rewrite,$this->mtic,$this->panne)
        = $row;
    }

    function activate($uid) {
        global $globals;
        if (!$this->active) {
            $globals->db->query("UPDATE emails
	                            SET flags = CONCAT_WS(',',flags,'active')
				  WHERE uid=$uid AND email='{$this->email}'");
	    $_SESSION['log']->log("email_on",$this->email);
            $this->active = true;
        }
    }

    function deactivate($uid) {
        global $globals;
        if ($this->active) {
	    $flags = $this->mtic ? 'mtic' : '';
            $globals->db->query("UPDATE emails
				    SET flags ='$flags'
				  WHERE uid=$uid AND email='{$this->email}'");
	    $_SESSION['log']->log("email_off",$this->email);
            $this->active = false;
        }
    }

    function rewrite($rew,$uid) {
        global $globals;
	if($this->rewrite == $rew) return;
	$globals->db->query("UPDATE emails SET rewrite='$rew' WHERE uid=$uid AND email='{$this->email}'");
	$this->rewrite = $rew;
	return;
    }
}

class Redirect {
    var $flag_active = 'active';
    var $emails;
    var $uid;

    function Redirect($_uid) {
        global $globals;
	$this->uid=$_uid;
        $result = $globals->db->query("
	    SELECT email, FIND_IN_SET('active',flags), rewrite, FIND_IN_SET('mtic',flags),panne
	      FROM emails WHERE uid = $_uid AND NOT FIND_IN_SET('filter',flags)");
        while ($row = mysql_fetch_row($result)) {
	    $this->emails[] = new Email($row);
        }
    }

    function other_active($email) {
        foreach($this->emails as $mail)
            if ($mail->email!=$email && $mail->active)
                return true;
        return false;
    }

    function delete_email($email) {
        global $globals;
        if (!$this->other_active($email))
            return ERROR_INACTIVE_REDIRECTION;
        $globals->db->query("DELETE FROM emails WHERE uid={$this->uid} AND email='$email'");
        $_SESSION['log']->log("email_del",$email);
	foreach($this->emails as $i=>$mail) {
	    if($email==$mail->email) unset($this->emails[$i]);
	}
        return SUCCESS;
    }

    function add_email($email) {
        global $globals;
        $email_stripped = strtolower(stripslashes($email));
        if (!isvalid_email($email_stripped))
            return ERROR_INVALID_EMAIL;
        if (!isvalid_email_redirection($email_stripped))
            return ERROR_LOOP_EMAIL;
        //construction des flags
        $flags = 'active';
        // on verifie si le domaine de email ou email est un domaine interdisant
        // les adresses internes depuis l'exterieur
        $mtic = 0;
        if (check_mtic($email_stripped)) {
            $flags .= ',mtic';
            global $page;
            $page->assign('mtic',1);
            $mtic = 1;
        }
        $globals->db->query("REPLACE INTO emails (uid,email,flags) VALUES({$this->uid},'$email','$flags')");
        $_SESSION['log']->log("email_add",$email);
	foreach($this->emails as $mail) {
	    if($mail->email == $email_stripped) return SUCCESS;
	}
        $this->emails[] = new Email(array($email,1,'',$mtic,'0000-00-00'));
        return SUCCESS;
    }

    function modify_email($emails_actifs,$emails_rewrite) {
        global $globals;
	foreach($this->emails as $i=>$mail) {
            if(in_array($mail->email,$emails_actifs)) {
                $this->emails[$i]->activate($this->uid);
	    } else {
                $this->emails[$i]->deactivate($this->uid);
	    }
	    $this->emails[$i]->rewrite($emails_rewrite[$mail->email], $this->uid);
        }
    }
}
?>

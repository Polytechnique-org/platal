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
        $Id: email.classes.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

require("xorg.misc.inc.php");
require("mtic.inc.php");
define("SUCCESS", 1);
define("ERROR_INACTIVE_REDIRECTION", 2);
define("ERROR_INVALID_EMAIL", 3);
define("ERROR_LOOP_EMAIL", 4);
define("ERROR_DUPLICATE_EMAIL", 5);

class Email {
    var $flag_active = 'active';
    var $flag_rewrite = 'rewrite';
    var $flag_m4x = 'm4x';
    var $num;
    var $email;
    var $active;
    var $rewrite;
    var $m4x;
    var $mtic;

    function Email($row) {
        list($this->num,$this->email,$this->active,$this->filtre,$this->rewrite,$this->m4x,$this->mtic)
        = $row;
    }

    function set_filtre_antispam() {
        $this->flag_active = 'filtre';
        $this->active = $this->filtre;
    }

    function set($flag) {
        global $globals;
        if (!$this->{$flag}) {
            $globals->db->query("update emails set flags = CONCAT_WS(',',flags,'".$this->{'flag_'.$flag}.
            "') where uid={$_SESSION['uid']} and num=".$this->num);
            if ($flag=='active')
                $_SESSION['log']->log("email_on",$this->email);
            $this->{$flag} = true;
        }
    }

    function deset($flag) {
        global $globals;
        if ($this->{$flag}) {
            $globals->db->query("update emails set flags = flags & 
            ~(1 << (FIND_IN_SET('".$this->{'flag_'.$flag}."',flags)-1)) 
            where uid={$_SESSION['uid']} and num=".$this->num);
            if ($flag=='active')
                $_SESSION['log']->log("email_off",$this->email);
            $this->{$flag} = false;
        }
    }
}

class Redirect {
    var $flag_active = 'active';
    var $emails;

    function Redirect() {
        global $globals;
        $result = $globals->db->query("select num, email,
        FIND_IN_SET('active',flags),FIND_IN_SET('filtre',flags),
        FIND_IN_SET('rewrite',flags), FIND_IN_SET('m4x',flags), FIND_IN_SET('mtic',flags) 
        from emails where uid = {$_SESSION['uid']}");
        while ($row = mysql_fetch_row($result)) {
            $num = $row[0];
            if ($num!=0)
                $this->emails[$num] = new Email($row);
            else
                $this->flag_active = 'filtre';
        }
        if ($this->flag_active == 'filtre')
            foreach($this->emails as $num=>$mail)
                $this->emails[$num]->set_filtre_antispam();
    }

    function other_active($num) {
        foreach($this->emails as $i=>$mail)
            if ($i!=$num && $this->emails[$i]->active)
                return true;
        return false;
    }

    function duplicate($email) {
        foreach($this->emails as $num=>$mail)
            if ($this->emails[$num]->email==$email)
                return true;
        return false;
    }

    function freenum() {
        $anc = 0;
        foreach ($this->emails as $num=>$mail) {
            if ($anc<$num-1)
                return $anc+1;
            $anc = $num;
        }
        return $anc+1;
    }

    function delete_email($num) {
        global $globals;
        if (!$this->other_active($num))
            return ERROR_INACTIVE_REDIRECTION;
        $globals->db->query("delete from emails where uid={$_SESSION['uid']} and num='$num'");
        $_SESSION['log']->log("email_del",$this->emails[$num]->email);
        unset($this->emails[$num]);
        return SUCCESS;
    }

    function add_email($email) {
        global $globals;
        $email_stripped = stripslashes($email);
        if (!isvalid_email($email_stripped))
            return ERROR_INVALID_EMAIL;
        if (!isvalid_email_redirection($email_stripped))
            return ERROR_LOOP_EMAIL;
        if ($this->duplicate($email))
            return ERROR_DUPLICATE_EMAIL;
        //construction des flags
        $flags = $this->flag_active.',rewrite';
        // on verifie si le domaine de email ou email est un domaine interdisant
        // les adresses internes depuis l'exterieur
        $mtic = 0;
        if (check_mtic($email_stripped)) {
            $flags .= ',mtic';
            global $page;
            $page->assign('mtic',1);
            $mtic = 1;
        }
        $newnum = $this->freenum();
        $globals->db->query("insert into emails (uid,num,email,flags) VALUES({$_SESSION['uid']},'$newnum','$email','$flags')");
        $_SESSION['log']->log("email_add",$email);
        $this->emails[$newnum] = new Email(array($newnum,$email,1,1,1,0,$mtic));
        return SUCCESS;
    }

    function modify_email($emails_actifs,$emails_rewrite) {
        global $globals;
        foreach($this->emails as $num=>$mail) {
            if ($emails_rewrite[$num] != 'no')
                $this->emails[$num]->set('rewrite');
            else
                $this->emails[$num]->deset('rewrite');
            if ($emails_rewrite[$num] == 'm4x')
                $this->emails[$num]->set('m4x');
            else
                $this->emails[$num]->deset('m4x');
            if(in_array($num,$emails_actifs))
                $this->emails[$num]->set('active');
            else
                $this->emails[$num]->deset('active');
        }
    }
}
?>

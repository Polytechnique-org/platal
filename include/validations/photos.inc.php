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
    $Id: photos.inc.php,v 1.2 2004-11-22 07:40:18 x2000habouzit Exp $
 ***************************************************************************/

// {{{ class PhotoReq

class PhotoReq extends Validate
{
    // {{{ properties
    
    var $mimetype;
    var $data;
    var $x;
    var $y;

    var $bestalias;
    var $prenom;
    var $nom;

    // }}}
    // {{{ constructor
   
    function PhotoReq($_uid, $_file, $_stamp=0)
    {
        global $erreur, $globals;

        $this->Validate($_uid, true, 'photo', $_stamp);
        $sql = $globals->db->query("
	    SELECT  a.alias, prenom, nom
	      FROM  auth_user_md5 AS u
        INNER JOIN  aliases       AS a ON ( a.id=u.user_id AND FIND_IN_SET('bestalias',a.flags) )
	     WHERE  user_id=".$this->uid);
        list($this->bestalias,$this->prenom,$this->nom) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        
        if (!file_exists($_file)) {
            $erreur = "Fichier inexistant";
            return false;
        }
        // calcul de la taille de l'image
        $image_infos = getimagesize($_file);
        if (empty($image_infos)) {
            $erreur = "Image invalide";
            return false;
        }
        list($this->x, $this->y, $this->mimetype) = $image_infos;
        // récupération du type de l'image
        switch ($this->mimetype) {
            case 1:
                $this->mimetype = "gif";
                break;
                
            case 2:
                $this->mimetype = "jpeg";
                break;
                
            case 3:
                $this->mimetype = "png";
                break;
                
            default:
                $erreur = "Type d'image invalide";
                return false;
        }
        // lecture du fichier
        if (!($size = filesize($_file)) or $size > SIZE_MAX) {
            $erreur = "Image trop grande (max 30ko)";
            return false;
        }
        $fd = fopen($_file, 'r');
        if (!$fd) return false;
        $this->data = fread($fd, SIZE_MAX);
        fclose($fd);

        unset($erreur);
    }
    
    // }}}
    // {{{ function get_unique_request()

    function get_unique_request($uid)
    {
        return parent::get_unique_request($uid,'photo');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.photos.tpl'; }

    // }}}
    // {{{ function handle_formu()
    
    function handle_formu ()
    {
        if (empty($_REQUEST['submit'])
                || ($_REQUEST['submit']!="Accepter" && $_REQUEST['submit']!="Refuser"))
        {
            return false;
        }
        
        require_once("xorg.mailer.inc.php");
        $mymail = new XOrgMailer('valid.photos.tpl');
        $mymail->assign('bestalias', $this->bestalias);

        if ($_REQUEST['submit']=="Accepter") {
            $mymail->assign('answer','yes');
            $this->commit();
        } else {
            $mymail->assign('answer','no');
        }
        
        $mymail->send();

        $this->clean();
        return "Mail envoyé";
    }

    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        
        $globals->db->query("REPLACE INTO  photo (uid, attachmime, attach, x, y)
                                   VALUES  ('{$this->uid}', '{$this->mimetype}', '"
                                            .addslashes($this->data)."', '{$this->x}', '{$this->y}')");
	require('notifs.inc.php');
	register_watch_op($this->uid,WATCH_FICHE);
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

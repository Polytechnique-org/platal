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

// {{{ class EvtReq

class EvtReq extends Validate
{
    // {{{ properties
    
    var $evtid;
    var $titre;
    var $texte;
    var $pmin;
    var $pmax;
    var $peremption;    
    var $comment;
    
    var $bestalias;
    var $promo;
    var $nom;
    var $prenom;

    // }}}
    // {{{ constructor

    function EvtReq($_evtid,$_titre,$_texte,$_pmin,$_pmax,$_peremption,
      $_comment,$_uid,$_stamp=0) {
        global $globals;
        $this->Validate($_uid, false, 'evts', $_stamp);
        $this->evtid      = $_evtid;
        $this->titre      = $_titre;
        $this->texte      = $_texte;
        $this->pmin       = $_pmin;
        $this->pmax       = $_pmax;
        $this->peremption = $_peremption;
        $this->comment    = $_comment;
        $res = $globals->xdb->query("
                SELECT  a.alias,promo,nom,prenom
                  FROM  auth_user_md5 AS u
            INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
                 WHERE  user_id={?}", $_uid);
        list($this->bestalias,$this->promo,$this->nom,$this->prenom) = $res->fetchOneRow();
    }

    // }}}
    // {{{ function get_unique_request()
    
    function get_unique_request($uid)
    {
        return false;  //non unique
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.evts.tpl'; }

    // }}}
    // {{{ function handle_formu()

    function handle_formu()
    {
        global $globals;
        if (Post::has('action')) {
            require_once("xorg.mailer.inc.php");
            $mymail = new XOrgMailer('valid.evts.tpl');
            $mymail->assign('bestalias',$this->bestalias);
            $mymail->assign('titre',$this->titre);

            $uid = Session::getInt('uid');

            if (Env::get('action') == "Valider") {
                $globals->xdb->execute(
                        "UPDATE  evenements
                            SET  creation_date = creation_date, validation_user_id = {?},
                                 validation_date = NULL, flags = CONCAT(flags,',valide')
                          WHERE  id={?}
                          LIMIT  1", $uid, $this->evtid);
                $mymail->assign('answer','yes');
                $mymail->send();
            } elseif (Env::get('action') == "Invalider") {
                $globals->xdb->execute(
                        "UPDATE  evenements
                            SET  creation_date = creation_date, validation_user_id = {?},
                                 validation_date = NULL, flags = REPLACE(flags,'valide','')
                          WHERE  id='{?}'
                          LIMIT 1", $uid, $this->evtid);
                $mymail->assign('answer', 'no');
                $mymail->send();
            } elseif (Env::get('action') == "Supprimer") {
                $globals->xdb->execute("DELETE FROM evenements WHERE id={?} LIMIT 1", $this->evtid);
            }
            
            $this->clean();
        }
        return "";
    }

    // }}}
    // {{{ function commit()

    function commit()
    { }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

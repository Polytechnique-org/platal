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

// {{{ class AliasReq

class AliasReq extends Validate
{
    // {{{ properties

    var $alias;
    var $raison;

    var $forlife;
    var $bestalias;
    var $prenom;
    var $nom;
    var $old='';

    // }}}
    // {{{ constructor

    function AliasReq ($_uid, $_alias, $_raison, $_stamp=0)
    {
        global $globals;
        $this->Validate($_uid, true, 'alias', $_stamp);
        $this->alias = $_alias;
        $this->raison = $_raison;

        $res = $globals->xdb->query("
                SELECT  l.alias,m.alias,prenom,nom
                  FROM  auth_user_md5    AS u
            INNER JOIN  aliases          AS l  ON (u.user_id=l.id AND l.type='a_vie')
            INNER JOIN  aliases          AS m  ON (u.user_id=m.id AND FIND_IN_SET('bestalias',m.flags))
                 WHERE  user_id={?}", $this->uid);
        list($this->forlife,$this->bestalias,$this->prenom,$this->nom) = $res->fetchOneRow();

        $res = $globals->xdb->query("
                SELECT  v.alias
                  FROM  virtual_redirect AS vr
            INNER JOIN  virtual          AS v  ON (v.vid=vr.vid AND v.alias LIKE '%@{$globals->mail->alias_dom}')
                 WHERE  vr.redirect={?} OR vr.redirect={?}",
                 "{$this->forlife}@{$globals->mail->domain}", "{$this->forlife}@{$globals->mail->domain2}");
        $this->old = $res->fetchOneCell();
        if (empty($this->old)) { unset($this->old); }
    }

    // }}}
    // {{{ function get_unique_request()

    function get_unique_request($uid)
    {
        return parent::get_unique_request($uid,'alias');
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.aliases.tpl'; }

    // }}}
    // {{{ function handle_formu()

    function handle_formu()
    {
        if (Env::get('submit') != "Accepter" && Env::get('submit') != "Refuser") {
            return false;
        }

        require_once("xorg.mailer.inc.php");
        $mymail = new XOrgMailer('valid.alias.tpl');
        $mymail->assign('alias', $this->alias);
        $mymail->assign('bestalias', $this->bestalias);

        if (Env::get('submit') == "Accepter") {
            $mymail->assign('answer', 'yes');
            $this->commit() ; 
        } else {
            $mymail->assign('answer', 'no');
            $mymail->assign('motif', stripslashes(Env::get('motif')));
        }
        $mymail->send();
        //Suppression de la demande
        $this->clean();
        return "Mail envoyé";
    }

    // }}}
    // {{{ function commit()

    function commit ()
    {
        global $globals;

        if ($this->old) {
            $globals->xdb->execute('UPDATE virtual SET alias={?} WHERE alias={?}',
                   $this->alias.'@'.$globals->mail->alias_dom, $this->old);
        } else {
            $globals->xdb->execute('INSERT INTO virtual SET alias={?},type="user"',
                    $this->alias.'@'.$globals->mail->alias_dom);
            $vid = mysql_insert_id();
            require_once('emails.inc.php');
            $dom = $globals->mail->shorter_domain();
            $globals->xdb->query('INSERT INTO virtual_redirect (vid,redirect) VALUES ({?}, {?})', $vid, $this->forlife.'@'.$dom);
        }
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

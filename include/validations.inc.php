<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

define('SIZE_MAX', 32768);

/**
 * Iterator class, that lists objects through the database
 */
class ValidateIterator extends XOrgDBIterator
{
    // {{{ constuctor

    function ValidateIterator ()
    {
        parent::XOrgDBIterator('SELECT data,stamp FROM requests ORDER BY stamp', MYSQL_NUM);
    }

    // }}}
    // {{{ function next()

    function next ()
    {
        if (list($result, $stamp) = parent::next()) {
            $result = unserialize($result);
            $result->stamp = $stamp;
            return($result);
        } else {
            return null;
        }
    }

    // }}}
}

/** classe "virtuelle" à dériver pour chaque nouvelle implémentation
 */
class Validate
{
    // {{{ properties
    
    var $uid;
    var $prenom;
    var $nom;
    var $promo;
    var $bestalias;
    var $forlife;

    var $stamp;
    var $unique;
    // enable the refuse button
    var $refuse = true;
    var $type;
    var $comments = Array();
    // the validations rules : comments for admins
    var $rules = "Mieux vaut laisser une demande de validation à un autre admin que de valider une requête illégale ou que de refuser une demande légitime";

    // }}}
    // {{{ constructor
    
    /** constructeur
     * @param       $_uid       user id
     * @param       $_unique    requête pouvant être multiple ou non
     * @param       $_type      type de la donnée comme dans le champ type de x4dat.requests
     */
    function Validate($_uid, $_unique, $_type)
    {
        $this->uid    = $_uid;
        $this->stamp  = date('YmdHis');
        $this->unique = $_unique;
        $this->type   = $_type;
        $res = XDB::query(
                "SELECT  u.prenom, u.nom, u.promo, a.alias, b.alias
                   FROM  auth_user_md5 AS u
             INNER JOIN  aliases       AS a ON ( u.user_id=a.id AND a.type='a_vie' )
             INNER JOIN  aliases       AS b ON ( u.user_id=b.id AND b.type!='homonyme' AND FIND_IN_SET('bestalias', b.flags) )
                  WHERE  u.user_id={?}", $_uid);
        list($this->prenom, $this->nom, $this->promo, $this->forlife, $this->bestalias) = $res->fetchOneRow();
    }
    
    // }}}
    // {{{ function submit()

    /** fonction à utiliser pour envoyer les données à la modération
     * cette fonction supprimme les doublons sur un couple ($user,$type) si $this->unique est vrai
     */
    function submit ()
    {
        if ($this->unique) {
            XDB::execute('DELETE FROM requests WHERE user_id={?} AND type={?}', $this->uid, $this->type);
        }
       
        $this->stamp = date('YmdHis');
        XDB::execute('INSERT INTO requests (user_id, type, data, stamp) VALUES ({?}, {?}, {?}, {?})',
                $this->uid, $this->type, $this, $this->stamp);

        return true;
    }

    // }}}
    // {{{ function update()

    function update ()
    {
        XDB::execute('UPDATE requests SET data={?}, stamp=stamp
                                 WHERE user_id={?} AND type={?} AND stamp={?}',
                                 $this, $this->uid, $this->type, $this->stamp);

        return true;
    }

    // }}}
    // {{{ function clean()
    
    /** fonction à utiliser pour nettoyer l'entrée de la requête dans la table requests
     * attention, tout est supprimé si c'est un unique
     */
    function clean ()
    {
        if ($this->unique) {
            return XDB::execute('DELETE FROM requests WHERE user_id={?} AND type={?}',
                    $this->uid, $this->type);
        } else {
            return XDB::execute('DELETE FROM requests WHERE user_id={?} AND type={?} AND stamp={?}',
                    $this->uid, $this->type, $this->stamp);
        }
    }

    // }}}
    // {{{ function handle_formu()
    
    /** fonction à réaliser en cas de valistion du formulaire
     */
    function handle_formu()
    {
        if (Env::has('delete')) {
            $this->clean();
            $this->trig('requete supprimée');
            return true;
        }

        // ajout d'un commentaire
        if (Env::has('hold') && Env::has('comm')) {
            $formid = Env::i('formid');
            foreach ($this->comments as $comment) {
                if ($comment[2] === $formid) {
                    return true;
                }
            }
            $this->comments[] = Array(S::v('bestalias'), Env::v('comm'), $formid);

            // envoi d'un mail à hotliners
            global $globals;
            require_once('diogenes/diogenes.hermes.inc.php');
            $mailer = new HermesMailer;
            $mailer->setSubject("Commentaires de validation {$this->type}");
            $mailer->setFrom("validation+{$this->type}@{$globals->mail->domain}");
            $mailer->addTo("hotliners@{$globals->mail->domain}");

            $body = "Validation {$this->type} pour {$this->prenom} {$this->nom}\n\n"
              . S::v('bestalias')." a ajouté le commentaire :\n\n" 
              . Env::v('comm')."\n\n"
              . "cf la discussion sur : ".$globals->baseurl."/admin/valider.php";

            $mailer->setTxtBody(wordwrap($body));
            $mailer->send();

            $this->update();
            $this->trig('commentaire ajouté');
            return true;
        }

        if (Env::has('accept')) {
            if ($this->commit()) {
                $this->sendmail(true);
                $this->clean();
                $this->trig('mail envoyé');
                return true;
            } else {
                $this->trig('erreur lors de la validation');
                return false;
            }
        }

        if (Env::has('refuse')) {
            if (Env::v('comm')) {
                $this->sendmail(false);
                $this->clean();
                $this->trig('mail envoyé');
                return true;
            } else {
                $this->trig('pas de motivation pour le refus !!!');
            }
        }

        return false;
    }

    // }}}
    // {{{ function sendmail

    function sendmail($isok)
    {
        global $globals;
        require_once('diogenes/diogenes.hermes.inc.php');
        $mailer = new HermesMailer;
        $mailer->setSubject($this->_mail_subj());
        $mailer->setFrom("validation+{$this->type}@{$globals->mail->domain}");
        $mailer->addTo("\"{$this->prenom} {$this->nom}\" <{$this->bestalias}@{$globals->mail->domain}>");
        $mailer->addCc("validation+{$this->type}@{$globals->mail->domain}");

        $body = "Cher(e) camarade,\n\n"
              . $this->_mail_body($isok)
              . (Env::has('comm') ? "\n\n".Env::v('comm') : '')
              . "\n\nCordialement,\nL'équipe Polytechnique.org\n";

        $mailer->setTxtBody(wordwrap($body));
        $mailer->send();
    }

    // }}}
    // {{{ function trig()
    
    function trig($msg) {
        global $page;
        $page->trig($msg);
    }
    
    // }}}
    // {{{ function get_request()

    /** fonction statique qui renvoie la requête de l'utilisateur d'id $uidau timestamp $t
     * @param   $uid    l'id de l'utilisateur concerné
     * @param   $type   le type de la requête
     * @param   $stamp  le timestamp de la requête
     *
     * XXX fonction "statique" XXX
     * à utiliser uniquement pour récupérer un objet dans la BD avec Validate::get_request(...)
     */
    function get_request($uid, $type, $stamp = -1)
    {
        if ($stamp == -1) {
            $res = XDB::query('SELECT data FROM requests WHERE user_id={?} and type={?}', $uid, $type);
        } else {
            $res = XDB::query("SELECT data, stamp FROM requests WHERE user_id={?} AND type={?} and stamp={?}", $uid, $type, $stamp);
        }
        if ($result = $res->fetchOneCell()) {
            $result = unserialize($result);
        } else {
            $result = false;
        }
        return($result);
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
    }
    
    // }}}
    // {{{ function _mail_subj

    function _mail_subj()
    {
    }
    
    // }}}
    // {{{ function commit()
    
    /** fonction à utiliser pour insérer les données dans x4dat
     * XXX la fonction est "virtuelle" XXX
     */
    function commit ()
    { }

    // }}}
    // {{{ function formu()
    
    /** nom du template qui contient le formulaire */
    function formu()
    { return null; }

    // }}}
    // {{{ function answers()

    /** automatic answers table for this type of validation */
    function answers() {
        static $answers_table;
        if (!isset($answers_table[$this->type])) {
            $r = XDB::query("SELECT id, title, answer FROM requests_answers WHERE category = {?}", $this->type);
            $answers_table[$this->type] = $r->fetchAllAssoc($r);
        }
        return $answers_table[$this->type];
    }

    // }}}
}

foreach (glob(dirname(__FILE__).'/validations/*.inc.php') as $file) {
    require_once($file);
}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker: */
?>

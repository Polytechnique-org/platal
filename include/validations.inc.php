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

// {{{ DEFINES

define('SIZE_MAX', 32768);

// }}}
// {{{ class ValidateIterator

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

// }}}
// {{{ class Validate

/** classe "virtuelle" à dériver pour chaque nouvelle implémentation
 * XXX attention, dans l'implémentation de la classe, il ne faut jamais faire confiance au timestamp
 * de l'objet qui sort du BLOB de la BD, on met donc systématiquement le champt $this->stamp depuis
 * le TIMESTAMP de la BD
 * Par contre, à la sortie de toute fonction il faut que le stamp soit valide !!! XXX
 */
class Validate
{
    // {{{ properties
    
    /** l'uid de la personne faisant la requête */
    var $uid;
    /** le time stamp de la requête */
    var $stamp;
    /** indique si la donnée est unique pour un utilisateur donné */
    var $unique;
    /** donne le type de l'objet (certes redonant, mais plus pratique) */
    var $type;

    // }}}
    // {{{ constructor
    
    /** constructeur
     * @param       $_uid       user id
     * @param       $_unique    requête pouvant être multiple ou non
     * @param       $_type      type de la donnée comme dans le champ type de x4dat.requests
     * @param       $_stamp     stamp de création, 0 si c'estun nouvel objet
     */
    function Validate($_uid, $_unique, $_type, $_stamp=0)
    {
        $this->uid = $_uid;
        $this->stamp = $_stamp;
        $this->unique = $_unique;
        $this->type = $_type;
    }
    
    // }}}
    // {{{ function get_unique_request
    
    /** fonction statique qui renvoie la requête dans le cas d'un objet unique de l'utilisateur d'id $uid
     * @param   $uid    l'id de l'utilisateur concerné
     * @param   $type   le type de la requête
     *
     * XXX fonction "statique" XXX
     * XXX à dériver XXX
     * à utiliser uniquement pour récupérer un objet <strong>unique</strong>
     */
    function get_unique_request($uid,$type)
    {
        global $globals;
        $res = $globals->xdb->query('SELECT data,stamp FROM requests WHERE user_id={?} and type={?}', $uid, $type);
        if (list($result, $stamp) = $res->fetchOneRow()) {
            $result = unserialize($result);
            // on ne fait <strong>jamais</strong> confiance au timestamp de l'objet,
            $result->stamp = $stamp;
            if (!$result->unique) { // on vérifie que c'est tout de même bien un objet unique
                $result = false;
            }
        } else {
            $result = false;
        }
        
        return $result;
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
    function get_request($uid, $type, $stamp)
    {
        global $globals;
        $res = $globals->xdb->query("SELECT data, stamp FROM requests WHERE user_id={?} AND type={?} and stamp={?}",
                $uid, $type, $stamp);
        if (list($result, $stamp) = $res->fetchOneRow()) {
            $result = unserialize($result);
            // on ne fait <strong>jamais</strong> confiance au timestamp de l'objet,
            $result->stamp = $stamp;
        } else {
            $result = false;
        }

        return($result);
    }

    // }}}
    // {{{ function submit()

    /** fonction à utiliser pour envoyer les données à la modération
     * cette fonction supprimme les doublons sur un couple ($user,$type) si $this->unique est vrai
     */
    function submit ()
    {
        global $globals;
        if ($this->unique) {
            $globals->xdb->execute('DELETE FROM requests WHERE user_id={?} AND type={?}', $this->uid, $this->type);
        }
       
        $globals->xdb->execute('INSERT INTO requests (user_id, type, data) VALUES ({?}, {?}, {?})',
                $this->uid, $this->type, $this);

        // au cas où l'objet est réutilisé après un commit, il faut mettre son stamp à jour
        $res = $globals->xdb->query('SELECT MAX(stamp) FROM requests WHERE user_id={?} AND type={?}', $this->uid, $this->type);
        $this->stamp = $res->fetchOneCell();
        return true;
    }

    // }}}
    // {{{ function clean()
    
    /** fonction à utiliser pour nettoyer l'entrée de la requête dans la table requests
     * attention, tout est supprimé si c'est un unique
     */
    function clean ()
    {
        global $globals;
        if ($this->unique) {
            return $globals->xdb->execute('DELETE FROM requests WHERE user_id={?} AND type={?}',
                    $this->uid, $this->type);
        } else {
            return $globals->xdb->execute('DELETE FROM requests WHERE user_id={?} AND type={?} AND stamp={?}',
                    $this->uid, $this->type, $this->stamp);
        }
    }

    // }}}
    // {{{ function formu()
    
    /** nom du template qui contient le formulaire */
    function formu()
    { return null; }

    // }}}
    // {{{ function handle_formu()
    
    /** fonction à réaliser en cas de valistion du formulaire
     * XXX la fonction est "virtuelle" XXX
     */
    function handle_formu()
    { }

    // }}}
    // {{{ function commit()
    
    /** fonction à utiliser pour insérer les données dans x4dat
     * XXX la fonction est "virtuelle" XXX
     */
    function commit ()
    { }

    // }}}
}

// }}}
// {{{ IMPLEMENTATIONS

require_once("validations/aliases.inc.php");
require_once("validations/epouses.inc.php");
require_once("validations/photos.inc.php");
require_once("validations/evts.inc.php");
require_once("validations/listes.inc.php");

// }}}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker: */
?>

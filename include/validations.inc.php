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
        $Id: validations.inc.php,v 1.14 2004-09-01 21:36:27 x2000habouzit Exp $
 ***************************************************************************/

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 textwidth=100:
 * $Id: validations.inc.php,v 1.14 2004-09-01 21:36:27 x2000habouzit Exp $
 *
 */

define('SIZE_MAX', 32768);

/** classe listant les objets dans la bd */
class ValidateIterator {
    /** variable interne qui conserve l'état en cours de la requête */
    var $sql;
    
    /** constructeur */
    function ValidateIterator () {
        global $globals;
        $this->sql = $globals->db->query("SELECT data,stamp FROM requests ORDER BY stamp");
    }

    /** renvoie l'objet suivant, ou false */
    function next () {
        if(list($result,$stamp) = mysql_fetch_row($this->sql)) {
            $result = unserialize($result);
            $result->stamp = $stamp;
            return($result);
        } else {
            mysql_free_result($this->sql);
            return(false);
        }
    }
}

/** classe "virtuelle" à dériver pour chaque nouvelle implémentation
 * XXX attention, dans l'implémentation de la classe, il ne faut jamais faire confiance au timestamp
 * de l'objet qui sort du BLOB de la BD, on met donc systématiquement le champt $this->stamp depuis
 * le TIMESTAMP de la BD
 * Par contre, à la sortie de toute fonction il faut que le stamp soit valide !!! XXX
 */
class Validate {
    /** l'uid de la personne faisant la requête */
    var $uid;
    /** le time stamp de la requête */
    var $stamp;
    /** indique si la donnée est unique pour un utilisateur donné */
    var $unique;
    /** donne le type de l'objet (certes redonant, mais plus pratique) */
    var $type;
    
    /** fonction statique qui renvoie la requête dans le cas d'un objet unique de l'utilisateur d'id $uid
     * @param   $uid    l'id de l'utilisateur concerné
     * @param   $type   le type de la requête
     *
     * XXX fonction "statique" XXX
     * XXX à dériver XXX
     * à utiliser uniquement pour récupérer un objet <strong>unique</strong>
     */
    function get_unique_request($uid,$type) {
        global $globals;
        $sql = $globals->db->query("SELECT data,stamp FROM requests WHERE user_id='$uid' and type='$type'");
        if(list($result,$stamp) = mysql_fetch_row($sql)) {
            $result = unserialize($result);
            // on ne fait <strong>jamais</strong> confiance au timestamp de l'objet,
            $result->stamp = $stamp;
            if(!$result->unique) // on vérifie que c'est tout de même bien un objet unique
                $result = false;
        } else
            $result = false;

        mysql_free_result($sql);
        return $result;
    }

    /** fonction statique qui renvoie la requête de l'utilisateur d'id $uidau timestamp $t
     * @param   $uid    l'id de l'utilisateur concerné
     * @param   $type   le type de la requête
     * @param   $stamp  le timestamp de la requête
     *
     * XXX fonction "statique" XXX
     * à utiliser uniquement pour récupérer un objet dans la BD avec Validate::get_request(...)
     */
    function get_request($uid, $type, $stamp) {
        global $globals;
        $sql = $globals->db->query("SELECT data,stamp"
            ." FROM requests"
            ." WHERE user_id='$uid' and type = '$type' and stamp='$stamp'");
        if(list($result,$stamp) = mysql_fetch_row($sql)) {
            $result = unserialize($result);
            // on ne fait <strong>jamais</strong> confiance au timestamp de l'objet,
            $result->stamp = $stamp;
        } else
            $result = false;

        mysql_free_result($sql);
        return($result);
    }

    /** constructeur
     * @param       $_uid       user id
     * @param       $_unique    requête pouvant être multiple ou non
     * @param       $_type      type de la donnée comme dans le champ type de x4dat.requests
     * @param       $_stamp     stamp de création, 0 si c'estun nouvel objet
     */
    function Validate($_uid, $_unique, $_type, $_stamp=0) {
        $this->uid = $_uid;
        $this->stamp = $_stamp;
        $this->unique = $_unique;
        $this->type = $_type;
    }
    
    /** fonction à utiliser pour envoyer les données à la modération
     * cette fonction supprimme les doublons sur un couple ($user,$type) si $this->unique est vrai
     */
    function submit () {
        global $globals;
        if($this->unique)
            $globals->db->query("DELETE FROM requests WHERE user_id='".$this->uid
                    .   "' AND type='".$this->type."'");
       
        $globals->db->query("INSERT INTO requests SET user_id='".$this->uid."', type='".$this->type
                .   "', data='".addslashes(serialize($this))."'");

        // au cas où l'objet est réutilisé après un commit, il faut mettre son stamp à jour
        $sql = $globals->db->query("SELECT MAX(stamp) FROM requests "
                .   "WHERE user_id='".$this->uid."' AND type='".$this->type."'");
        list($this->stamp) = mysql_fetch_row($sql);
        mysql_free_result($sql);
        return true;
    }
    
    /** fonction à utiliser pour nettoyer l'entrée de la requête dans la table requests
     * attention, tout est supprimé si c'est un unique
     */
    function clean () {
        global $globals;
        return $globals->db->query("DELETE FROM requests WHERE user_id='".$this->uid."' AND type='".$this->type."'"
                .($this->unique ? "" : " AND stamp='".$this->stamp."'"));
    }
    
    /** nom du template qui contient le formulaire */
    function formu() { return null; }
    /** fonction à réaliser en cas de valistion du formulaire
     * XXX la fonction est "virtuelle" XXX
     */
    function handle_formu () { }
    /** fonction à utiliser pour insérer les données dans x4dat
     * XXX la fonction est "virtuelle" XXX
     */
    function commit () { }
}

//***************************************************************************************
//
// IMPLEMENTATIONS
//
//***************************************************************************************

require("valid_aliases.inc.php");
require("valid_epouses.inc.php");
require("valid_photos.inc.php");
require("valid_emploi.inc.php");
require("valid_evts.inc.php");

?>

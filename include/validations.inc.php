<?php
/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 textwidth=100:
 * $Id: validations.inc.php,v 1.1 2004-01-26 22:29:02 x2000habouzit Exp $
 *
 */

define('SIZE_MAX', 32768);

/** classe listant les objets dans la bd */
class ValidateIterator {
    /** variable interne qui conserve l'état en cours de la requête */
    var $sql;
    
    /** constructeur */
    function ValidateIterator () {
        $this->sql = mysql_query("SELECT data,stamp FROM requests ORDER BY stamp");
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
     * à utiliser uniquement pour récupérer un objet <br>unique</br>
     */
    function get_unique_request($uid,$type) {
        $sql = mysql_query("SELECT data,stamp FROM requests WHERE user_id='$uid' and type='$type'");
        if(list($result,$stamp) = mysql_fetch_row($sql)) {
            $result = unserialize($result);
            // on ne fait <b>jamais</b> confiance au timestamp de l'objet,
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
        $sql = mysql_query("SELECT data,stamp"
            ." FROM requests"
            ." WHERE user_id='$uid' and type = '$type' and stamp='$stamp'");
        if(list($result,$stamp) = mysql_fetch_row($sql)) {
            $result = unserialize($result);
            // on ne fait <b>jamais</b> confiance au timestamp de l'objet,
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
        global $no_update_bd;
        if($no_update_bd) return false;
        mysql_query("LOCK requests"); // le lock est obligatoire pour récupérer le dernier stamp !
        
        if($this->unique)
            mysql_query("DELETE FROM requests WHERE user_id='".$this->uid
                    .   "' AND type='".$this->type."'");
       
        mysql_query("INSERT INTO requests SET user_id='".$this->uid."', type='".$this->type
                .   "', data='".addslashes(serialize($this))."'");

        // au cas où l'objet est réutilisé après un commit, il faut mettre son stamp à jour
        $sql = mysql_query("SELECT MAX(stamp) FROM requests "
                .   "WHERE user_id='".$this->uid."' AND type='".$this->type."'");
        list($this->stamp) = mysql_fetch_row($sql);
        mysql_free_result($sql);

        mysql_query("UNLOCK requests");
        return true;
    }
    
    /** fonction à utiliser pour nettoyer l'entrée de la requête dans la table requests
     * attention, tout est supprimé si c'est un unique
     */
    function clean () {
        global $no_update_bd;
        if($no_update_bd) return false;
        return mysql_query("DELETE FROM requests WHERE user_id='".$this->uid."' AND type='".$this->type."'"
                .($this->unique ? "" : " AND stamp='".$this->stamp."'"));
    }
    
    /** doit afficher le fomulaire de validation de la donnée
     * XXX la fonction est "virtuelle" XXX
     * XXX doit définir les variables $uid et $stamp en hidden XXX
     */
    function echo_formu() { }
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
require("valid_ml.inc.php");
require("valid_sondages.inc.php");
require("valid_emploi.inc.php");
require("valid_evts.inc.php");

?>

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
        $Id: search.classes.inc.php,v 1.21 2004-10-19 18:06:45 x2000bedo Exp $
 ***************************************************************************/

require_once("xorg.misc.inc.php");

/*
 * Variables globales pour l'affichage des résultats de la recherche
 */
$globals->search_result_fields = '
                u.epouse,u.date,u.web,
                ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
                ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
                e.entreprise, es.label AS secteur, ef.label AS fonction,
                n.text AS nat,
                adr.ville, gp.pays, gr.name AS region,';
$globals->search_result_where_statement = '
                LEFT JOIN  applis_ins     AS ai0 ON (u.user_id = ai0.uid AND ai0.ordre = 0)
                LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
                LEFT JOIN  applis_ins     AS ai1 ON (u.user_id = ai1.uid AND ai1.ordre = 1)
                LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
                LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = u.user_id)
                LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
                LEFT JOIN  emploi_naf     AS ef  ON (e.fonction = ef.id)
                LEFT JOIN  nationalites   AS n   ON (u.nationalite = n.id)
                LEFT JOIN  adresses       AS adr ON (u.user_id = adr.uid AND FIND_IN_SET(\'active\',adr.statut))
                LEFT JOIN  geoloc_pays    AS gp  ON (adr.pays = gp.a2)
                LEFT JOIN  geoloc_region  AS gr  ON (adr.pays = gr.a2 AND adr.region = gr.region)';

/** classe qui gère les erreurs dans les requêtes des utilisateurs finaux
 * passe le message d'erreur au template de page et exécute le template
 */
class ThrowError {
    /** constucteur
     * @param explain message explicatif de l'erreur de l'utilisateur
     */
    function ThrowError($explain) {
        global $page;
        $page->assign('error','Erreur : '.$explain);
        $page->run();
    }
}

/** classe de base représentant un champ de recherche
 * (correspond à un champ du formulaire mais peut être à plusieurs champs de la bdd)
 * interface étendue pour chaque type de champ particulier
 */
class SField {
    /** le nom du champ dans le formulaire HTML */
    var $fieldFormName;
    /** champs de la bdd correspondant à ce champ sous forme d'un tableau */
    var $fieldDbName;
    /** champ résultat dans la requête MySQL correspondant à ce champ
     * (alias utilisé pour la clause ORDER BY) */
    var $fieldResultName;
    /** valeur du champ instanciée par l'utilisateur */
    var $value;

    /** constructeur
     * (récupère la requête de l'utilisateur pour ce champ) */
    function SField($_fieldFormName,$_fieldDbName='',$_fieldResultName='') {
        $this->fieldFormName = $_fieldFormName;
        $this->fieldDbName = $_fieldDbName;
        $this->fieldResultName = $_fieldResultName;
        $this->get_request();
    }

    /** récupérer la requête de l'utilisateur 
     * on met une chaîne vide si le champ n'a pas été complété */
    function get_request() {
        $this->value =
        (isset($_REQUEST[$this->fieldFormName]))?trim($_REQUEST[$this->fieldFormName]):'';
    }

    /** récupérer la clause correspondant au champ dans la clause WHERE de la requête
     * on parcourt l'ensemble des champs de la bdd de $fieldDbName et on associe 
     * à chacun d'entre eux une clause spécifique
     * la clause totale et la disjonction de ces clauses spécifiques */
    function get_where_statement() {
        if ($this->value=='')
            return false;
        $res = implode(' OR ',array_filter(array_map(array($this,'get_single_where_statement'),$this->fieldDbName)));
        return ($res!='')?('('.$res.')'):'';
    }

    /** récupérer la clause correspondant au champ dans la clause ORDER BY de la requête
     * utilisé par exemple pour placer d'abord le nom égal à la requête avant les approximations */
    function get_order_statement() {
        return false;
    }

    function get_select_statement() {
        return false;
    }

    /** récupérer le bout d'URL correspondant aux paramètres permettant d'imiter une requête d'un
     * utilisateur assignant la valeur $this->value à ce champ */
    function get_url() {
        if ($this->value=='')
            return false;
        else
            return $this->fieldFormName.'='.urlencode($this->value);
    }
}

/** classe de champ numérique entier (offset par exemple)
 */
class NumericSField extends SField {
    /** constructeur
     * (récupère la requête de l'utilisateur pour ce champ) */
    function NumericSField($_fieldFormName) {
        $this->fieldFormName = $_fieldFormName;
        $this->get_request();
    }
    
    /** récupère la requête de l'utilisateur et échoue s'il ne s'agit pas d'un entier */
    function get_request() {
        parent::get_request();
        if ($this->value=='')
            $this->value = 0;
        if (!preg_match("/^[0-9]+$/", $this->value))
            new ThrowError('Un champ numérique contient des caractères alphanumériques.');
    }
}

class RefSField extends SField {
    var $refTable;
    var $refAlias;
    var $refCondition;
    var $exact=true;

    function RefSField($_fieldFormName,$_fieldDbName='',$_refTable,$_refAlias,$_refCondition,$_exact=true) {
        $this->fieldFormName = $_fieldFormName;
        $this->fieldDbName = $_fieldDbName;
        $this->refTable = $_refTable;
        $this->refAlias = $_refAlias;
        $this->refCondition = $_refCondition;
        $this->exact = $_exact;
        $this->get_request();
    }
    
    function get_request() {
        parent::get_request();
        if ($this->value=='00' || $this->value=='0')
            $this->value='';
    }

    function too_large() {
        return ($this->value=='');
    }

    function compare() {
        if ($this->exact)
            return "='".$this->value."'";
        else
            return " LIKE '%".$this->value."%'";
    }

    function get_single_match_statement($field) {
        return $field.$this->compare();
    }

    function get_single_where_statement($field) {
        if ($this->refTable=='')
            return $this->get_single_match_statement($field);
        return false;
    }

    function get_select_statement() {
        if ($this->value=='' || $this->refTable=='')
            return false;
        $res = implode(' OR ',array_filter(array_map(array($this,'get_single_match_statement'),$this->fieldDbName)));
        return 'INNER JOIN '.$this->refTable.' AS '.$this->refAlias.
        ' ON('.$this->refCondition.' AND '.'('.$res.')'.")";
    }
}

class RefWithSoundexSField extends RefSField {
    function get_request() {
        parent::get_request();
        if ($this->value!='')
            $this->value=soundex_fr($this->value);
    }
}

/** classe de champ texte (nom par exemple)
 */
class StringSField extends SField {
    /** récupère la requête de l'utilisateur et échoue si la chaîne contient des caractères
     * interdits */
    function get_request() {
        parent::get_request();
        if (preg_match(":[][<>{}~/§_`|%$^=+]|\*\*:", $this->value))
            new ThrowError('Un champ contient un caractère interdit rendant la recherche impossible.');
    }

    /** donne la longueur de la requête de l'utilisateur
     * (au sens strict i.e. pas d'* ni d'espace ou de trait d'union -> les contraintes réellement
     * imposées par l'utilisateur) */
    function length() {
        global $lc_accent,$uc_accent;
        return
        strlen($this->value)-strlen(ereg_replace('[a-z'.$lc_accent.$uc_accent.']','',strtolower($this->value)));
    }

    function too_large() {
        return ($this->length()<2);
    }

    /** clause WHERE correspondant à un champ de la bdd et à ce champ de formulaire
     * @param field nom de champ de la bdd concerné par la clause */
    function get_single_where_statement($field) {
        //on rend les traits d'union et les espaces équivalents
        //$regexp = preg_replace('/[ -]/','[ \-]',$this->value);
        //on remplace le pseudo language des * par une regexp
        //$regexp = str_replace('*','.+',$regexp);
        //return $field." RLIKE '^(.*[ -])?".replace_accent_regexp($regexp).".*'";

        //Nouvelle version plus rapide
        $regexp = str_replace('-',' ',$this->value);
        $regexp = str_replace('*','%',$regexp);
        return "$field LIKE '$regexp%'";
    }

    /** clause ORDER BY correspondant à ce champ de formulaire */
    function get_order_statement() {
        if ($this->value!='' && $this->fieldResultName!='')
            return $this->fieldResultName.'!="'.$this->value.'"';
        else
            return false;
    }
}

/** classe pour les noms : on cherche en plus du like 'foo%' le like '% foo' (particules)
+*/
class NameSField extends StringSField {
    function get_single_where_statement($field) {
        $regexp = str_replace('-',' ',$this->value);
        $regexp = str_replace('*','%',$regexp);
        return "$field LIKE '$regexp%' OR $field LIKE '% $regexp%'";
    }
    
    function get_order_statement() {
        if ($this->value!='' && $this->fieldResultName!='')
            return $this->fieldResultName.' NOT LIKE "'.$this->value.'"';
        else
            return false;
    }
}

/** classe de champ texte avec soundex (nom par exemple)
 */
class StringWithSoundexSField extends StringSField {
    /** clause WHERE correspondant à un champ de la bdd et à ce champ de formulaire
     * @param field nom de champ de la bdd concerné par la clause */
    function get_single_where_statement($field) {
        return $field.'="'.soundex_fr($this->value).'"';
    }
}

/** classe de champ de promotion */
class PromoSField extends SField {
    /** opérateur de comparaison (<,>,=) de la promo utilisé pour ce champ de formulaire */
    var $compareField;

    /** constructeur 
     * compareField est un champ de formulaire très simple qui ne sert qu'à la construction de la
     * clause WHERE de la promo */
    function PromoSField($_fieldFormName,$_compareFieldFormName,$_fieldDbName,$_fieldResultName) {
        parent::SField($_fieldFormName,$_fieldDbName,$_fieldResultName);
        $this->compareField = new SField($_compareFieldFormName);
    }

    /** récupère la requête utilisateur et échoue si le champ du formulaire ne représente pas une
     * promotion (nombre à 4 chiffres) */
    function get_request() {
        parent::get_request();
        if (!(empty($this->value) or preg_match("/^[0-9]{4}$/", $this->value)))
            new ThrowError('La promotion est une année à quatre chiffres.');
    }

    /** teste si la requête est de la forme =promotion -> contrainte forte imposée -> elle suffit
     * pour autoriser un affichage des résultats alors que <promotion est insuffisant */
    function is_a_single_promo() {
        return ($this->compareField->value=='=' && $this->value!='');
    }

    function too_large() {
        return (!$this->is_a_single_promo());
    }

    /** clause WHERE correspondant à ce champ */
    function get_single_where_statement($field) {
        return $field.$this->compareField->value.$this->value;
    }

    /** récupérer le bout d'URL correspondant aux paramètres permettant d'imiter une requête
     * d'un utilisateur assignant la valeur $this->value à ce champ et assignant l'opérateur de
     * comparaison adéquat */
    function get_url() {
        if (!($u=parent::get_url()))
            return false;
        return $u.'&'.$this->compareField->get_url();
    }
}

/** classe groupant des champs de formulaire de recherche */
class SFieldGroup {
    /** tableau des classes correspondant aux champs groupés */
    var $fields;
    /** type de groupe : ET ou OU */
    var $and;

    /** constructeur */
    function SFieldGroup($_and,$_fields) {
        $this->fields = $_fields;
        $this->and = $_and;
    }

    function too_large() {
        $b = true;
        for ($i=0;$i<count($this->fields);$i++)
            $b = $b && $this->fields[$i]->too_large();
        return $b;
    }

    function field_get_select($f) {
        return $f->get_select_statement();
    }

    /** récupérer la clause WHERE d'un objet champ de recherche */
    function field_get_where($f) {
        return $f->get_where_statement();
    }

    /** récupérer la clause ORDER BY d'un objet champ de recherche */
    function field_get_order($f) {
        return $f->get_order_statement();
    }

    /** récupérer le bout d'URL correspondant à un objet champ de recherche */
    function field_get_url($f) {
        return $f->get_url();
    }

    function get_select_statement() {
        return implode(' ',array_filter(array_map(array($this,'field_get_select'),$this->fields)));
    }

    /** récupérer la clause WHERE du groupe de champs = conjonction (ET) ou disjonction (OU) de
     * clauses des champs élémentaires */
    function get_where_statement() {
        $joinText=($this->and)?' AND ':' OR ';
        $res = implode($joinText,array_filter(array_map(array($this,'field_get_where'),$this->fields)));
        return ($res!='')?('('.$res.')'):'';
    }

    /** récupérer la clause ORDER BY du groupe de champs = conjonction (ET) ou disjonction (OU) de
     * clauses des champs élémentaires */
    function get_order_statement() {
        $order = array_filter(array_map(array($this,'field_get_order'),$this->fields));
        return (count($order)>0)?implode(',',$order):false;
    }

    /** récupérer le bout d'URL correspondant à ce groupe de champs = concaténation des bouts d'URL
     * des champs élémentaires */
    function get_url() {
        $url = array_filter(array_map(array($this,'field_get_url'),$this->fields));
        return (count($url)>0)?implode('&',$url):false;
    }
}
?>

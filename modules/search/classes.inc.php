<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once("xorg.misc.inc.php");

// {{{ Global variables used for the search Queries

@$globals->search->result_fields = '
    u.user_id, u.promo, u.matricule, u.matricule_ax,
    if(u.nom_usage=\'\', u.nom, u.nom_usage) AS NomSortKey,
    u.nom_usage,u.date,
    u.deces!=0 AS dcd,u.deces,
    u.perms IN (\'admin\',\'user\', \'disabled\') AS inscrit,
    u.perms != \'pending\' AS wasinscrit,
    FIND_IN_SET(\'femme\', u.flags) AS sexe,
    a.alias AS forlife,
    ad0.text AS app0text, ad0.url AS app0url, ai0.type AS app0type,
    ad1.text AS app1text, ad1.url AS app1url, ai1.type AS app1type,
    es.label AS secteur, ef.fonction_fr AS fonction,
    IF(n.nat=\'\',n.pays,n.nat) AS nat, n.a2 AS iso3166,
    COUNT(em.email) > 0 AS actif,';
// hide private information if not logged
if (S::logged())
    $globals->search->result_fields .='
        q.profile_web AS web,
        q.profile_mobile AS mobile,
        q.profile_freetext AS freetext,
        adr.city, gp.pays AS countrytxt, gr.name AS region,
        e.entreprise,';
else
    $globals->search->result_fields .="
    IF(q.profile_web_pub='public', q.profile_web, '') AS web,
        IF(q.profile_mobile_pub='public', q.profile_mobile, '') AS mobile,
        IF(q.profile_freetext_pub='public', q.profile_freetext, '') AS freetext,
        IF(adr.pub='public', adr.city, '') AS city,
        IF(adr.pub='public', gp.pays, '') AS countrytxt,
        IF(adr.pub='public', gr.name, '') AS region,
        IF(e.pub='public', e.entreprise, '') AS entreprise,";
@$globals->search->result_where_statement = '
    LEFT JOIN  applis_ins     AS ai0 ON (u.user_id = ai0.uid AND ai0.ordre = 0)
    LEFT JOIN  applis_def     AS ad0 ON (ad0.id = ai0.aid)
    LEFT JOIN  applis_ins     AS ai1 ON (u.user_id = ai1.uid AND ai1.ordre = 1)
    LEFT JOIN  applis_def     AS ad1 ON (ad1.id = ai1.aid)
    LEFT JOIN  entreprises    AS e   ON (e.entrid = 0 AND e.uid = u.user_id)
    LEFT JOIN  emploi_secteur AS es  ON (e.secteur = es.id)
    LEFT JOIN  fonctions_def  AS ef  ON (e.fonction = ef.id)
    LEFT JOIN  geoloc_pays    AS n   ON (u.nationalite = n.a2)
    LEFT JOIN  adresses       AS adr ON (u.user_id = adr.uid AND FIND_IN_SET(\'active\',adr.statut))
    LEFT JOIN  geoloc_pays    AS gp  ON (adr.country = gp.a2)
    LEFT JOIN  geoloc_region  AS gr  ON (adr.country = gr.a2 AND adr.region = gr.region)
    LEFT JOIN  emails         AS em  ON (em.uid = u.user_id AND em.flags = \'active\')';

// }}}
// {{{ class ThrowError

/** handle errors for end-users queries
 * assign the error message and runs the templates
 *
 * @author Jean-Sebastien Bedo
 */
class ThrowError
{
    public static $throwHook = array('ThrowError', 'defaultHandler');

    /** constuctor
     * @param  $explain string  the error (in natural language)
     */
    public function __construct($explain)
    {
        call_user_func(ThrowError::$throwHook, $explain);
    }

    /** defaut error handler
     */
    private static function defaultHandler($explain)
    {
        global $page, $globals;
        $page->changeTpl('search/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Annuaire');
        $page->assign('baseurl', $globals->baseurl);
        $page->trig('Erreur : '.$explain);
        $page->run();
    }
}

// }}}
// {{{ class SField                                     [Base class]

/** classe de base représentant un champ de recherche
 * (correspond à un champ du formulaire mais peut être à plusieurs champs de la bdd)
 * interface étendue pour chaque type de champ particulier
 */
class SField
{
    // {{{ properties

    /** le nom du champ dans le formulaire HTML */
    var $fieldFormName;
    /** champs de la bdd correspondant à ce champ sous forme d'un tableau */
    var $fieldDbName;
    /** champ résultat dans la requête MySQL correspondant à ce champ
     * (alias utilisé pour la clause ORDER BY) */
    var $fieldResultName;
    /** valeur du champ instanciée par l'utilisateur */
    var $value;

    // }}}
    // {{{ constructor

    /** constructeur
     * (récupère la requête de l'utilisateur pour ce champ) */
    function SField($_fieldFormName, $_fieldDbName='', $_fieldResultName='')
    {
        $this->fieldFormName   = $_fieldFormName;
        $this->fieldDbName     = $_fieldDbName;
        $this->fieldResultName = $_fieldResultName;
        $this->get_request();
    }

    // }}}
    // {{{ function get_request()

    /** récupérer la requête de l'utilisateur
     * on met une chaîne vide si le champ n'a pas été complété */
    function get_request()
    {
        $this->value = trim(Env::v($this->fieldFormName));
    }

    // }}}
    // {{{ function get_where_statement()

    /** récupérer la clause correspondant au champ dans la clause WHERE de la requête
     * on parcourt l'ensemble des champs de la bdd de $fieldDbName et on associe
     * à chacun d'entre eux une clause spécifique
     * la clause totale et la disjonction de ces clauses spécifiques */
    function get_where_statement()
    {
        if ($this->value=='') {
            return false;
        }
        $res = implode(' OR ', array_filter(array_map(array($this, 'get_single_where_statement'), $this->fieldDbName)));
        return empty($res) ? '' : "($res)";
    }

    // }}}
    // {{{ function get_order_statement()

    /** récupérer la clause correspondant au champ dans la clause ORDER BY de la requête
     * utilisé par exemple pour placer d'abord le nom égal à la requête avant les approximations */
    function get_order_statement()
    {
        return false;
    }

    // }}}
    // {{{ function get_select_statement()

    function get_select_statement()
    {
        return false;
    }

    // }}}
    // {{{ function get_url()

    /** récupérer le bout d'URL correspondant aux paramètres permettant d'imiter une requête d'un
     * utilisateur assignant la valeur $this->value à ce champ */
    function get_url()
    {
        if (empty($this->value)) {
            return false;
        } else {
            return $this->fieldFormName.'='.urlencode($this->value);
        }
    }

    // }}}
}

// }}}
// {{{ class QuickSearch                                [Google Like]

class QuickSearch extends SField
{
    // {{{ properties

    /** stores tokens */
    var $strings;
    /** stores numerical ranges */
    var $ranges;
    /** stores admin searches */
    var $email;
    var $ip;

    // }}}
    // {{{ constructor

    function QuickSearch($_fieldFormName)
    {
        $this->fieldFormName = $_fieldFormName;
        $this->get_request();
        if (preg_match(":[\]\[{}~/§_`|%$^=+]|\*\*:u", $this->value)) {
            new ThrowError('Un champ contient un caractère interdit rendant la recherche impossible.');
        }
    }

    // }}}
    // {{{ function isempty()

    function isempty()
    {
        return empty($this->strings) && empty($this->ranges) && empty($this->email) && empty($this->ip);
    }

    // }}}
    // {{{ function get_request()

    function get_request()
    {
        parent::get_request();
        $s = replace_accent(trim($this->value));
        $r = $s = str_replace('*','%',$s);

        if (S::has_perms() && strpos($s, '@') !== false) {
            $this->email = $s;
        } else if (S::has_perms() && preg_match('/[0-9]+\.([0-9]+|%)\.([0-9]+|%)\.([0-9]+|%)/', $s)) {
            $this->ip = $s;
        }
        if ($this->email || $this->ip) {
            $this->strings = $this->ranges = array();
            return;
        }

        $s = preg_replace('!\d+!', ' ', $s);
        $this->strings = preg_split("![^a-zA-Z%]+!",$s, -1, PREG_SPLIT_NO_EMPTY);
        if (count($this->strings) > 5) {
            global $page;
            $page->trig("Tu as indiqué trop d'éléments dans ta recherche, seuls les 5 premiers seront pris en compte");
            $this->strings = array_slice($this->strings, 0, 5);
        }

        $s = preg_replace('! *- *!', '-', $r);
        $s = preg_replace('!([<>]) *!', ' \1', $s);
        $s = preg_replace('![^0-9\-><]!', ' ', $s);
        $s = preg_replace('![<>\-] !', '', $s);
        $ranges = preg_split('! +!', $s, -1, PREG_SPLIT_NO_EMPTY);
        $this->ranges=Array();
        foreach ($ranges as $r) {
            if (preg_match('!^([<>]\d{4}|\d{4}(-\d{4})?)$!', $r)) $this->ranges[] = $r;
        }
    }

    // }}}
    // {{{ function get_where_statement()

    function get_where_statement()
    {
        $where = Array();
        foreach ($this->strings as $i => $s) {
            if (Env::i('with_soundex') && strlen($s) > 1) {
                $t = soundex_fr($s);
                $where[] = "sn$i.soundex = '$t'";
            } else {
                $t = str_replace('*', '%', $s).'%';
                $t = str_replace('%%', '%', $t);
                $where[] = "sn$i.token LIKE '$t'";
            }
        }

        $wherep = Array();
        foreach ($this->ranges as $r) {
            if (preg_match('!^\d{4}$!', $r)) {
                $wherep[] = "u.promo=$r";
            } elseif (preg_match('!^(\d{4})-(\d{4})$!', $r, $matches)) {
                $p1=min(intval($matches[1]), intval($matches[2]));
                $p2=max(intval($matches[1]), intval($matches[2]));
                $wherep[] = "(u.promo>=$p1 AND u.promo<=$p2)";
            } elseif (preg_match('!^<(\d{4})!', $r, $matches)) {
                $wherep[] = "u.promo<={$matches[1]}";
            } elseif (preg_match('!^>(\d{4})!', $r, $matches)) {
                $wherep[] = "u.promo>={$matches[1]}";
            }
        }
        if (!empty($wherep)) {
            $where[] = '('.join(' OR ',$wherep).')';
        }
        if (!empty($this->email)) {
            $where[] = 'ems.email = ' . XDB::escape($this->email);
        }
        if (!empty($this->ip)) {
            $ip = XDB::escape($this->ip);
            $where[] = "( ls.ip = $ip OR ls.forward_ip = $ip )";
        }

        return join(" AND ", $where);
    }

    // }}}
    // {{{ get_select_statement
    function get_select_statement()
    {
        $join = "";
        $and  = '';
        $uniq = '';
        foreach ($this->strings as $i => $s) {
            if (!S::logged()) {
                $and = "AND FIND_IN_SET('public', sn$i.flags)";
            }
            $myu  = str_replace('snv', "sn$i", $uniq);
            $join .= "INNER JOIN search_name AS sn$i ON (u.user_id = sn$i.uid $and$myu)\n";
            $uniq .= " AND sn$i.token != snv.token";
        }
        if (!empty($this->email)) {
            $join .= "LEFT JOIN emails AS ems ON (ems.uid = u.user_id)";
        }
        if (!empty($this->ip)) {
            $join .= "INNER JOIN logger.sessions AS ls ON (ls.uid = u.user_id)\n";
        }
        return $join;
    }
    // }}}
    // {{{ function get_order_statement()

    function get_order_statement()
    {
        return false;
    }

    // }}}
    // {{{ function get_score_statement

    function get_score_statement()
    {
        $sum = array('0');
        foreach ($this->strings as $i => $s) {
            $sum[] .= "SUM(sn$i.score + IF('$s'=sn$i.token,5,0))";
        }
        return join('+', $sum).' AS score';
    }

    // }}}
}

// }}}
// {{{ class NumericSField                              [Integer fields]

/** classe de champ numérique entier (offset par exemple)
 */
class NumericSField extends SField
{
    // {{{ constructor

    /** constructeur
     * (récupère la requête de l'utilisateur pour ce champ) */
    function NumericSField($_fieldFormName)
    {
        $this->fieldFormName = $_fieldFormName;
        $this->get_request();
    }

    // }}}
    // {{{ function get_request()

    /** récupère la requête de l'utilisateur et échoue s'il ne s'agit pas d'un entier */
    function get_request()
    {
        parent::get_request();
        if (empty($this->value)) {
            $this->value = 0;
        }
        if (!preg_match("/^[0-9]+$/", $this->value)) {
            new ThrowError('Un champ numérique contient des caractères alphanumériques.');
        }
    }

    // }}}
}

// }}}
// {{{ class RefSField                                  [ ??? ]

class RefSField extends SField
{
    // {{{ properties

    var $refTable;
    var $refAlias;
    var $refCondition;
    var $exact = true;

    // }}}
    // {{{ constructor

    function RefSField($_fieldFormName, $_fieldDbName='', $_refTable, $_refAlias, $_refCondition, $_exact=true)
    {
        $this->fieldFormName = $_fieldFormName;
        $this->fieldDbName   = $_fieldDbName;
        $this->refTable      = $_refTable;
        $this->refAlias      = $_refAlias;
        $this->refCondition  = $_refCondition;
        $this->exact         = $_exact;
        $this->get_request();
    }

    // }}}
    // {{{ function get_request()

    function get_request() {
        parent::get_request();
        if ($this->value=='00' || $this->value=='0') {
            $this->value='';
        }
    }

    // }}}
    // {{{ function too_large()

    function too_large()
    {
        return ($this->value=='');
    }

    // }}}
    // {{{ function compare()

    function compare()
    {
        $val = addslashes($this->value);
        return $this->exact ? "='$val'" : " LIKE '%$val%'";
    }

    // }}}
    // {{{ function get_single_match_statement()

    function get_single_match_statement($field)
    {
        return $field.$this->compare();
    }

    // }}}
    // {{{ function get_single_where_statement()

    function get_single_where_statement($field)
    {
        return $this->refTable=='' ? $this->get_single_match_statement($field) : false;
    }

    // }}}
    // {{{ function get_select_statement()

    function get_select_statement()
    {
        if ($this->value=='' || $this->refTable=='') {
            return false;
        }
        $res = implode(' OR ', array_filter(array_map(array($this, 'get_single_match_statement'), $this->fieldDbName)));
        if (is_array($this->refTable)) {
           foreach ($this->refTable as $i => $refT)
               $last = $i;
           $inner = "";
            foreach ($this->refTable as $i => $refT)
                $inner .= " INNER JOIN {$refT} AS {$this->refAlias[$i]} ON ({$this->refCondition[$i]} ".(($i == $last)?"AND ($res) ":"").")\n";
            return $inner;
        } else {
            return "INNER JOIN {$this->refTable} AS {$this->refAlias} ON ({$this->refCondition} AND ($res) )";
        }
    }

    // }}}
}

// }}}

// {{{ class RefSFieldMultipleTable
class MapSField extends RefSField
{
    var $mapId;

    function MapSField($_fieldFormName, $_fieldDbName='', $_refTable, $_refAlias, $_refCondition, $_mapId=false)
    {
        if ($_mapId === false)
            $this->mapId = Env::v($_fieldFormName, '');
        else
            $this->mapId = $_mapId;
        $this->value =  $this->mapId;
        $this->RefSField($_fieldFormName, $_fieldDbName, $_refTable, $_refAlias, $_refCondition, true, false);
    }

    function get_select_statement()
    {
        if ($this->mapId === '') return false;
        $res = implode(' OR ', array_filter(array_map(array($this, 'get_single_match_statement'), $this->fieldDbName)));
        foreach ($this->refTable as $i => $refT)
            $last = $i;
        $inner = "";
        foreach ($this->refTable as $i => $refT)
            $inner .= " INNER JOIN {$refT} AS {$this->refAlias[$i]} ON ({$this->refCondition[$i]} ".(($i == $last)?"AND ($res) ":"").")";
        return $inner;
    }
    function get_request()
    {
        $this->value = $this->mapId;
    }
}

// {{{ class RefWithSoundexSField                       [ ??? ]

class RefWithSoundexSField extends RefSField
{
    // {{{ function compare()

    function compare()
    {
        return "='".soundex_fr($this->value)."'";
    }

    // }}}
}

// }}}
// {{{ class StringSField                               [String fields]

/** classe de champ texte (nom par exemple)
 */
class StringSField extends SField
{
    // {{{ function get_request()

    /** récupère la requête de l'utilisateur et échoue si la chaîne contient des caractères
     * interdits */
    function get_request()
    {
        parent::get_request();
        if (preg_match(":[\]\[<>{}~/§_`|%$^=+]|\*\*:u", $this->value)) {
            new ThrowError('Un champ contient un caractère interdit rendant la recherche impossible.');
        }
    }

    // }}}
    // {{{ function length()

    /** donne la longueur de la requête de l'utilisateur
     * (au sens strict i.e. pas d'* ni d'espace ou de trait d'union -> les contraintes réellement
     * imposées par l'utilisateur) */
    function length()
    {
        $cleaned = replace_accent(strtolower($this->value));
        $length  = strlen(ereg_replace('[a-z0-9]', '', $cleaned));
        return strlen($this->value) - $length;
    }

    // }}}
    // {{{ function too_large()

    function too_large()
    {
        return ($this->length()<2);
    }

    // }}}
    // {{{ function get_single_where_statement()

    /** clause WHERE correspondant à un champ de la bdd et à ce champ de formulaire
     * @param field nom de champ de la bdd concerné par la clause */
    function get_single_where_statement($field)
    {
        $regexp = strtr(addslashes($this->value), '-*', '_%');
        return "$field LIKE '$regexp%'";
    }

    // }}}
    // {{{ function get_order_statement()

    /** clause ORDER BY correspondant à ce champ de formulaire */
    function get_order_statement()
    {
        if ($this->value!='' && $this->fieldResultName!='') {
            return "{$this->fieldResultName}!='".addslashes($this->value)."'";
        } else {
            return false;
        }
    }

    // }}}
}

// }}}
// {{{ class NameSField                                 [Names : serach 'n%' + '% b']

/** classe pour les noms : on cherche en plus du like 'foo%' le like '% foo' (particules)
+*/
class NameSField extends StringSField
{
    // {{{ function get_single_where_statement()

    function get_single_where_statement($field)
    {
        $regexp = strtr(addslashes($this->value), '-*', '_%');
        return "$field LIKE '$regexp%' OR $field LIKE '% $regexp%' OR $field LIKE '%-$regexp%'";
    }

    // }}}
    // {{{ function get_order_statement()

    function get_order_statement()
    {
        if ($this->value!='' && $this->fieldResultName!='') {
            return "{$this->fieldResultName} NOT LIKE '".addslashes($this->value)."'";
        } else {
            return false;
        }
    }

    // }}}
}

// }}}
// {{{ class StringWithSoundexSField                    [Strings + soundex]

/** classe de champ texte avec soundex (nom par exemple)
 */
class StringWithSoundexSField extends StringSField
{
    // {{{ function get_single_where_statement()

    /** clause WHERE correspondant à un champ de la bdd et à ce champ de formulaire
     * @param field nom de champ de la bdd concerné par la clause */
    function get_single_where_statement($field) {
        return $field.'="'.soundex_fr($this->value).'"';
    }

    // }}}
}

// }}}
// {{{ class PromoSField                                [Prom field]

/** classe de champ de promotion */
class PromoSField extends SField
{
    // {{{ properties

    /** opérateur de comparaison (<,>,=) de la promo utilisé pour ce champ de formulaire */
    var $compareField;

    // }}}
    // {{{ constructor

    /** constructeur
     * compareField est un champ de formulaire très simple qui ne sert qu'à la construction de la
     * clause WHERE de la promo */
    function PromoSField($_fieldFormName, $_compareFieldFormName, $_fieldDbName, $_fieldResultName)
    {
        parent::SField($_fieldFormName, $_fieldDbName, $_fieldResultName);
        $this->compareField = new SField($_compareFieldFormName);
    }

    // }}}
    // {{{ function get_request()

    /** récupère la requête utilisateur et échoue si le champ du formulaire ne représente pas une
     * promotion (nombre à 4 chiffres) */
    function get_request()
    {
        parent::get_request();
        if (preg_match('/^[0-9]{2}$/', $this->value)){
            $this->value = intval($this->value) + 1900;
        }
        if (!(empty($this->value) or preg_match('/^[0-9]{4}$/', $this->value))) {
            new ThrowError('La promotion est une année à quatre chiffres.');
        }
    }

    // }}}
    // {{{ function is_a_single_promo()

    /** teste si la requête est de la forme =promotion -> contrainte forte imposée -> elle suffit
     * pour autoriser un affichage des résultats alors que <promotion est insuffisant */
    function is_a_single_promo()
    {
        return ($this->compareField->value=='=' && $this->value!='');
    }

    // }}}
    // {{{ function too_large()

    function too_large()
    {
        return !$this->is_a_single_promo();
    }

    // }}}
    // {{{ function get_single_where_statement()

    /** clause WHERE correspondant à ce champ */
    function get_single_where_statement($field)
    {
        return $field.$this->compareField->value.$this->value;
    }

    // }}}
    // {{{ function get_url()

    /** récupérer le bout d'URL correspondant aux paramètres permettant d'imiter une requête
     * d'un utilisateur assignant la valeur $this->value à ce champ et assignant l'opérateur de
     * comparaison adéquat */
    function get_url()
    {
        if (!($u=parent::get_url())) {
            return false;
        }
        return $u.'&amp;'.$this->compareField->get_url();
    }

    // }}}
}

// }}}
// {{{ class SFieldGroup                                [Group fields]

/** classe groupant des champs de formulaire de recherche */
class SFieldGroup
{
    // {{{ properties

    /** tableau des classes correspondant aux champs groupés */
    var $fields;
    /** type de groupe : ET ou OU */
    var $and;

    // }}}
    // {{{ constuctor

    /** constructeur */
    function SFieldGroup($_and, $_fields)
    {
        $this->fields = $_fields;
        $this->and    = $_and;
    }

    // }}}
    // {{{ function too_large()

    function too_large()
    {
        $b = true;
        for ($i=0 ; $b && $i<count($this->fields) ; $i++) {
            $b = $b && $this->fields[$i]->too_large();
        }
        return $b;
    }

    // }}}
    // {{{ function field_get_select()

    function field_get_select($f)
    {
        return $f->get_select_statement();
    }

    // }}}
    // {{{ function field_get_where()

    /** récupérer la clause WHERE d'un objet champ de recherche */
    function field_get_where($f)
    {
        return $f->get_where_statement();
    }

    // }}}
    // {{{ function field_get_order()

    /** récupérer la clause ORDER BY d'un objet champ de recherche */
    function field_get_order($f)
    {
        return $f->get_order_statement();
    }

    // }}}
    // {{{ function field_get_url()

    /** récupérer le bout d'URL correspondant à un objet champ de recherche */
    function field_get_url($f)
    {
        return $f->get_url();
    }

    // }}}
    // {{{ function get_select_statement()

    function get_select_statement()
    {
        return implode(' ', array_filter(array_map(array($this, 'field_get_select'), $this->fields)));
    }

    // }}}
    // {{{ function get_where_statement()

    /** récupérer la clause WHERE du groupe de champs = conjonction (ET) ou disjonction (OU) de
     * clauses des champs élémentaires */
    function get_where_statement()
    {
        $joinText = $this->and ? ' AND ' : ' OR ';
        $res = implode($joinText, array_filter(array_map(array($this, 'field_get_where'), $this->fields)));
        return $res == '' ? '' : "($res)";
    }

    // }}}
    // {{{ function get_order_statement()

    /** récupérer la clause ORDER BY du groupe de champs = conjonction (ET) ou disjonction (OU) de
     * clauses des champs élémentaires */
    function get_order_statement()
    {
        $order = array_filter(array_map(array($this, 'field_get_order'), $this->fields));
        return count($order)>0 ? implode(',', $order) : false;
    }

    // }}}
    // {{{ function get_url()

    /** récupérer le bout d'URL correspondant à ce groupe de champs = concaténation des bouts d'URL
     * des champs élémentaires */
    function get_url($others=Array())
    {
        $url = array_filter(array_map(array($this, 'field_get_url'), $this->fields));
        foreach ($url as $key=>$val) {
            if (empty($val)) {
                unset($url[$key]);
            }
        }
        foreach ($others as $key=>$val) {
            if (!empty($val)) {
                $url[] = "$key=$val";
            }
        }
        return count($url)>0 ? implode('&amp;', $url) : false;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

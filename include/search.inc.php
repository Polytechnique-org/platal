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

require_once('xorg.plugin.inc.php');
require_once("search/classes.inc.php");

// {{{ function advancedSearchFromInput
function getadr_join($table) {
    return 'u.user_id='.$table.'.uid'.(Env::v('only_current',false)?' AND FIND_IN_SET(\'active\','.$table.'.statut)':'');
}
function advancedSearchFromInput()
{
    if ($with_soundex = Env::has('with_soundex')) {
        $nameField      = new RefWithSoundexSField('name',array('rn.nom1_soundex','rn.nom2_soundex','rn.nom3_soundex'),'recherche_soundex','rn','u.matricule = rn.matricule');
        $firstnameField = new RefWithSoundexSField('firstname',array('rp.prenom1_soundex','rp.prenom2_soundex'),'recherche_soundex','rp','u.matricule = rp.matricule');
    } else {
        $nameField      = new NameSField('name',array('u.nom','u.nom_usage'),'');
        $firstnameField = new StringSField('firstname',array('u.prenom'),'');
    }
    $nicknameField   = new StringSField('nickname',array('q.profile_nick'),'');
    
    $promo1Field     = new PromoSField('promo1','egal1',array('u.promo'),'');
    $promo2Field     = new PromoSField('promo2','egal2',array('u.promo'),'');
    $womanField      = new RefSField('woman',array('FIND_IN_SET(u.flags,\'femme\')+1'),'','','');
    $subscriberField = new RefSField('subscriber',array('!(u.perms IN (\'admin\',\'user\'))+1'),'','','');
    $aliveField      = new RefSField('alive',array('(u.deces!=0)+1'),'','','');

    $townField      = new RefSField('city',array('ac.city'),'adresses','ac',getadr_join('ac'),false);
    $cityIdField    = new RefSField('cityid',array('av.cityid'),'adresses','av',getadr_join('av'));
    $countryField   = new RefSField('country',array('ap.country'),'adresses','ap',getadr_join('ap'));
    $regionField    = new RefSField('region',array('ar.region'),'adresses','ar',getadr_join('ar'));
    $mapField       = new MapSField('mapid', array('gcim.map_id'), array('adresses','geoloc_city_in_maps'), array('am','gcim'), array(getadr_join('am'), 'am.cityid = gcim.city_id'));
   
    $entrepriseField = new RefSField('entreprise',array('ee.entreprise'),'entreprises','ee','u.user_id=ee.uid',false);
    $posteField      = new RefSField('poste',array('ep.poste'),'entreprises','ep','u.user_id=ep.uid', false);
    $fonctionField   = new RefSField('fonction',array('en.fonction'),'entreprises','en','u.user_id=en.uid');
    $secteurField    = new RefSField('secteur',array('fm.secteur'),'entreprises','fm','u.user_id=fm.uid');
    $cvField         = new RefSField('cv',array('u.cv'),'','','',false);
   
    $natField        = new RefSField('nationalite',array('u.nationalite'),'','','');
    $binetField      = new RefSField('binet',array('b.binet_id'),'binets_ins','b','u.user_id=b.user_id');
    $groupexField    = new RefSField('groupex',array('g.gid'),'groupesx_ins','g','u.user_id=g.guid');
    $sectionField    = new RefSField('section',array('u.section'),'','','');
    $schoolField     = new RefSField('school',array('as.aid'),'applis_ins','`as`','u.user_id=as.uid');
    $diplomaField    = new RefSField('diploma',array('ad.type'),'applis_ins','ad','u.user_id=ad.uid');
  
    $freeField       = new RefSField('free',array('q.profile_freetext'),'','','',false);
  
    return array( 
                $nameField, $firstnameField, $nicknameField, $promo1Field,
                $promo2Field, $womanField, $subscriberField, $aliveField,
                $townField, $countryField, $regionField, $entrepriseField,
                $posteField, $secteurField, $cvField, $natField, $binetField,
                $groupexField, $sectionField, $schoolField, $diplomaField,
                $freeField, $fonctionField, $cityIdField, $mapField);
}

// }}}

// {{{ class XOrgSearch

class XOrgSearch extends XOrgPlugin
{
    // {{{ properties
    
    var $_get_vars    = Array('offset', 'order', 'order_inv', 'rechercher');
    var $limit        = 20;
    var $order_defaut = 'promo';
    // type of orders : (field name, default ASC, text name, auth)
    var $orders = array(
            'nom'       =>array('nom,prenom',   true,  'nom',             AUTH_PUBLIC),
            'promo'     =>array('promo,nom',  false, 'promotion',             AUTH_PUBLIC),
            'date_mod'  =>array('u.date,nom', false, 'dernière modification', AUTH_COOKIE)
        );

    // }}}
    // {{{ function setNbLines()
    
    function setNbLines($lines)
    { $this->limit = $lines; }

    // }}}
    // {{{ function setAuth()

    function setAuth()
    {
        foreach ($this->orders as $key=>$o) {
            if ($o[3] == AUTH_COOKIE) {
                $this->orders[$key][3] = S::logged();
            } elseif ($o[3] == AUTH_PUBLIC) {
                $this->orders[$key][3] = true;
            } else {
                $this->orders[$key][3] = S::identified();
            }
        }
    }

    // }}}
    // {{{ function addOrder()

    function addOrder($name, $field, $inv_order, $text, $auth, $defaut=false)
    {
        // reverse the array, to get the defaut order first
        if ($defaut)
            $this->orders = array_reverse($this->orders);
        $this->orders[$name] = array($field, $inv_order, $text, $auth);
        if ($defaut) {
            $this->orders = array_reverse($this->orders);
            $this->order_defaut = $name;
        }
    }

    // }}}
    // {{{ function show()
    
    function show()
    {
        $this->setAuth();
	global $page;

        $offset = intval($this->get_value('offset'));
        $tab    = @$this->orders[$this->get_value('order')];
        if (!$tab || !$tab[3]) {
            $tab = $this->orders[$this->order_defaut];
        }
        $order     = $tab[0];
        $order_inv = ($this->get_value('order_inv') != '') == $tab[1];

        if ($order_inv && $order)
            $sql_order = str_replace(",", " DESC,", $order)." DESC";
        else $sql_order = $order;

        list($list, $total) = call_user_func($this->_callback, $offset, $this->limit, $sql_order);
        
	$page_max = intval(($total-1)/$this->limit);
        if(!S::logged() && $page_max > $globals->search->public_max)
            $page_max = $globals->search->public_max;

	$links = Array();
	if ($offset) {
	    $links[] = Array('u'=> $this->make_url(Array('offset'=>$offset-1)), 'i' => $offset-1,  'text' => 'précédent');
	}
	for ($i = 0; $i <= $page_max ; $i++) {
	    $links[] = Array('u'=>$this->make_url(Array('offset'=>$i)), 'i' => $i, 'text' => $i+1);
        }
	if ($offset < $page_max) {
	    $links[] = Array ('u' => $this->make_url(Array('offset'=>$offset+1)), 'i' => $offset+1, 'text' => 'suivant');
	}
        
        $page->assign('search_results', $list);
        $page->assign('search_results_nb', $total);
        $page->assign('search_page', $offset);
        $page->assign('search_pages_nb', $page_max+1);
        $page->assign('search_pages_link', $links);

        $order_links = Array();
        foreach ($this->orders as $key=>$o) if ($o[3]) {
            $order_links[] = Array(
                "text" => $o[2],
                "url"  => $this->make_url(Array('order' => $key, 'order_inv' => ($o[0] == $order) && ($order_inv != $o[1]))),
                "asc"  => ($o[0] == $order) && $order_inv,
                "desc" => ($o[0] == $order) && !$order_inv
            );
        }
        $page->assign('search_order_link', $order_links);
  
        return $total;
    }
    
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

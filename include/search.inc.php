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

require_once('xorg.plugin.inc.php');
require_once("search/classes.inc.php");

// {{{ class XOrgSearch

class XOrgSearch extends XOrgPlugin
{
    // {{{ properties
    
    var $_get_vars    = Array('offset', 'order', 'order_inv', 'rechercher');
    var $limit        = 20;
    var $order_defaut = 'promo';
    // type of orders : (field name, default ASC, text name, auth)
    var $orders = array(
            'promo'     =>array('promo',  false, 'promotion',             AUTH_PUBLIC),
            'nom'       =>array('nom',    true,  'nom',                   AUTH_PUBLIC),
            'date_mod'  =>array('u.date', false, 'dernière modification', AUTH_COOKIE)
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
                $this->orders[$key][3] = logged();
            } elseif ($o[3] == AUTH_PUBLIC) {
                $this->orders[$key][3] = true;
            } else {
                $this->orders[$key][3] = identified();
            }
        }
    }

    // }}}
    // {{{ function addOrder()

    function addOrder($name, $field, $inv_order, $text, $auth, $defaut=false)
    {
        $this->orders[$name] = array($field, $inv_order, $text, $auth);
        if ($defaut) {
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
        $tab    = $this->orders[$this->get_value('order')];
        if (!$tab || !$tab[3]) {
            $tab = $this->orders[$this->order_defaut];
        }
        $order     = $tab[0];
        $order_inv = ($this->get_value('order_inv') != '') == $tab[1];
	
        list($list, $total) = call_user_func($this->_callback, $offset, $this->limit, $order, $order_inv);
        
	$page_max = intval(($total-1)/$this->limit);

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
        $page->assign('search_pages_nb', $page_max);
        $page->assign('search_pages_link', $links);
        
        $order_links = Array();
        foreach ($this->orders as $key=>$o) if ($o[3]) {
            $order_links[] = Array(
                "text"=>$o[2],
                "url" =>$this->make_url(Array('order'    =>$key,'order_inv'=>($o[0] == $order) && ($order_inv != $o[1]))),
                "asc" =>($o[0] == $order) && $order_inv,
                "desc"=>($o[0] == $order) && !$order_inv
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

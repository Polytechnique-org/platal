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


$mois_fr = array('01'=>'Janvier',
                 '02'=>'Février',
                 '03'=>'Mars',
                 '04'=>'Avril',
                 '05'=>'Mai',
                 '06'=>'Juin',
                 'O7'=>'Juillet',
                 '08'=>'Août',
                 '09'=>'Septembre',
                 '10'=>'Octobre',
                 '11'=>'Novembre',
                 '12'=>'Decembre');


$trim_fr = array('01'=>'Janvier-Mars',
		 '04'=>'Avril-Juin',
		 '07'=>'Juillet-Septembre',
		 '10'=>'Octobre-Decembre');


function isDate($date)
{
  list($d, $m, $y) = split('[/.-]', $date);
  $dummy = date("d/m/Y", mktime (0,0,0,$m,$d,$y));
  $date = ereg_replace('-', '/', $date);
  if ($dummy != $date)
    return false;
  else
    return true;
}


function solde_until($date='')
{
    global $globals;
    $sql = "SELECT SUM(credit)-SUM(debit) FROM money_trezo";
    if (empty($date)) {
        $res = $globals->xdb->query($sql);
    } else {
        $res = $globals->xdb->query($sql." WHERE date <= {?}", $date);
    }
    return $res->fetchOneCell();
}

?>

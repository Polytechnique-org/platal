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
        $Id: tabs.inc.php,v 1.5 2004-08-31 19:48:46 x2000habouzit Exp $
 ***************************************************************************/


$tabname_array = Array(
    "general"  => "Informations<br/>générales",
    "adresses" => "Adresses<br/>personnelles",
    "poly"     => "Informations<br/>polytechniciennes",
    "emploi"   => "Informations<br/>professionnelles",
    "skill"    => "Compétences<br/>diverses",
    "mentor"   => "Mentoring"
);
    
$opened_tab = 'general';

$page->assign("onglets",$tabname_array);
$page->assign("onglet_last",'mentor');

function get_last_tab(){
    end($GLOBALS['tabname_array']);
    return key($GLOBALS['tabname_array']);
}

function get_next_tab($tabname){
    global $tabname_array;
    reset($tabname_array);
    $marker = false;
    while(list($current_tab,$current_tab_desc) = each($tabname_array)){
        if($current_tab == $tabname){
            $res = key($tabname_array);// each() sets key to the next element
            if($res != NULL)// if it was the last call of each(), key == NULL => we return the first key
                return $res;
            else{
                reset($tabname_array);
                return key($tabname_array);
            }
        }
    }
    // We should not arrive to this point, but at least, we return the first key
    reset($tabname_array);
    return key($tabname_array);
}

function draw_all_tabs(){
    global $tabname_array, $new_tab;
    reset($tabname_array);
?>
<ul id="onglet">
<?php
    while(list($current_tab,$current_tab_desc) = each($tabname_array)){
        if($current_tab == $new_tab){
            draw_tab($current_tab, true);
        }
        else{
            draw_tab($current_tab, false);
        }
    }?>
</ul>
<?php
}

function draw_tab($tab_name, $is_opened){
    global $tabname_array;
    if($is_opened){?>
           <li class="actif">
              <?php echo $tabname_array["$tab_name"];?>
           </li>
  <?php }
    else{ ?>
           <li>
          <a href="<?php echo "{$_SERVER['PHP_SELF']}?old_tab=$tab_name";?>">
                 <?php echo $tabname_array["$tab_name"];?>
              </a>
           </li>
  <?php }
}


?>

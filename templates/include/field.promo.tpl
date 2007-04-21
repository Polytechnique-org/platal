{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}

<script type="text/javascript">//<![CDATA[ 
    var prefix = "{$prefix}";
    {literal} 
    function updateRange() 
    { 
      var range = document.getElementById(prefix + 'promo_range'); 
      min = document.getElementById(prefix + 'promo_min').value; 
      max = document.getElementById(prefix + 'promo_max').value; 
      if (isNaN(min) || (min != 0 && (min < 1900 || min > 2020))) { 
        range.innerHTML = '<span class="erreur">La promotion minimum n\'est pas valide</span>'; 
        return false; 
      } else if (isNaN(max) || (max != 0 && (max < 1900  || max > 2020))) { 
        range.innerHTML = '<span class="erreur">La promotion maximum n\'est pas valide</span>'; 
        return false; 
      } else if (max != 0 && min != 0 && max < min) { 
        range.innerHTML = '<span class="erreur">L\'intervalle de promotion est inversé</span>'; 
        return false; 
      } else if (max == 0 && min == 0) { 
        range.innerHTML = 'L\'annonce est destinée à toutes les promotions'; 
      } else if (max == 0) { 
        range.innerHTML = 'L\'annonce est destinée aux promotions plus jeunes que ' + min + ' (incluse)'; 
      } else if (min == 0) { 
        range.innerHTML = "L\'annonce est destinée aux promotions plus anciennes que " + max + ' (incluse)'; 
      } else if (min == max - 1) {
        range.innerHTML = "L\'annonce est destinée aux promotions " + min + " et " + max; 
      } else if (min == max) {
        range.innerHTML = "L\'annonce est destinée à la promotion " + min;
      } else { 
        range.innerHTML = "L\'annonce est destinée aux promotions de " + min + " à " + max + ' (incluses)'; 
      } 
      return true; 
    } 
    {/literal} 
//]]></script> 

{if $full}
<table class="bicol">
{/if}
  <tr id="{$prefix}promo_min_tr" class="impair"> 
    <td class="titre">Promotion la plus ancienne</td> 
    <td> 
      <input type="text" name="{$min_field_name|default:"promo_min"}" id="{$prefix}promo_min"
             size="4" maxlength="4" value="{$promo_min|default:0}" 
             onkeyup="return updateRange();" onchange="return updateRange();" /> incluse 
      &nbsp;<span class="smaller">(ex : 1980)</span> 
    </td> 
  </tr> 
  <tr id="{$prefix}promo_max_tr" class="impair"> 
    <td class="titre">Promotion la plus jeune</td> 
    <td> 
      <input type="text" name="{$max_field_name|default:"promo_max"}" id="{$prefix}promo_max"
             size="4" maxlength="4" value="{$promo_max|default:0}" 
             onkeyup="return updateRange();" onchange="return updateRange();" /> incluse 
      &nbsp;<span class="smaller">(ex : 2000)</span> 
    </td> 
  </tr> 
  <tr id="{$prefix}promo_range_tr" class="impair"> 
    <td colspan="2" id="promo_range" class="smaller"> 
      <script type="text/javascript">updateRange();</script> 
    </td> 
  </tr> 
{if $full}
</table>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}

{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<select name="{$egal1}" onchange="updatepromofields($('select[name={$egal1}]'), $('input[name={$egal2}]'), $('input[name={$promo2}]'))" style="text-align: center">
  <option value="=" {if $promo_data.$egal1 eq "="}selected="selected"{/if}>&nbsp;=&nbsp;</option>
  <option value="&gt;=" {if $promo_data.$egal1 eq "&gt;="}selected="selected"{/if}>&nbsp;&gt;=&nbsp;</option>
  <option value="&lt;=" {if $promo_data.$egal1 eq "&lt;="}selected="selected"{/if}>&nbsp;&lt;=&nbsp;</option>
</select>
<input type="text" name="{$promo1}" size="4" maxlength="4" value="{$promo_data.$promo1}" />
&nbsp;et&nbsp;
<input type="text" name="{$egal2}" size="1" style="text-align:center" {if t($promo_data.$egal1) && $promo_data.$egal1 neq "="}value="{$promo_data.$egal2}"{else}value="&gt;=" disabled="disabled"{/if} readonly="readonly" />
<input type="text" name="{$promo2}" size="4" maxlength="4" {if t($promo_data.$egal1) && $promo_data.$egal1 neq "="}value="{$promo_data.$promo2}"{else}disabled="disabled"{/if} />
<select name="edu_type" style="text-align: center">
  <option value="{#UserFilter::GRADE_ING#}" {if $promo_data.$edu_type eq #UserFilter::GRADE_ING#}selected="selected"{/if}>X</option>
  <option value="{#UserFilter::GRADE_MST#}" {if $promo_data.$edu_type eq #UserFilter::GRADE_MST#}selected="selected"{/if}>Master</option>
  <option value="{#UserFilter::GRADE_PHD#}" {if $promo_data.$edu_type eq #UserFilter::GRADE_PHD#}selected="selected"{/if}>Docteur</option>
</select>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

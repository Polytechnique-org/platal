{***************************************************************************
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
        $Id: choix_date.tpl,v 1.5 2004/08/31 11:25:44 x2000habouzit Exp $
 ***************************************************************************}


{assign var=def_month value=$smarty.now|date_format:"%m"}
{assign var=def_year value=$smarty.now|date_format:"%Y"}
{assign var=month value=$smarty.request.mois|default:$def_month}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <div class="center">
    <input type="hidden" name="action" value="lister" />
    Afficher la période suivante :
    <select name="mois" size="1">
      {foreach key=key item=item from=$month_arr}
      <option value="{$key}" {if $month eq $key}selected="selected"{/if}>{$item}</option>
      {/foreach}
    </select>
    <input type="text" name="annee" size="10" value="{$smarty.request.annee|default:$def_year}" />
    <input type="submit" value="lister" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}

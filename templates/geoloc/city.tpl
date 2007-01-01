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
<?xml version="1.0" encoding="utf-8"?>
<city id="{$smarty.request.cityid}">
  {assign var="beginning" value=true}
  {assign var="nb_displayed" value=0}
  {iterate from=$users item="user"}{if !$beginning}<br/>{/if}{if $nb_displayed < 10}<a href="javascript:ficheXorg('{$user.alias}');">{$user.prenom|utf8_encode} {$user.nom|strtolower|ucwords|utf8_encode} - {$user.promo}</a>{else}<a href="javascript:clickOnCity({$smarty.request.cityid})">...</a>{/if}{assign var="nb_displayed" value=$nb_displayed+1}{assign var="beginning" value=false}{/iterate}
</city>
{* vim:set et sw=2 sts=2 sws=2: *}

{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<h1>Mes groupes X sur Polytechnique.net</h1>

<p class="center"> 
[<a href="http://www.polytechnique.net/login/plan">Tous les groupes X</a>] 
</p> 

{foreach from=$assos item="asso"}
<div style="width:48%;float:left" >
<fieldset style="margin:0.6em">
    <legend style="padding:4px"><strong><a href="http://www.polytechnique.net/login/{$asso.diminutif}">{$asso.nom}</a></strong></legend>
    {if $asso.has_logo}
    <a href="http://www.polytechnique.net/login/{$asso.diminutif}" style="width: 30%; display: block; float: right; ">
      <img alt="[ LOGO ]" src="http://www.polytechnique.net/{$asso.diminutif}/logo" style="width: 100%" />
    </a>
    {/if}
    <ul style="padding-top:0px;padding-bottom:0px">
        <li><a href="http://www.polytechnique.net/{$asso.diminutif}/annuaire">annuaire</a></li>
        <li><a href="http://www.polytechnique.net/{$asso.diminutif}/trombi">trombino</a></li>
        <li><a href="http://www.polytechnique.net/{$asso.diminutif}/geoloc">carte</a></li>
        {if $asso.lists}
            <li><a href="http://www.polytechnique.net/{$asso.diminutif}/lists">listes de diffusion</a></li>
        {/if}
        {if $asso.events}
            <li><a href="http://www.polytechnique.net/{$asso.diminutif}/events">{$asso.events} événement{if $asso.events > 1}s{/if}</a></li>
        {/if}
        {if !$asso.lists}
            <li style="display:block">&nbsp;</li>
        {/if}
        {if !$asso.events}
            <li style="display:block">&nbsp;</li>
        {/if}
    </ul>
</fieldset>
</div>
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

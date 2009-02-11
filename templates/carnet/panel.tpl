{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

<h1>Bilan des Notifications</h1>

<p>
Cette page récapitule tous les événements que tu surveilles de la semaine écoulée.
</p>

<p>
Les lignes en gras sont les événements qui ont été porté à notre connaissance
depuis ta dernière connexion sur cette page.<br />
Tu peux les <a href="carnet/panel?read={$now}">marquer comme vus</a> sans te déconnecter.
</p>

<p>
Tu peux choisir plus finement les données affichées sur cette page.
Il faut pour cela se rendre sur la page de <a href='carnet/notifs'>configuration des notifications</a>.
</p>

<div class="right">
{if $smarty.session.token}
<a href="carnet/rss/{$smarty.session.hruid}/{$smarty.session.token}/rss.xml" title="Notifications">{icon name=feed title='fil rss'}</a>
{/if}
</div>

{foreach from=$notifs item=cat}
<fieldset style="width: 75%; margin-left: auto; margin-right: auto">
  <legend>{$cat.title}</legend>
  {assign var=date value=false}
    {foreach from=$cat.users item=user}
    {assign var=userdate value=$cat.operation->getDate($user)}
    {if !$date || $date ne $userdate}
    {if $date}
    </ul>
    {/if}
    {assign var=date value=$userdate}
    <p>Le {$date|date_format}&nbsp;:</p>
    <ul>
    {/if}
    <li>
      {if $cat.operation->seen($user,$smarty.session.watch_last)}<strong>{/if}
      {profile user=$user promo=true}
      {if $cat.operation->seen($user,$smarty.session.watch_last)}</strong>{/if}
    </li>
    {/foreach}
  </ul>
</fieldset>
{/foreach}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

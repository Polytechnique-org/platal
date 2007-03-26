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

<h1>Bilan des Notifications</h1>

<p>
Cette page récapitule tous les événements que tu surveilles de la semaine écoulée
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
{if $smarty.session.core_rss_hash}
<a href='carnet/rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml'>{icon name=feed title='fil rss'}</a>
{/if}
</div>

{foreach from=$notifs->_data item=c key=cid}
<h2>{if ($c|@count) > 1}
{$notifs->_cats[$cid].mail} :
{else}
  {foreach from=$c item=promo}
    {if ($promo|@count) > 1}
      {$notifs->_cats[$cid].mail} :
    {else}
      {$notifs->_cats[$cid].mail_sg} :
    {/if}
  {/foreach}
{/if}</h2>

<br />

<table class='tinybicol'>
  {foreach from=$c key=p item=promo}
  {section name=row loop=$promo}
  <tr {if ( $promo[row].known > $smarty.session.watch_last ) || ( $promo[row].date eq $today ) }style="font-weight: bold"{/if}>
    <td class='titre' style="width:15%">{if $smarty.section.row.first}{$p}{/if}</td>
    <td>
      {if $promo[row].inscrit}
      <a href="profile/{$promo[row].bestalias}" class="popup2">
        {$promo[row].prenom} {$promo[row].nom}
      </a>
      {if !$promo[row].contact}
      <a href="carnet/contacts?action=ajouter&amp;user={$promo[row].bestalias}">{*
        *}{icon name=add title="ajouter à mes contacts"}</a>
      {/if}
      {else}
      {$promo[row].prenom} {$promo[row].nom}
      {/if}
    </td>
    <td style="width:25%">
      {$promo[row].date|date_format}
    </td>
  </tr>
  {/section}
  {/foreach}
</table>

<br />
{/foreach}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

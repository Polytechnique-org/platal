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

<h1>Parcours des logs du site</h1>

{if $events}

<table class="bicol">
<tr>
  <th colspan="2">Propriétés de la session</th>
</tr>
<tr class="impair">
  <td class="titre">utilisateur</td>
  <td>{$session.username} {if $session.suer}(suid by {$session.suer}){/if}
  [<a href="{$platal->ns}admin/logger/user/{$session.username}">user's log</a>]</td>
</tr>
{if $session.forward_ip}
<tr class="pair">
  <td class="titre">Hôte</td>
  <td><em>{$session.forward_host}</em> <tt>IP: {$session.forward_ip|uint_to_ip}</tt></td>
</tr>
<tr class="pair">
  <td class="titre">Proxy</td>
  <td><em>{$session.host}</em> <tt>IP: {$session.ip|uint_to_ip}</tt></td>
</tr>
{else}
<tr class="pair">
  <td class="titre">{if $session.flags}Proxy{else}Hôte{/if}</td>
  <td><em>{$session.host}</em> <tt>IP: {$session.ip|uint_to_ip}</tt></td>
</tr>
{/if}
<tr class="impair">
  <td class="titre">Navigateur</td>
  <td>{$session.browser}</td>
</tr>
</table>

<br />

<table class="bicol">
<tr>
  <th>date</th>
  <th>action</th>
  <th>donnée</th>
</tr>
{foreach from=$events item=myevent}
<tr class="{cycle values="impair,pair"}">
  <td style="font-size:90%;">{$myevent.stamp|date_format:"%x %X"}</td>
  <td><strong>{$myevent.text}</strong></td>
  <td>{$myevent.data}</td>
</tr>
{/foreach}
</table>

{else}

<form method="post" action="{$platal->ns}admin/logger">
<table class="bicol">
<tr>
  <td><strong>Date</strong></td>
  <td>
    Année
    <select name="year" onchange="this.form.submit()">
      {html_options options=$years selected=$year}
    </select>
    &nbsp;Mois
    <select name="month" onchange="this.form.submit()">
      {html_options options=$months selected=$month}
    </select>
    &nbsp;Jour
    <select name="day" onchange="this.form.submit()">
      {html_options options=$days selected=$day}
    </select>
  </td>
</tr>
<tr>
  <td><strong>Utilisateur</strong></td>
  <td>
    <input type="text" name="loguser" value="{$loguser}" />
    <input type="submit" />
  </td>
</tr>
</table>

</form>

<br />

<table class="bicol" style="font-size: 90%">
  <tr>
    <th>début</th>
    <th>utilisateur</th>
    <th>résumé</th>
    <th>actions</th>
  </tr>
{foreach from=$sessions item=mysess}
  <tr class="{cycle values="impair,pair"}">
    <td>{$mysess.start|date_format:"%x %X"}</td>
    <td><strong>{$mysess.username}</strong></td>
    <td>
      {foreach from=$mysess.events item=myevent}{$myevent}<br />{/foreach}
    </td>
    <td class="action">
      <a href="admin/logger/session/{$mysess.id}">session</a>
      <a href="admin/logger/user/{$mysess.username}">user's log</a>
    </td>
  </tr>
  {/foreach}
{if $msg_nofilters}
<tr>
  <td>{$msg_nofilters}</td>
</tr>
{/if}
</table>
{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

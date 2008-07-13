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

<td style="width: 16px">
  {if $liste.own && $liste.sub}
  {icon name=wrench title="Modérateur"}
  {elseif $liste.own}
  {icon name=error title="Modérateur mais non-membre"}
  {elseif $liste.priv}
  {icon name=weather_cloudy title="Liste privée"}
  {/if}
</td>
<td>
  <a href='{$platal->ns}lists/members/{$liste.list}'>{$liste.list}</a> 
</td>
<td>
  {$liste.desc|smarty:nodefaults}<br/>
  {if $liste.subscriptions|@count}
  <strong>&bull; Demandes d'inscription</strong><br />
  {foreach from=$liste.subscriptions item=s}
    <a href='{$platal->ns}lists/moderate/{$liste.list}?sadd={$s.id}&amp;token={xsrf_token}'
        onclick="return (is_IE || Ajax.update_html('list_{$liste.list}', '{$platal->ns}lists/ajax/{$liste.list}?sadd={$s.id}&amp;token={xsrf_token}'));">
      {icon name=add title="Accepter"}
    </a>
    <a href='{$platal->ns}lists/moderate/{$liste.list}?sid={$s.id}'>
      {icon name=delete title="Refuser"}
    </a>
    {$s.name}
    {if $s.login}
    <a href="profile/{$s.login}" class="popup2">{icon name=user_suit title="Afficher la fiche"}</a>
    {/if}
    <br />
  {/foreach}
  {/if}
  {if $liste.mails|@count}
  <strong>&bull; Demandes de modération</strong><br />
  <span class="smaller">
  {foreach from=$liste.mails item=m}
    <a href='{$platal->ns}lists/moderate/{$liste.list}?mid={$m.id}&amp;mok=1&amp;token={xsrf_token}'
        onclick="return (is_IE || Ajax.update_html('list_{$liste.list}', '{$platal->ns}lists/ajax/{$liste.list}?mid={$m.id}&amp;mok=1&amp;token={xsrf_token}'));">
      {icon name=add title="Valider l'email"}
    </a>
    <a href='{$platal->ns}lists/moderate/{$liste.list}?mid={$m.id}&amp;mdel=1&amp;token={xsrf_token}'
        onclick="return (is_IE || Ajax.update_html('list_{$liste.list}', '{$platal->ns}lists/ajax/{$liste.list}?mid={$m.id}&amp;mdel=1&amp;token={xsrf_token}'));">
      {icon name=delete title="Spam"}
    </a>
    De&nbsp;: {$m.sender}<br />
    <a href='{$platal->ns}lists/moderate/{$liste.list}?mid={$m.id}'>
      {icon name=magnifier title="Voir le message"}
    </a>
    Sujet&nbsp;: {$m.subj|hdc|smarty:nodefaults|default:"[pas de sujet]"}<br />
  {/foreach}
  </span>
  {/if}
</td>
<td class='center'>
  {if $liste.diff eq 2}modérée{elseif $liste.diff}restreinte{else}libre{/if}
</td>
<td class='center'>
  {if $liste.ins}modérée{else}libre{/if}
</td>
<td class='right'>{$liste.nbsub}</td>
<td class='right'>
  {if $liste.sub eq 2}
  <a href='{$platal->ns}lists?del={$liste.list}&amp;token={xsrf_token}'
      onclick="return (is_IE || Ajax.update_html('list_{$liste.list}', '{$platal->ns}lists/ajax/{$liste.list}?unsubscribe=1&amp;token={xsrf_token}'));">
    {icon name=cross title="me désinscrire"}
  </a>
  {elseif $liste.sub eq 1}
  {icon name=flag_orange title='inscription en attente de modération'}
  {else}
  <a href='{$platal->ns}lists?add={$liste.list}&amp;token={xsrf_token}'
      onclick="return (is_IE || Ajax.update_html('list_{$liste.list}', '{$platal->ns}lists/ajax/{$liste.list}?subscribe=1&amp;token={xsrf_token}'));">
    {icon name=add title="m'inscrire"}
  </a>
  {/if}
 </td>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

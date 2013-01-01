{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

{include file="newsletter/header.tpl" current="index"}

{if $nl->maySubmit()}
<p class="center">
  <a href="{$nl->prefix()}/submit">{icon name=page_edit value="Proposer un article"} Proposer un article pour la {$nl->name}</a>
</p>
{/if}

<h2>Ton statut</h2>

{if $nl->subscriptionState()}
{if $smarty.session.user->type != 'xnet'}
<p>
Tu es actuellement inscrit à la {$nl->name} (pour choisir le format HTML ou texte, rends toi sur la page <a href="https://{$globals->core->secure_domain}/prefs">des préférences</a>).
</p>
{/if}
<div class='center'>
  [<a href='{$nl->prefix()}/out'>{icon name=delete} me désinscrire de la {$nl->name}</a>]
</div>
{else}
<p>
Tu n'es actuellement pas inscrit à la {$nl->name}.
</p>
<div class='center'>
  [<a href='{$nl->prefix()}/in'>{icon name=add} m'inscrire à la {$nl->name}</a>]
</div>
{/if}

{include file="newsletter/search.tpl" nl_search_type="1" nl_search=""}

<h2>Les archives</h2>

<table class="bicol" cellpadding="3" cellspacing="0" summary="liste des NL">
  <tr>
    <th>date</th>
    <th>titre</th>
  </tr>
  {foreach item=nli from=$nl_list}
  <tr class="{cycle values="impair,pair"}">
    <td>{$nli->date|date_format}</td>
    <td>
      <a href="{$nl->prefix()}/show/{$nli->id()}">{$nli->title()|default:"[Sans titre]"}</a>
    </td>
  </tr>
  {/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>{$asso.nom} : Accueil</h1>

<table cellpadding="0" cellspacing="0" class='tiny'>
  {if $asso.site}
  <tr>
    <td class="titre">
      Site Web:
    </td>
    <td><a href="{$asso.site}">{$asso.site}</a></td>
  </tr>
  {/if}

  {if $asso.resp || $asso.mail}
  <tr>
    <td class="titre">
      Contact:
    </td>
    <td>
      {if $asso.mail}
      {mailto address=$asso.mail text=$asso.resp|default:"par mail" encode=javascript}
      {else}
      {$asso.resp}
      {/if}
    </td>
  </tr>
  {/if}

  {if $asso.forum}
  <tr>
    <td class="titre">
      Forum:
    </td>
    <td>
      <a href="https://www.polytechnique.org/banana/{$asso.forum}">par le web</a>
      ou <a href="news://ssl.polytechnique.org/{$asso.forum}">par nntp</a>
    </td>
  </tr>
  {/if}

  {if !$is_member && $logged && $asso.pub eq 'public' && $xnet_type != 'promotions'}
  <tr>
    <td class="titre">
      M'inscrire :
    </td>
    <td>
      <a href="{$platal->ns}subscribe">m'inscrire</a>
    </td>
  </tr>
  {/if}

  {if $asso.ax}
  <tr>
    <td class="titre center" colspan="2">
      groupe agréé par l'AX
    </td>
  </tr>
  {/if}
</table>

<br />

<div style="text-align: justify">
  {$asso.descr|smarty:nodefaults}
</div>

<br />

{if $articles->total()}
<div>
{iterate item=art from=$articles}
{include file="xnet/groupe/form.announce.tpl"}
<br />
{/iterate}
</div>
{/if}

{if $article_index && $article_index->total()}
<table class="tinybicol">
  <tr>
    <th>
      {if $smarty.session.core_rss_hash}
      <a href='{$platal->ns}rss/{$smarty.session.forlife}/{$smarty.session.core_rss_hash}/rss.xml' style="display:block;float:right">
        {icon name=feed title='fil rss'}
      </a>
      {else}
      <a href='https://www.polytechnique.org/prefs/rss?referer=events'  style="display:block;float:right">
        {icon name=feed_add title='Activer mon fil rss'}
      </a>
      {/if}
      Sommaire des annonces du groupe
    </th>
  </tr>
  {iterate item=art from=$article_index}
  <tr>
    <td>&bull; 
    {if $art.nonlu}
      <a href="{$platal->ns}#art{$art.id}"><strong>
    {else}
      <a href="{$platal->ns}?unread={$art.id}">
    {/if}
    {tidy}{$art.titre}{/tidy}
    {if $art.nonlu}</strong>{/if}</a>
    </td>
  </tr>
  {/iterate}
</table>
{/if}

{if $is_admin}
<div class="center">
  [<a href="{$platal->ns}announce/new">Ajouter une annonce</a>]
</div>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}

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


{include file="lists/header_listes.tpl" on=members}

<h1>
  Liste {$platal->argv[1]}
</h1>

<table class='tinybicol' cellpadding='0' cellspacing='0'>
  <tr>
    <td class='titre'> Adresse </td>
    <td>{mailto address=$details.addr}</td>
  </tr>
  <tr>
    <td class='titre'> Sujet </td>
    <td>{$details.desc|smarty:nodefaults}</td>
  </tr>
  <tr>
    <td class='titre'> Visibilité </td>
    <td>{if $details.priv eq 0}publique{elseif $details.priv eq 1}privée{else}admin{/if}</td>
  </tr>
  <tr>
    <td class='titre'> Diffusion </td>
    <td>{if $details.diff eq 2}modérée{elseif $details.diff}restreinte{else}libre{/if}</td>
  </tr>
  <tr>
    <td class='titre'> Inscription </td>
    <td>{if $details.ins}modérée{else}libre{/if}</td>
  </tr>
  <tr>
    <td class='titre'>Nb. membres:</td>
    <td>{$nb_m|default:"0"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Ton statut:</td>
    <td>
      {if $details.sub>1}
      Tu es inscrit sur la liste.<br />
      Te désinscrire&nbsp;:
      <a href='{$platal->pl_self(1)}?del=1'>{icon name=cross title="me désinsiscrire"}</a>
      {elseif $details.sub eq 1}
      Ta demande d'inscription est en cours de validation.
      {else}
      Tu n'es pas inscrit.<br />
      Demander ton inscription&nbsp;:
      <a href="{$platal->pl_self(1)}?add=1">{icon name=add title="demander mon inscription"}</a>
      {/if}
    </td>
  </tr>
</table>
{if $details.info}
<br />
<table class='tinybicol' cellpadding='0' cellspacing='0'>
  <tr><th colspan='2'>Informations sur la liste</th></tr>
  <tr>
    <td colspan='2'>{$details.info|smarty:nodefaults|nl2br}</td>
  </tr>
</table>
{/if}

<h1>
  modérateurs de la liste
</h1>

{if $owners|@count}
<table class='tinybicol' cellpadding='0' cellspacing='0'>
  {foreach from=$owners item=xs key=promo}
  {foreach from=$xs item=x name=all}
  <tr>
    <td class='titre'>
      {if $smarty.foreach.all.first}
      {if $promo}{$promo}{else}non-X{/if}
      {/if}
    </td>
    <td>
      {if $promo && strpos($x.l, '@') === false}
      <a href="profile/{$x.l}" class="popup2">{$x.n}</a>
      {elseif $x.x}
      <a href="{$platal->ns}member/{$x.x}">{$x.n}</a>
      {elseif $x.n}
      {$x.n}
      {else}
      {$x.l}
      {/if}
    </td>
    {if $x.p}
    <td class="right">
      {$x.p}
    </td>
    {/if}
  </tr>
  {/foreach}
  {/foreach}
</table>
{/if}

<h1>
  membres de la liste
  {if $smarty.get.alpha}
  (<a href='{$platal->pl_self(1)}'>trier par promo</a>)
  {else}
  (<a href='{$platal->pl_self(1)}?alpha=1'>trier par nom</a>)
  {/if}
</h1>

{if $members|@count}
<table class='bicol' cellpadding='0' cellspacing='0'>
  {foreach from=$members item=xs key=promo}
  {foreach from=$xs item=x name=all}
  <tr>
    <td class='titre' style="width: 20%">
      {if $smarty.foreach.all.first}
      {if $promo}{$promo}{else}non-X{/if}
      {/if}
    </td>
    <td>
      {if $promo && strpos($x.l, '@') === false}
      <a href="profile/{$x.l}" class="popup2">{$x.n}</a>
      {elseif $x.x}
      <a href="{$platal->ns}member/{$x.x}">{$x.n}</a>
      {elseif $x.n}
      {$x.n}
      {else}
      {$x.l}
      {/if}
    </td>
    {if $x.p}
    <td class="right">
      {$x.p}
    </td>
    {/if}
  </tr>
  {/foreach}
  {/foreach}
</table>
{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

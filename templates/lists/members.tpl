{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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
    <td class='titre'>Adresse&nbsp;:</td>
    <td>{mailto address=$details.addr}</td>
  </tr>
  <tr>
    <td class='titre'>Sujet&nbsp;:</td>
    <td>{$details.desc|smarty:nodefaults}</td>
  </tr>
  <tr>
    <td class='titre'>Visibilité&nbsp;:</td>
    <td>{if $details.priv eq 0}publique{elseif $details.priv eq 1}privée{else}admin{/if}</td>
  </tr>
  <tr>
    <td class='titre'>Diffusion&nbsp;:</td>
    <td>{if $details.diff eq 2}modérée{elseif $details.diff}restreinte{else}libre{/if}</td>
  </tr>
  <tr>
    <td class='titre'>Inscription&nbsp;:</td>
    <td>{if $details.ins}modérée{else}libre{/if}</td>
  </tr>
  <tr>
    <td class='titre'>Nb. membres&nbsp;:</td>
    <td>{$nb_m|default:"0"}</td>
  </tr>
  <tr class="pair">
    <td class="titre">Ton statut&nbsp;:</td>
    <td>
      {if $details.sub>1}
      Tu es inscrit sur la liste.<br />
      Te désinscrire&nbsp;:
      <a href='{$platal->pl_self(1)}?del=1&amp;token={xsrf_token}'>{icon name=cross title="me désinscrire"}</a>
      {elseif $details.sub eq 1}
      Ta demande d'inscription est en cours de validation.
      {else}
      Tu n'es pas inscrit.<br />
      Demander ton inscription&nbsp;:
      <a href="{$platal->pl_self(1)}?add=1&amp;token={xsrf_token}">{icon name=add title="demander mon inscription"}</a>
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
  {include file='lists/display_list.tpl' list=$owners delete=false no_sort_key='non-X' promo=$smarty.get.alpha}
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
  {if $details.own || hasPerms('admin,groupadmin')}
  <tr><td colspan="2">
    {include file="include/csv.tpl" url="`$platal->ns`lists/csv/`$platal->argv[1]`/`$platal->argv[1]`.csv"}
  </td></tr>
  {/if}
  {include file='lists/display_list.tpl' list=$members delete=false no_sort_key='non-X' promo=$smarty.get.alpha}
</table>

{if t($lostUsers)}
<p class="smaller">
  {icon name=error}&nbsp;Un camarade signalé par ce symbole n'a plus d'adresse de redirection et ne peut donc
  plus être contacté via son adresse polytechnique.org. Si tu connais sa nouvelle adresse, tu peux nous la communiquer en
  cliquant sur le symbole.
</p>
{/if}

{/if}


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

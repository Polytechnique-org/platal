{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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


{include file="lists/header_listes.tpl" on=trombi}

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
    <td>{$details.desc}</td>
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

<p class="center">
[<a href="{$platal->ns}{$plset_base}/{$plset_mod}{$plset_args}" {if !$show_moderators}class="erreur"{/if}>membres</a>]
[<a href="{$platal->ns}{$plset_base}/moderators/{$plset_mod}{$plset_args}" {if $show_moderators}class="erreur"{/if}>modérateurs</a>]
</p>

{include core=plset.tpl}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

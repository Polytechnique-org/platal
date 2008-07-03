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

{include file="core/plset.tpl"}

<h1>
  modérateurs de la liste
</h1>

<table cellpadding="8" cellspacing="2" style="width:100%;">
  {foreach from=$owners item=xs key=promo}
    {foreach from=$xs item=x}
      {if $promo}
      {cycle values="1,2,3,4" assign="loop"}
      {if $loop eq "1"}<tr>{/if}
        <td class='center'>
          <img src="photo/{$x.l}" width="80" alt=" [ PHOTO ] " />
          <br />
          <a href="profile/{$x.l}" class="popup2">
            {$x.n} ({$promo})
          </a>
        </td>
      {if $loop eq "4"}</tr>{/if}
      {/if}
    {/foreach}
  {/foreach}
  {if $loop eq "1"}
    {cycle values="1,2,3" assign="loop"}
    {cycle values="1,2,3" assign="loop"}
    {cycle values="1,2,3" assign="loop"}
    <td></td><td></td><td></td></tr>
  {elseif $loop eq "2"}
    {cycle values="1,2,3" assign="loop"}
    {cycle values="1,2,3" assign="loop"}
    <td></td><td></td></tr>
  {elseif $loop eq "3"}
    {cycle values="1,2,3" assign="loop"}
    <td></td></tr>
  {/if}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

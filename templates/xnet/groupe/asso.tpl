{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

{if $asso.site}
<a href="{$asso.site}"><img src='getlogo.php' alt="LOGO" style="float: right;" /></a>
{else}
<img src='getlogo.php' alt="LOGO" style="float: right;" />
{/if}

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
      <a href="https://www.polytechnique.org/banana/?group={$asso.forum}">par le web</a>
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
      <a href="{$asso.sub_url|default:"inscrire.php"}">m'inscrire</a>
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

<div>
  {$asso.descr|smarty:nodefaults}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}

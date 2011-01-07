{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<h2>Fusion des annuaires X.org - AX&nbsp;: promotions</h2>

{if $total > 0}
<p>
  Il reste {$total} problème{if $total > 1}s{/if} du{if $total > 1}s{/if} à la
  fusion des annuaires lié{if $total > 1}s{/if} aux promotions à corriger sur les profils&nbsp;:
</p>
<form action="{$platal->ns}fusionax/issues/promo/edit" method="post"> 
  {xsrf_token_field}
  <table class="bicol">
    <tr>
      <th>Nom</th>
      <th>Promo Xorg</th>
      <th>Promo entrée Xorg</th>
      <th>Promo sortie Xorg</th>
      <th>Promo AX</th>
      <th>Choix promo affichée</th>
      <th>Choix promo entrée</th>
      <th>Choix promo sortie</th>
    </tr>
    {foreach from=$issues item=profile}
    <tr>
      <td>
        <a href="profile/edit/{$profile.hrpid}">{$profile.directory_name}</a>
      </td>
      <td>{$profile.promo}</td>
      <td>{$profile.entry_year}</td>
      <td>{$profile.grad_year}</td>
      <td>{$profile.entry_year_ax}</td>
      <td><input type="text" name="display_{$profile.pid}" size="4" maxlength="4" /></td>
      <td><input type="text" name="entry_{$profile.pid}" size="4" maxlength="4" /></td>
      <td><input type="text" name="grad_{$profile.pid}" size="4" maxlength="4" /></td>
    </tr>
    {/foreach}
  </table>
  <div class="center"><input type="submit" value="Valider les corrections" /></div>
</form>
{else}
<p>Il ne reste plus d'erreur liée à la fusion des annuaires concernant les promotions&nbsp;!</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

<h1>Recherche dans l'annuaire</h1>

<form action="search" method="get"{if $show_js} id="quick_form"{/if}>
  <table class="bicol" cellspacing="0" cellpadding="4">
    {if $smarty.session.auth ge AUTH_COOKIE}
    <tr>
      <th colspan="2">
        Recherche simple
      </th>
    </tr>
    <tr>
      <td colspan="2">
        <input type='text' name="quick" value="{$smarty.request.quick}" style="width: 98%" /><br />
      </td>
    </tr>
    <tr class="noprint">
      <td style="width: 70%">
        <input type="checkbox" name="with_soundex" id="with_soundex" value="1" {if $smarty.request.with_soundex}checked="checked"{/if} /> <label for="with_soundex">Activer la recherche par proximité sonore.</label>
        <br /><input type='checkbox' name='order' id="order" value='date_mod' {if $smarty.request.order eq "date_mod"}checked='checked'{/if} /> <label for="order">Mettre les fiches modifiées récemment en premier.</label>
        <br /><input type='checkbox' name='nonins' id="nonins" {if $smarty.request.nonins}checked='checked'{/if} value='1' /> <label for="nonins">Chercher uniquement des non inscrits.</label>
      </td>
      <td class="right">
        <br /><input type="submit" value="Chercher" />
      </td>
    {else}
    <tr class="noprint">
      <td style="width: 60%">
        <input type='text' name="quick" value="{$smarty.request.quick}" style="width: 98%" /><br />
      </td>
      <td class="right">
        <input type="submit" value="Chercher" />
      </td>
    {/if}
    </tr>
  </table>
</form>
{if $smarty.session.auth ge AUTH_COOKIE}
<p class="center">[<a href="search/adv">Effectuer une recherche avancée</a>]</p>
{/if}

{if $show_js}
{literal}
<script type="text/javascript">
  <!--
  // Activate the first search input field.
  document.getElementById("quick_form").quick.focus();
  // -->
</script>
{/literal}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

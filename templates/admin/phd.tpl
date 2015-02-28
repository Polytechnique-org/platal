{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<h1>Mise à jour des année de soutenance de thèse</h1>
{if t($nothing)}
  <p>Rien à faire.</p>
{* presentation page, listing possible graduation years *}
{elseif t($promo_list)}
<p>
  Années de soutenance de thèse prévues pour lesquelles il reste des doctorants n'ayant pas soutenu&nbsp;:<br />|
  {foreach from=$promo_list item=promo}
    <a href="admin/phd/{$promo}">{$promo}</a> |
  {/foreach}
  <br />
  <a href="admin/phd/bulk">Ajout d'une liste</a>
</p>
{* this page allows bulk updating, several graduation years at once *}
{elseif $promo=="bulk"}
  {if t($updatedAccounts)}
    <p>
      Les comptes suivants ont été mis à jour&nbsp;:
      <ul>
      {foreach from=$updatedAccounts key=hruid item=name}
       <li><a href="{$platal->ns}admin/user/{$hruid}">{$name}</a></li>
      {/foreach}
      </ul>
    </p>
  {/if}

  <form action="admin/phd/bulk/validate" id="phd_bulk" method="post">
    {xsrf_token_field}
  <table class="bicol">
   <tr>
     <td>Identifiant (prenom.nom ou hrid)</td>
     <td>Année de soutenance</td>
   </tr>
    <tr>
      <td colspan="2"><textarea name="people" rows="20" cols="80"></textarea></td>
    </tr>
  </table>

  <p class="center">
    <strong>Séparateur&nbsp;:</strong>
    <input type="text" name="separator" value=";" size="1" maxlength="1" /><br /><br />
    <input type='submit' value='Ajouter' />
  </p>
  </form>

  <p>
   <a href="admin/phd">Revenir à la liste des années de soutenance de thèse prévues pour lesquelles il reste des doctorants n'ayant pas soutenus.</a>
  </p>
{* else, we list members of the selected graduation year *}
{else}
<script type="text/javascript">//<![CDATA[
{literal}
var toggleState = false;
function toggleAll() {
  toggleState = !toggleState;
  var boxes = $(":checkbox.completed");
  if (toggleState) {
    boxes.attr("checked", "checked");
  } else {
    boxes.removeAttr("checked");
  }
}
{/literal}
//]]></script>

<form action="admin/phd/{$promo}/validate" id="phd_list" method="post">
  {xsrf_token_field}
  <table class="bicol" summary="liste des doctorants">
    <tr>
      <th>Nom</th>
      <th>Année de soutenance prévue</th>
      <th>A soutenu <a href="javascript:toggleAll()">{icon name="arrow_refresh" title="Tout (dé)cocher"}</a></th>
    </tr>
    {iterate item=profile from=$list}
    <tr class="{cycle values="impair,pair"}">
      <td>{$profile.directory_name}</td>
      <td class="center">
        <input type="text" name="grad_year_{$profile.pid}" value="{$promo}" size="10" maxlength="10" />
      </td>
      <td class="center">
        <input type="checkbox" class="completed" name="completed_{$profile.pid}" />
      </td>
    </tr>
    {/iterate}
    <tr>
      <td class="center" colspan="3">
        <input type="submit" value="Valider" />
      </td>
    </tr>
  </table>
</form>

<p>
  <a href="admin/phd">Revenir à la liste des années de soutenance de thèse prévues pour lesquelles il reste des doctorants n'ayant pas soutenus.</a>
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

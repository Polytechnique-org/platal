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

{if t($nothing)}
<p>Rien à faire.</p>
{elseif t($promo_list)}
<p>
  Années de soutenance de thèse prévues pour lesquelles il reste des doctorants n'ayant pas soutenus&nbsp;:<br />|
  {foreach from=$promo_list item=promo}
  <a href="admin/phd/{$promo}">{$promo}</a> |
  {/foreach}
</p>
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

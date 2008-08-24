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

<h1>{$asso.nom}&nbsp;: Validation des inscriptions</h1>

<script type="text/javascript">//<![CDATA[
{literal}
  var toggleState = false;
  function toggleSelection()
  {
    toggleState = !toggleState;
    var boxes = $(':checkbox.select_sub');
    if (toggleState) {
      boxes.attr("checked", "checked");
    } else {
      boxes.removeAttr("checked");
    }
    return true;
  }
{/literal}
//]]></script>

<form action="{$platal->ns}subscribe/valid" method="post">
  <table class="tinybicol">
    <tr>
      <th><a href="javascript:toggleSelection()">{icon name="arrow_refresh" title="Inverser la sélection"}</a></th> 
      <th>Prénom Nom</th>
      <th>Date de demande</th>
      <th></th>
    </tr>
    {iterate from=$valid item=user}
    <tr>
      <td><input type="checkbox" name="subs[{$user.hruid}]" value="1" class="select_sub" /></td>
      <td><a href="profile/{$user.hruid}" class="popup2">{$user.prenom} {$user.nom} (X{$user.promo})</a></td>
      <td>{$user.date|date_format}</td>
      <td><a href="{$platal->ns}subscribe/{$user.hruid}">{icon name=magnifier title="Détails"}</a></td>
    </tr>
    {/iterate}
  </table>

  <div class="center">
    {xsrf_token_field}
    <input type="submit" name="valid" value="Accepter" />
  </div>

  <div>
    Pour voir le détail sur une demande, clique sur le lien {icon name=magnifier title="Détails"}.<br />
    Pour refuser une demande, tu dois aller consulter les détails et remplir la raison du refus.
  </div>
</form>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

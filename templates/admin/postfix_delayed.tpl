{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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

<h1>Emails en attente de décision</h1>

<table class="bicol" cellpadding='0' cellspacing='0'>
  <tr>
    <th>Checksum</th>
    <th>Nb d'emails reçus</th>
    <th>Dernier reçu</th>
    <th>Premier reçu</th>
    <th>Etat</th>
    <th>Actions</th>
  </tr>
  {iterate from=$mails item=m}
  <tr class="{cycle values="impair,pair"}">
    <td>{$m.crc}</td>
    <td><strong>{$m.nb}</strong></td>
    <td>{$m.update_time|date_format}</td>
    <td>{$m.create_time|date_format}</td>
    <td><strong>{if $m.del}Poubelle{elseif $m.ok}Autorisé{else}En attente{/if}</strong></td>
    <td>
      <form method="post" action="admin/postfix/delayed">
        <div>
          <input type="hidden" name="crc" value="{$m.crc}" />
          <input type="submit" name="ok"  value="Laisser passer" />
          <input type="submit" name="del" value="Effacer les emails" />
        </div>
      </form>
    </td>
  </tr>
  {/iterate}
</table>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

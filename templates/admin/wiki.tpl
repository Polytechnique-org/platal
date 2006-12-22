{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Pages wiki du site</h1>

{literal}
<script type="text/javascript">
// <!--
  function check_all_boxes(form,action) {
    var boxes = document.getElementById(form).getElementsByTagName('input');
    for (var i=0; i<boxes.length; i++) if (boxes[i].type == 'checkbox') {
      if (action == 'toggle')
        boxes[i].checked = !boxes[i].checked;
      else
        boxes[i].checked = action;
    }
    return false;
  }
// -->
</script>
{/literal}
<form action="admin/wiki/update" method="post" id="update_pages">
<table class="bicol">
  <tr>
    <th>
      page
    </th>
    <th>
      lecture
    </th>
    <th>
      écriture
    </th>
    <th class="action">
      action
    </th>
  </tr>
{foreach from=$wiki_pages key=cat item=pages}
  <tr class="pair">
    <td colspan="4" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      <img src="images/k2.gif" alt="-" width="9" height="21" />
      {$cat}
    </td>
  </tr>
{foreach from=$pages item=perm key=page name=pages}
  <tr class="impair" onmouseover="this.className='pair';" onmouseout="this.className='impair';">
    <td style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {if $smarty.foreach.pages.last}
      <img src="images/L.gif" alt="L" width="12" height="21" />
      {else}
      <img src="images/T.gif" alt="|" style="width: 12px: height: 21px" />
      {/if}
      <a href="{$cat}/{$page}">{$page}</a> <a href="{$cat}/{$page}?action=edit" class="indice">{icon name=page_edit title='éditer'}</a>
    </td>
    <td class="center" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {$perm.read}
    </td>
    <td class="center" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {$perm.edit}
    </td>
    <td class="action" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      <input type="checkbox" name="{$cat}/{$page}"/>
    </td>
  </tr>
{/foreach}
{/foreach}
  <tr class="pair">
    <td class="action" colspan="4">
      <span onclick="check_all_boxes('update_pages', true)">{icon name=tick title='tout cocher'}</span>
      <span onclick="check_all_boxes('update_pages', false)">{icon name=cross title='tout décocher'}</span>
      <span onclick="check_all_boxes('update_pages', 'toggle')">{icon name=arrow_refresh title='toggle'}</span>
    </td>
  </tr>
  <tr class="pair">
    <td>
      Attribue les permissions aux pages cochées :
    </td>
    <td>
      <select name="read">
        <option value=""> - </option>
        {html_options options=$perms_opts}
      </select>
    </td>
    <td>
      <select name="edit">
        <option value=""> - </option>
        {html_options options=$perms_opts}
      </select>
    </td>
    <td class="option">
      <input type="submit" value="ok"/>
    </td>
  </tr>
</table>
</form>
{* vim:set et sw=2 sts=2 sws=2: *}

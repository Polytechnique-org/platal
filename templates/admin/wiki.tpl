{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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
  var toggle = 0;
  function replie(me, cat) {
    if (toggle == 1) return;
    toggle = 2;
    $("tr[@id^=row_"+cat+"_]").hide();
    $(me).attr('src', 'images/k1.gif');
  }
  function deplie(me, cat) {
    if (toggle == 2) return;
    toggle = 1;
    $("tr[@id^=row_"+cat+"_]").show();
    $(me).attr('src', 'images/k2.gif');
  }
  function toggle_folder() {
    me = this;
    if ($(this).attr("class") == "wiki_category")
        me = $("../img.wiki_root", me)[0]; 
    var cat=$.trim($(me).parent().text().replace(/(.*)\([0-9]+\)/, "$1"));
    if ($(me).attr('src') == "images/k1.gif") {
      deplie(me, cat);  
    }
    replie(me, cat);
    setTimeout("toggle = 0;", 10);
  }
  $(document).ready(function() {
    $("tr.pair img[@alt=-]").css("cursor","pointer").click(toggle_folder).each(toggle_folder);
    $(".wiki_category").css("cursor","pointer").click(toggle_folder);
  });
// -->
</script>
{/literal}

<p class="center">
   {icon name=magnifier} <a href="Site/AllRecentChanges">Voir les changements récents</a>
</p>

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
      <img class="wiki_root" src="images/k2.gif" alt="-" width="9" height="21" />
      <span class="wiki_category">{$cat}</span> ({$pages|@count}) <a href="{$cat}/RecentChanges">{icon name=magnifier title="Changements récents"}</a>
    </td>
  </tr>
{foreach from=$pages item=perm key=page name=pages}
  <tr id="row_{$cat}_{$page}" class="impair" onmouseover="this.className='pair';" onmouseout="this.className='impair';">
    <td style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {if $smarty.foreach.pages.last}
      <img src="images/L.gif" alt="L" width="12" height="21" />
      {else}
      <img src="images/T.gif" alt="|" style="width: 12px: height: 21px" />
      {/if}
      <a href="{$cat}/{$page}">{$page}</a>{if $perm.cached}*{/if} <a href="{$cat}/{$page}?action=edit" class="indice">{icon name=page_edit title='éditer'}</a>
    </td>
    <td class="center" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {$perm.read}
    </td>
    <td class="center" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      {$perm.edit}
    </td>
    <td class="action" style="margin-top: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; height: 20px">
      <a href="admin/wiki/rename/{$cat}.{$page}" onclick="var newname=prompt('Déplacer la page {$cat}.{$page} vers :', '{$cat}.{$page}'); if (!newname) return false; this.href += '/'+newname;">{icon name=book_next title='déplacer'}</a>
      <a href="admin/wiki/delete/{$cat}.{$page}" onclick="return confirm('Supprimer la page {$cat}.{$page} ?');">{icon name=cross title='supprimer'}</a>
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

<p class="smaller">
  * : les pages marquées d'une astérisque sont actuellement disponibles en cache (accès plus rapide)
</p>
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

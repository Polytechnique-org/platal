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

{include core=table-editor.tpl}

<h1>Administration Polytechnique.org</h1>

{literal}
<script type="text/javascript">
  <!--
  function del_grade( myid ) {
    if (confirm ("You are about to delete this entry. Do you want to proceed?")) {
      document.forms.form_grade.act.value = "del";
      document.forms.form_grade.gid.value = myid;
      document.forms.form_grade.submit();
      return true;
    }
  }
  // -->
</script>
{/literal}

<form method="post" action='{$t->pl}/edit/{$id}#form_grade' id='form_grade'>
  <table class='bicol'>
    <tr>
      <th>id</th>
      <th>intitulé</th>
      <th>ordre</th>
      <th>&nbsp;</th>
    </tr>
    {iterate from=$grades item=g}
    <tr class="{cycle values="pair,impair"}">
      <td>{$g.gid}</td>
      <td>
        <input type='text' size='65' value="{$g.text}" name="grades[{$g.gid}]" />
      </td>
      <td>
        <input type='text' maxlength='2' value="{$g.pos}" name="pos[{$g.gid}]" />
      </td>
      <td class='action'>
        <a href='javascript:del_grade({$g.gid})'>{icon name=delete title='supprimer grade'}</a>
      </td>
    </tr>
    {/iterate}
    <tr class="{cycle values="impair,pair"}">
      <td></td>
      <td>
        <input type='text' size='65' name="grades[0]" />
      </td>
      <td>
        <input type='text' maxlength='2' name="pos[0]" value="0" />
      </td>
      <td class='action'>
        <a href='javascript:document.forms.form_grade.submit()'>{icon name=add title='nouveau grade'}</a>
      </td>
    </tr>
    <tr class="{cycle values="impair,pair"}">
      <td colspan='4' class="center">
        <input type='hidden' name='frm_id' value='{$smarty.post.frm_id}' />
        <input type='hidden' name='action' value='{$smarty.post.action}' />
        <input type='hidden' name='act' value='' />
        <input type='hidden' name='gid' value='' />
        <input type='submit' name='gr_sub' value='Enregistrer' />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

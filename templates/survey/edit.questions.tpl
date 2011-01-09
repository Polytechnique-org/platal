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

{literal}
<script id="q_edit_new" type="text/x-jquery-tmpl">
  <div>
    <div><strong>Question ${qid + 1}</strong></div>
    Type de question&nbsp;: <select name="q_edit[${qid}][type]">
      <option value=""></option>
      <option value="text">Texte</option>
      <option value="section">Section</option>
    </select>
    <div class="q_edit_form"></div>
  </div>
</script>

<script id="q_edit_base" type="text/x-jquery-tmpl">
  {{if type}}
    {{tmpl "#q_edit_" + type}}
  {{/if}}
</script>

<script id="q_edit_text" type="text/x-jquery-tmpl">
  Question&nbsp;: <input type="text" name="q_edit[${qid}][label]" /><br />
</script>

<script id="q_edit_section" type="text/x-jquery-tmpl">
  <div id="section_${qid}" style="padding-left: 4ex; border-left: 1px solid white">
    <div class="center">
      <a href="javascript:$('#section_${qid}').addQuestion(next_qid++)">
        {/literal}{icon name="add"}{literal} Ajouter une question
      </a>
    </div>
  <div>
</script>
{/literal}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}

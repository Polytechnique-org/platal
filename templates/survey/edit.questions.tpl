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
  <div style="clear: both; padding-top: 1em" class="q_edit" id="q_edit[${qid}]">
    <div>
      <span class="q_edit_label" style="font-weight: bold">Question ${qid + 1}</span> 
      (<a onclick="$(this).removeQuestion()" style="text-decoration: none">
        {/literal}{icon name="delete"}{literal} Supprimer
      </a>)
    </div>
    Titre&nbsp;: <input type="text" name="q_edit[${qid}][label]"
                        value="{{if label}}${label}{{/if}}" /><br />
    Type de question&nbsp;: <select name="q_edit[${qid}][type]">
      <option value=""></option>
      <option value="section" {{if type}}{{if type == 'section'}}selected="selected"{{/if}}{{/if}}>
        Section
      </option>
      <option value="text" {{if type}}{{if type == 'text'}}selected="selected"{{/if}}{{/if}}>
        Texte
      </option>
      <option value="multiple" {{if type}}{{if type == 'multiple'}}selected="selected"{{/if}}{{/if}}>
        Question à choix multiples
      </option>
    </select>
    <div class="q_edit_form">
      {{tmpl "#q_edit_base"}}
    </div>
  </div>
</script>

<script id="q_edit_base" type="text/x-jquery-tmpl">
  {{if type}}
    {{tmpl "#q_edit_" + type}}
  {{/if}}
</script>

<script id="q_edit_text" type="text/x-jquery-tmpl">
</script>

<script id="q_edit_section" type="text/x-jquery-tmpl">
  <div id="q_edit[${qid}][section]" style="padding-left: 4ex; border-left: 1px solid white">
    <div class="add_question">
      <a onclick="$(this).addQuestion()" style="text-decoration: none">
        {/literal}{icon name="add"}{literal} Ajouter une question
      </a>
    </div>
  </div>
</script>

<script id="q_edit_multiple" type="text/x-jquery-tmpl">
  <div id="q_edit[${qid}][answers]">
    <div class="add_answer">
      <a onclick="$(this).multiple_addAnswer()">
        {/literal}{icon name="add"}{literal} Ajouter une réponse
      </a>
    </div>
    {{if parameters}}{{if parameters.answer}}
      {{tmpl(parameters.answer) "#q_edit_multiple_answer"}}
    {{/if}}{{/if}}
    <div>
      Permettre la sélection de plusieurs réponses ?
      <select name="q_edit[${qid}][subtype]">
        <option value="checkbox">Oui</option>
        <option value="radio" selected="selected">Non</option>
      </select>
    </div>
    <div>
      Ajouter une case Autre ?
      <select name="q_edit[${qid}][allow_other]">
        <option value="1">Oui</option>
        <option value="" selected="selected">Non</option>
      </select>
    </div>
  </div>
</script>

<script id="q_edit_multiple_answer" type="text/x-jquery-tmpl">
  <div>
    <span class="q_edit_answer_box"></span>
    Réponse&nbsp;: <input type="text" name="q_edit[${qid}][answer][][value]" value="${value}" />
  </div>
</script>

{/literal}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}

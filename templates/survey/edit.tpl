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

<h1>Edition de sondage</h1>

<form action="survey/edit/{$survey->shortname}" method="post" id="form">
  <fieldset>
    <legend>Description du sondage</legend>

    Titre&nbsp;: <input type="text" name="title" value="{$survey->title}" /><br />
    Nom&nbsp;: <input type="text" name="shortname" value="{$survey->shortname}" /><br />
    Description&nbsp;:<br /><textarea name="description" style="width: 100%">{$survey->description}</textarea>
  </fieldset>

  <fieldset>
    <legend>Paramètre du sondage</legend>
    Premier jour&nbsp;: <input type="text" class="datepicker" name="begin" value="{$survey->begin}" /><br />
    Dernier jour&nbsp;: <input type="text" class="datepicker" name="end" value="{$survey->end}" /><br />
    Sondage anonyme&nbsp;: <label>Oui&nbsp;<input type="radio" name="anonymous" value="1" checked="checked" /></label>
    <label><input type="radio" name="anonymous" value="0" />&nbsp;Non</label>
  </fieldset>

  <h2>Questions</h2>

  <div id="questions" class="q_edit">
    <div class="add_question">
      <a onclick="$(this).addQuestion()" style="text-decoration: none">
        {icon name="add"} Ajouter une question
      </a>
    </div>
  </div>

  <div class="center">
    <input type="submit" name="valid" value="Soumettre" />
  </div>
</form>

{include file="survey/vote.questions.tpl"}
{include file="survey/edit.questions.tpl"}

{literal}
<script type="text/javascript">
  //<![CDATA[
  var questions = {/literal}{$survey->exportQuestionsToJSON()|smarty:nodefaults}{literal};

  $(function() {
    $('#form').submit(function() {
      $(this).buildParentsQuestions();
      return true;
    });
    $('#questions').prepareQuestions(questions);
  });
  //]]>
</script>
{/literal}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}

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

    <tr>
      <td class="titre">Question</td>
      <td><input type="text" name="survey_question[question]" size="50" maxlength="200" value="{$survey_current.question}"{if $disable_question} disabled="disabled"{/if}/></td>
    </tr>
    <tr>
      <td class="titre">Commentaire</td>
      <td><textarea name="survey_question[comment]" rows="5" cols="60">{$survey_current.comment}</textarea></td>
    </tr>
    <tr>
      <td></td>
      <td class="smaller">
        <a href="wiki_help/notitle" class="popup3">
          {icon name=information title="Syntaxe wiki"} Voir la syntaxe wiki autorisée pour le commentaire d'une question
        </a>
      </td>
    </tr>
    <script type="text/javascript">//<![CDATA[
      var id = new Array();
      id['choices'] = {$survey_current.choices|@count};
      id['subquestions'] = {$survey_current.subquestions|@count};
      {literal}
      function newField(name, tid)
      {
        fid = "t" + id[name];
        $("#" + name + "_" + tid).before('<div id="' + name + '_' + fid + '">'
            + '<input id="' + name + '_' + fid + '_field" type="text" name="survey_question[' + name + '][' + fid + ']" size="50" maxlength="200" value="" />&nbsp;'
            + '<a href="javascript:removeField(&quot;' + name + '&quot;,&quot;' + fid + '&quot;)"><img src="images/icons/delete.gif" alt="" title="Supprimer" /></a>'
            + '</div>');
        id[name]++;
        $("#" + name + "_" + fid + "_field").focus();
      }
      function removeField(name, tid)
      {
        $("#" + name + "_" + tid).remove();
      }
      {/literal}
    //]]></script>

{* vim:set et sw=2 sts=2 ts=8 fenc=utf-8: *}

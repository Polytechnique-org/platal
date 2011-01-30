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
<script id="question_base" type="text/x-jquery-tmpl">
  {{tmpl "#question_" + type}}
</script>

<script id="question_section" type="text/x-jquery-tmpl">
  <fieldset>
    <legend>${label}</legend>

    {{tmpl(children) "#question_base"}}
  </fieldset>
</script>

<script id="question_text" type="text/x-jquery-tmpl">
  <div>
    {{if subtype}}
      {{if subtype == 'monoline'}}
        <strong>${label}</strong>&nbsp;:
        <input type="text" name="qid[${qid}]" value="" />
      {{else}}
        <div><strong>${label}</strong></div>
        <textarea name="qid[${qid}]" rows="4" cols="80"></textarea>
      {{/if}}
    {{else}}
      <strong>${label}</strong>&nbsp;:
      <input type="text" name="qid[${qid}]" value="" />
    {{/if}}
  </div>
</script>

<script id="question_multiple" type="text/x-jquery-tmpl">
  <div>
    <div><strong>${label}</strong></div>
    {{each answers}}
      <input type="${subtype}" name="qid[${qid}][]" value="${$index}" /> ${$value}<br />
    {{/each}}
    {{if allow_other}}
      <input type="${subtype}" name="qid[${qid}][other][checked]" value="1" /> Autre, préciser&nbsp;:
      <input type="text" name="qid[${qid}][other][text]" />
    {{/if}}
  </div>
</script>
{/literal}

{* vim:set et sw=2 sts=2 ts=8 enc=utf-8: *}

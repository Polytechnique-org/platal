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

<script type="text/javascript">
{literal}
// <![CDATA[
$(function() {
  $(".error_state").click(function() {
    $(this).children(".error_state_content").toggle();
  });
});
// ]]>
{/literal}
</script>

<h1>Erreurs d'exécution</h1>
  <form action="site_errors" method="post">
    <div>
      <input type="submit" name="clear" value="Effacer les erreurs" />
    </div>
  </form>
  {iterate from=$errors item=error}
  <fieldset>
    <legend>{$error->date}</legend>
    <pre>{$error->error}</pre>
    {foreach from=$error->state item=table key=name}
    <div class="error_state">
      <div><strong>{$name} (click to show/hide content)</strong></div>
      <div class="error_state_content" style="display: none">
        {php}
        $var = $this->get_template_vars('table');
        var_dump($var);
        {/php}
      </div>
    </div>
    {/foreach}
  </fieldset>
  {/iterate}

{* vim:set et sws=2 sts=2 sw=2 fenc=utf-8: *}

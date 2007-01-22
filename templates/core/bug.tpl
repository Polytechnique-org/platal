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

<script type="text/javascript">//<![CDATA[
{literal}
var edited=false;

function cleanContent()
{
  if (edited == false) {
    var field = document.getElementById('flyspray_detail');
    field.value = '';
    field.style.color = "black";
    field.style.textAlign = "left";
  }
}

function fillContent()
{
  var field = document.getElementById('flyspray_detail');
  if (field.value == '' || field.value.toUpperCase() == field.value) {
    field.value = "** Explique ici le problème ou l'amélioration proposée **";
    field.style.color = "gray";
    field.style.textAlign = "center";
    edited = false;
  } else {
    edited = true;
  }
}
{/literal}
//]]></script>

<div style="width:800px;height:600px">
{if $bug_sent}
<script type="text/javascript">window.close();</script>
{/if}
<form action="send_bug" method="post" onsubmit="cleanContent()">
  <h1>Signaler un bug ou demander une amélioration</h1>
  <div style="margin-left:10%;margin-right:10%">
    <select name="task_type">
	  <option value="bug">Bug</option>
      <option value="wish">Wish</option>
    </select>
    &nbsp;&nbsp;Sujet : <input type="text" name="item_summary" id="flyspray_title" value="sur la page {$smarty.server.HTTP_REFERER}" size="50" maxlength="100"/>
    <textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10" style="width:100%;margin-top:10px;margin-bottom:10px;height:400px;display:block;" onFocus="cleanContent()" onBlur="fillContent()"></textarea>
    <input type="hidden" name="page" value="{$smarty.server.HTTP_REFERER}" />
    <input type="hidden" name="browser" value="{$smarty.server.HTTP_USER_AGENT}" />
    <input type="hidden" name="skin" value="{$smarty.session.skin}" />
    <div class="center">
      <input type="button" value="Fermer" onclick="window.close()"/>
      <input type="submit" name="send" value="Signaler le bug"/>
    </div>
  </div>
</form>
<script type="text/javascript">
  fillContent();
</script>
</div>

{* vim:set et sws=2 sts=2 sw=2: *}

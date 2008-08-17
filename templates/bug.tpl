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
  if (field.value == '' || (field.value.toUpperCase() == field.value && field.value.toLowerCase() == field.value)) {
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
<p class="erreur">
  Ton message a bien été envoyé au support de {#globals.core.sitename#}, tu devrais en
  recevoir une copie d'ici quelques minutes. Nous allons le traiter et y répondre
  dans les plus brefs délais.
</p>

<div class="center"><input type="submit" onclick="window.close()" name="close" value="Fermer" /></div>
{else}
<form action="send_bug" method="post" onsubmit="cleanContent()">
  {xsrf_token_field}
  <h1>Signaler un bug ou demander une amélioration</h1>
  <div style="margin-left:10%;margin-right:10%">
    <select name="task_type">
      <option value="bug">Erreur</option>
      <option value="wish">Souhait</option>
      <option value="help">Aide/Dépannage</option>
    </select>
    &nbsp;&nbsp;Sujet&nbsp;: <input type="text" name="item_summary" id="flyspray_title" value="sur la page { $location }" size="50" maxlength="100"/>
    <textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10" style="width:100%;margin-top:10px;margin-bottom:10px;height:400px;display:block;" onFocus="cleanContent()" onBlur="fillContent()"></textarea>
    <input type="hidden" name="page" value="{$smarty.server.HTTP_REFERER|default:$smarty.request.page}" />
    <div class="center">
      <input type="button" value="Abandonner" onclick="window.close()"/>
      <input type="submit" name="send" value="Envoyer"/>
    </div>
  </div>
</form>
<script type="text/javascript">
  fillContent();
</script>
</div>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}

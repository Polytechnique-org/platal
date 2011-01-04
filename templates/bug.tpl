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

<fieldset style="width:700px; margin-left: 30px">
  <legend>{icon name="bug"} Signaler un bug ou demander une amélioration</legend>

  {if $bug_sent}
  <div>
    <input type="submit" onclick="window.close()" name="close" value="Fermer" />
  </div>
  {else}
  <form action="send_bug" method="post" onsubmit="cleanContent()">
    {xsrf_token_field}
    <div>
      <select name="task_type">
        <option value="bug">Erreur</option>
        <option value="wish">Souhait</option>
        <option value="help">Aide/Dépannage</option>
      </select>
      &nbsp;&nbsp;Sujet&nbsp;: <input type="text" name="item_summary" id="flyspray_title" value="sur la page { $location }" size="50" maxlength="100"/>
      <textarea name="detailed_desc" id="flyspray_detail" cols="70" rows="10"
                style="width:100%;margin-top:10px;margin-bottom:10px;height:400px;display:block;"></textarea>
      <input type="hidden" name="page" value="{$smarty.server.HTTP_REFERER|default:$smarty.request.page}" />
      <div class="center">
        <input type="button" value="Abandonner" onclick="window.close()"/>
        <input type="submit" name="send" value="Envoyer"/>
      </div>
    </div>
  </form>
  <script type="text/javascript">
    {literal}
    $(document).ready(function() {
      var edited=false;
      $('#flyspray_detail')
        .focus(function() {
          if (edited == false) {
            $(this).val('')
                   .css('color', 'black')
                   .css('textAlign', 'left');
          }
        })
        .blur(function() {
          var value = $(this).val();
          if (value == '' || (value.toUpperCase() == value && value.toLowerCase() == value)) {
            $(this).val("** Explique ici le problème ou l'amélioration proposée **")
                   .css('color', 'gray')
                   .css('textAlign', 'center');
            edited = false;
          } else {
            edited = true;
          }
        })
        .blur();
    });
    {/literal}
  </script>
  {/if}
</fieldset>

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}

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

<form action="{$wiz_baseurl}/{$lookup[$current]}" method="post" id="prof_annu">
  {xsrf_token_field}
  <div>
    {icon name=information title="Voir ma fiche"} Tu peux consulter
    {if $isMe}ta{else}cette{/if} fiche telle qu'elle apparaît
    <a class="popup2" href="profile/{$profile->hrpid}?view=public">pour n'importe quel internaute</a>,
    <a class="popup2" href="profile/{$profile->hrpid}?view=ax">dans l'annuaire papier</a>{if $viewPrivate} ou
    <a class="popup2" href="profile/{$profile->hrpid}">pour les X</a>{/if}.
  </div>
  <div class="flags">
  {include file="include/flags.radio.tpl" disabled=true withtext=true val="novalue" name="profile_ex_pub"}
  </div>
  <div style="margin-top: 1em">
    {include file=$profile_page}
  </div>
  <div style="clear: both; margin-top: 1em" class="center">
    <input type="hidden" name="valid_page" value="{$current}" />
    <input type="submit" name="current_page" value="Valider les modifications" />
    {if $current neq count($lookup)-1}
    <input type="submit" name="next_page" value="Valider et passer à la page suivante" />
    {/if}
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

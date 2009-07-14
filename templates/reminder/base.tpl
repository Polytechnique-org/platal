{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

<div id="reminder">
  {if $previous_reminder}
    {include file="reminder/notification.tpl" previous_reminder=$previous_reminder}
  {/if}

  <fieldset class="warnings">
    <legend>
      {if $reminder->warning()}{icon name=error}{else}{icon name=information}{/if}
      &nbsp;{$reminder->title()}
    </legend>

    {if $reminder->template()}
      {include file=$reminder->template()}
    {else}
      <div style="margin-bottom: 0.5em">
        {$reminder->text()}
      </div>
      <div class="center">
        <a href="" onclick="Ajax.update_html('reminder', '{$reminder->baseurl()}/yes'); return false" style="text-decoration: none">
          {icon name=add} M'inscrire
        </a> -
        <a href="" onclick="Ajax.update_html('reminder', '{$reminder->baseurl()}/no'); return false" style="text-decoration: none">
          {icon name=delete} Ne pas m'inscrire
        </a> -
        <a href="" onclick="Ajax.update_html('reminder', '{$reminder->baseurl()}/dismiss'); return false" style="text-decoration: none">
          {icon name=cross} Décider plus tard
        </a>
        {if $reminder->info()}
          - <a class="popup2" href="{$reminder->info()}">{icon name=information} En savoir plus</a>
        {/if}
      </div>
    {/if}
  </fieldset>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

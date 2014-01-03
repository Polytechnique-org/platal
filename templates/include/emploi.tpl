{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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
{if $job->company || $job->sector || $job->subsector ||
  $job->subsubsector || $job->description || $job->user_email}
      <div class="adresse" style="float: left">
      	<table>
        {if $job->company || $job->user_site}
        <tr>
          <td><em>Ent/Org&nbsp;: </em></td>
          <td><strong>{if $job->company->url}<a href='{$job->company->url}'>{$job->company->name}</a>{else}{$job->company->name}{/if}
          {if $job->user_site} [<a href='{$job->user_site}'>Page perso</a>]{/if}</strong></td>
        </tr>
        {/if}
        {if count($job->terms)}
        <tr>
          <td><em>Mots-clefs&nbsp;: </em></td>
          <td><ul>
            {foreach from=$job->terms item=term}
            <li><strong>{$term->full_name}</strong></li>
            {/foreach}
          </ul></td>
        </tr>
        {/if}

        {if $job->description}
        <tr>
          <td><em>Fonction&nbsp;: </em></td>
          <td><strong>{$job->description}</strong></td>
        </tr>
        {/if}
        {if $job->user_email}
        <tr>
          <td><em>Email&nbsp;: </em></td>
          <td><strong>{$job->user_email}</strong></td>
        </tr>
        {/if}
        </table>
      </div>
{/if}

{* vim:set et sws=2 sts=2 sw=2 enc=utf-8: *}

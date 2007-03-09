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

<table style="float: right; text-align: center" id="letter_nav">
  <tr>
    {if $mm->prev() neq null}
    <td rowspan="2" style="vertical-align: middle">
      <a href="{$base}/show/{$mm->prev()}">
      {icon name=resultset_previous title="Lettre précédente"}Lettre précédente
      </a>
    </td>
    {/if}
    <td>
      [<a href="{$base}">Liste des lettres</a>]
    </td>
    {if $mm->next() neq null}
    <td rowspan="2" style="vertical-align: middle">
      <a href="{$base}/show/{$mm->next()}">
      Lettre suivante{icon name=resultset_next title="Lettre suivante"}
      </a>
    </td>
    {/if}
  </tr>
  {if $mm->last() neq null}
  <tr>
    <td>
      <a href="{$base}/show/{$mm->last()}">
        <img src="images/up.png" alt="" title="Liste des lettres" />Dernière lettre<img src="images/up.png" alt="" title="Liste des lettres" />
      </a>
    </td>
  </tr>
  {/if}
</table>


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

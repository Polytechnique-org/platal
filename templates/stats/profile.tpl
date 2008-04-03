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


<h1>
  Fiches les plus consultées
</h1>

<ul>
  <li>
    {if $period neq 'overall'}<a href="stats/profile/overall">{/if}
    Classement de tous les temps
    {if $period neq 'overall'}</a>{/if}
  </li>
  <li>
    {if $period neq 'year'}<a href="stats/profile/year">{/if}
    Classement sur l'année écoulée
    {if $period neq 'year'}</a>{/if}
 </li>
 <li>
    {if $period neq 'month'}<a href="stats/profile/month">{/if}
    Classement sur le mois écoulé
    {if $period neq 'month'}</a>{/if}
 </li>
 <li>
    {if $period neq 'week'}<a href="stats/profile/week">{/if}
    Classement sur la semaine écoulée
    {if $period neq 'week'}</a>{/if}
 </li>

</ul>

<table class="tinybicol">
  <tr>
    <th>Nom</th>
    {if hasPerms('admin')}<th>Consultations</th>{/if}
  </tr>
  {iterate from=$profiles item=profile}
  <tr class="{cycle values="pair,impair"}">
    <td><a href="profile/{$profile.forlife}" class="popup">{$profile.prenom} {$profile.nom} (X{$profile.promo})</a></td>
    {if hasPerms('admin')}<td class="right">{$profile.count}</td>{/if}
  </tr>
  {/iterate}
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

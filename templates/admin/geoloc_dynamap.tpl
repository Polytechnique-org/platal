{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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
  Administration de la dynamap
</h1>

<h2>
  Utiliser de nouvelles cartes
</h2>
  <form action='' method='post'>
    <p>
      <input type='text' name='url' value='{$smarty.request.url|default:"url des données des cartes"}' onfocus='select()' size='40'/>
	  <br/>
      <input type='submit' name='new_maps'/>
    </p>
  </form>
  
<h2>
  Placement des villes sur les cartes
</h2>
{if $nb_cities_not_on_map}
  <p>
    Il y a {$nb_cities_not_on_map} villes qui ne sont pas placées dans les cartes. [<a href='?fix=cities_not_on_map'>Réparer</a>]
  </p>
{else}
  <p> Toutes les villes de la base sont placées dans des cartes. </p>
{/if}
{if $no_smallest}
  <p>
    Il faut <a href='?fix=smallest_maps'>définir la plus petite carte</a> pour chaque ville (à ne faire qu'une fois quand on a placé toutes les villes).
  </p>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}

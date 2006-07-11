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

<h1>Année de sortie</h1>

{if $myorange}

<p>
  Tu recevras un mail dès que les changements demandés auront été effectués. 
  Encore merci de nous faire confiance pour tes e-mails !
</p>

{else}

<p>
  Afin de pouvoir être considéré(e) à la fois dans ta promotion d'origine et ta
  ou tes promotions d'adoption tu peux entrer ici ton année de sortie de l'école.
  Plus précisément, il s'agit de l'année d'entrée en quatrième année ou année d'application.
  Pour tes cocons d'origine (X{$promo}) il s'agit de l'année {math equation="a + b" a=$promo b=3}.
</p>

<br />

<form action="{rel}/profile/orange" method="post">
  <table class="bicol" cellpadding="4" summary="Année de sortie">
    <tr>
      <th>Année de sortie</th>
    </tr>
    <tr>
      <td class="center"><input type="text" name="promo_sortie" value="{$promo_sortie_old}" /></td>
    </tr>
    <tr>
      <td class="center"><input type="submit" name="submit" value="Envoyer" /></td>
    </tr>
  </table>
</form>

{/if}

<p>[<a href="profil.php">Revenir au profil</a>]</p>

{* vim:set et sw=2 sts=2 sws=2: *}

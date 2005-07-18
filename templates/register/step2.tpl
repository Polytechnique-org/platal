{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

<h1>Identification</h1>

<p>
<a href="?back=1">retour</a>
</p>

<form action="?" method="post">
  <table class="bicol" summary="Identification" cellpadding="3">
    {if $smarty.session.sub_state.promo >= 1996}
    <tr>
      <th colspan="2">matricule</th>
    </tr>
    <tr>
      <td class="titre">
        Matricule X :
      </td>
      <td>
        <input type="text" size="6" maxlength="6" name="mat" 
          value="{$smarty.request.matricule|default:$smarty.session.sub_state.mat}" />
      </td>
    </tr>
    <tr class="pair">
      <td></td>
      <td>
        6 chiffres terminant par le numéro d'entrée (ex: 960532 ou 101532)<br />
        Voir sur le GU ou un bulletin de solde pour trouver cette information<br /><br />
        Pour les élèves étrangers voie 2, il est du type :<br />
        Promotion: 1996, Matricule: 970XXX - Promotion: 2001, Matricule 102XXX.
      </td>
    </tr>
    {/if}
    <tr>
      <th colspan="2">
        Identification
      </th>
    </tr>
    <tr>
      <td class="titre"> 
        Nom<br />
        <span class="smaller">(Xettes mariées : nom à l'X)</span>
      </td>
      <td>
        <input type="text" size="20" maxlength="30" name="nom" value="{$smarty.request.nom}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Prénom
      </td>
      <td>
        <input type="text" size="15" maxlength="20" name="prenom" value="{$smarty.request.prenom}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Promotion
      </td>
      <td>
        <input type="text" size="4" readonly="readonly" value="{$smarty.session.sub_state.promo}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Continuer l'inscription" />
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}

{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: utilisateurs_edit.tpl,v 1.6 2004-10-24 14:41:14 x2000habouzit Exp $
 ***************************************************************************}


<h1>
  Editer la base de tous les X
</h1>
{dynamic}
{if $success eq "1"}
<p>
La modification de la table identification a été effectuée.
</p>
<p>
<a href="{$smarty.server.PHP_SELF}">Retour</a>
</p>
{else}
<p>
<strong>Attention</strong> la table d'identification contenant la liste des polytechniciens sera
modifiée !! (aucune vérification n'est faite)
</p>
<div class="center">
  <form action="{$smarty.server.PHP_SELF}" method="get">
    <table class="bicol" summary="Edition de fiche">
      <tr>
        <th colspan="2">
          Editer
        </th>
      </tr>
      <tr>
        <td class="titre">Prénom :</td>
        <td>
          <input type="text" size="40" maxlength="60" value="{$row.prenom}" name="prenomN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Nom :</td>
        <td>
          <input type="text" size="40" maxlength="60" value="{$row.nom}" name="nomN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Femme :</td>
        <td>
          <input type="checkbox" name="flag_femmeN" value="1"{if in_array("femme",explode(",",$row.flags))}checked{/if} />
        </td>
      </tr>
      <tr>
        <td class="titre">Promo :</td>
        <td>
          <input type="text" size="4" maxlength="4" value="{$row.promo}" name="promoN" />
        </td>
      </tr>
      <tr>
        <td class="titre">Décés :</td>
        <td>
          <input type="text" size="10" value="{$row.deces}" name="decesN" />
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <a href="http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$row.matricule_ax}" onclick="return popup(this)">Voir sa fiche sur le site de l'AX</a>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="hidden" name="xmat" value="{$smarty.request.xmat}" />
          <input type="submit" value="Modifier la base" name="submit" />
        </td>
      </tr>
    </table>
  </form>
</div>
{/if}
{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}

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
 ***************************************************************************}


<h1>Micropaiments</h1>

{dynamic}
{if $smarty.request.op eq "submit" and !$xorg_error->errs|count}

{$pay->form($montant)|smarty:nodefaults}

{else}

<form method="post" action="{$smarty.server.PHP_SELF}">
  <p> Si tu ne souhaites pas utiliser notre interface de
  télépaiement, tu peux virer directement la somme de ton choix sur notre compte
  30004 00314 00010016782 60. Nous veillerons à ce que ton paiement parvienne à
  son destinataire.  Pense toutefois à le préciser dans le motif du
  versement.
  <br /><br />
  </p>
  <table class="bicol">
    <tr>
      <th colspan="2">Effectuer un télépaiement</th>
    </tr>
    <tr>
      <td>Transaction</td>
      <td>
        <select name="ref" onchange="this.form.op.value='select'; this.form.submit();">
          {select_db_table table="`$prefix`paiements" valeur=$pay->id where=" WHERE FIND_IN_SET('old',flags)=0"}
        </select>
        {if $pay->url}
        <br />
        <a href="{$pay->url}">plus d'informations</a>
        {/if}
      </td>
    </tr>
    <tr>
      <td>Méthode</td>
      <td>
        <select name="methode">
          {select_db_table table="paiement.methodes" valeur=$methode}
        </select>
      </td>
    </tr>
    <tr>
      <td>Montant (euros)</td>
      <td><input type="text" name="montant" size="13" class='right' value="{$montant}" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>
        <input type="hidden" name="op" value="submit" />
        <input type="submit" value="Continuer" />
      </td>
    </tr>
  </table>
</form>

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

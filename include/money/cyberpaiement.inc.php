<?php
/***************************************************************************
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
 ***************************************************************************/

class CyberPayment
{
    // {{{ properties

    var $val;

    // }}}
    // {{{ constructor
    
    function CyberPayment($val)
    {
        $this->val = strtr(sprintf("%.02f", (float)$val), '.', ',');
    }

    // }}}
    // {{{ function form()

    function form(&$pay)
    {
        global $globals;

        $roboturl = str_replace("https://","http://",$globals->baseurl)
            ."/paiement/cyberpaiement_retour.php?uid={$_SESSION['uid']}&amp;CHAMPBPX";
        if (! isset($_COOKIE[session_name()])) {
            $returnurl .= "?".SID;
        }

        // on constuit la reference de la transaction
        $prefix = ($pay->flags->hasflag('unique')) ? str_pad("",15,"0") : rand_url_id();
        $fullref = substr("$prefix-xorg-{$pay->id}",-15);

        $e = $_SESSION['sexe'] ? 'e' : '';
        
        return <<<EOF
<table class="bicol">
  <tr>
    <th colspan="2">Paiement via CyberP@iement</th>
  </tr>
  <tr>
    <td><b>Transaction</b></td>
    <td>{$pay->text}</td>
  </tr>
  <tr>
    <td><b>Montant (euros)</b></td>
    <td>{$this->val}</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
      <form method="post" action="https://ecom.cimetz.com/telepaie/cgishell.exe/epaie01.exe">
      <div>
	<!-- infos commercant -->
	<input type="hidden" name="CHAMP000" value="510879" />
	<input type="hidden" name="CHAMP001" value="5965" />
	<input type="hidden" name="CHAMP002" value="5429159012" />
	<input type="hidden" name="CHAMP003" value="I" />
	<input type="hidden" name="CHAMP004" value="Polytechnique.org" />
	<input type="hidden" name="CHAMP005" value="$roboturl" />
	<input type="hidden" name="CHAMP006" value="Polytechnique.org" />
	<input type="hidden" name="CHAMP007" value="{$globals->baseurl}/" />
	<input type="hidden" name="CHAMP008" value="{$pay->mail}" />
	<!-- infos client -->
	<input type="hidden" name="CHAMP100" value="{$_SESSION['nom']}" />
	<input type="hidden" name="CHAMP101" value="{$_SESSION['prenom']}" />
	<input type="hidden" name="CHAMP102" value="." />
	<input type="hidden" name="CHAMP103" value="." />
	<input type="hidden" name="CHAMP104" value="{$_SESSION['bestalias']}@polytechnique.org" />
	<input type="hidden" name="CHAMP106" value="." />
	<input type="hidden" name="CHAMP107" value="." />
	<input type="hidden" name="CHAMP108" value="." />
	<input type="hidden" name="CHAMP109" value="." />
	<input type="hidden" name="CHAMP110" value="." />
	<!-- infos commande -->
	<input type="hidden" name="CHAMP200" value="$fullref" />
	<input type="hidden" name="CHAMP201" value="{$this->val}" />
	<input type="hidden" name="CHAMP202" value="EUR" />
	<!-- infos divers -->
	<input type="hidden" name="CHAMP900" value="01" />
	<input type="submit" value="Valider" />
      </div>
      </form>
    </td>
  </tr>
</table>

<p>
En cliquant sur "Valider", tu seras redirigé$e vers le site de la BP Lorraine Champagne, où il te
sera demandé de saisir ton numéro de carte bancaire.  Lorsque le paiement aura été effectué, tu
recevras une confirmation par email.
</p>
EOF;
    }

    // }}}
}

$api = 'CyberPayment';

?>

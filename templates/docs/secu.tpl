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
        $Id: secu.tpl,v 1.6 2004-08-31 11:25:40 x2000habouzit Exp $
 ***************************************************************************}


<script language="JavaScript" type="text/javascript">
{literal}
  function popUp(url) {
    sealWin=window.open(url,"win",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=450');
    self.name = "mainWin"; 
  }
{/literal}
</script>

<div class="center">
  <a
    href="javascript:popUp('https://servicecenter.verisign.com/cgi-bin/Xquery.exe?Template=authCertByIssuer&amp;remote_host=https://www.certplus.com/server/cgi-bin/haydn.exe&amp;form_file=../fdf/authCertByIssuer.fdf&amp;issuerSerial=2a7ca007e2dd1cfe5cb7c705cf197084')">
    <img src="{"images/SceauCertplus_petit.png"|url}" alt=" [ SCEAU CERTPLUS ] " />
  </a>
  &nbsp;&nbsp;&nbsp;
  <a href="http://www.certplus.com/">
    <img src="{"images/verisign.png"|url}" alt=" [ LOGO CERTPLUS ] ">
  </a>
</div>
<p>
La sécurité des connexions sur le site Polytechnique.org est garantie par un
certificat fourni par <a href="http://www.certplus.com/">Certplus</a>.
</p>
<p>
Créé en 1998 par quatre actionnaires fondateurs, Gemplus (49%), France Telecom (17%),
EADS (Aérospatiale Matra) (17%) et VeriSign, rejoints début 2000 par la CIBP
(Confédération Internationale des Banques Populaires), Certplus est le
premier opérateur français de services de confiance. Le métier de Certplus
est d'accompagner les entreprises et les administrations dans la mise en
place de leurs espaces de confiance, pour répondre à leurs besoins de
messagerie sécurisée (signature et chiffrement), de contrôle d'accès et
confidentialité (Intranet, Extranet, VPN, applications informatiques), et de
signature électronique (intégrité et non-répudiation de documents, formulaires 
et déclarations en ligne).
</p>
<p>
Lorsque l'adresse de la page commence par <strong>https</strong> vous êtes assurés d'une 
communication cryptée avec le serveur. 
</p>
<p>
L'association Polytechnique.org tient tout particulièrement à remercier <strong>Laurent 
  Malhomme</strong> (X92), <strong>Matthieu Bergot</strong> (X89) et <strong>Cyril Dujardin</strong> (X95)
grâce à qui ce partenariat a pu être établi puis entretenu afin d'assurer
la sécurité du site.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}

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
        $Id: doc_carva.tpl,v 1.5 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}


<div class="rubrique">
  Redirection de page WEB
</div>

<div class="ssrubrique">
  Pourquoi une redirection de page WEB ?
</div>

<p>
  Dans la lignée du service de redirection d'emails de <strong>Polytechnique.org</strong>, 
  il est possible de faire pointer 
{if $smarty.session.alias}
  l'adresse <strong>http://www.carva.org/{dyn s=$smarty.session.username}</strong>
{else}
  les adresses <strong>http://www.carva.org/{dyn s=$smarty.session.username}</strong>
  et <strong>http://www.carva.org/{$smarty.session.alias}</strong> ";
{/if}
  vers la page WEB de ton choix.
</p>
<p>
  La redirection fournie par <strong>carva.org</strong> t'offre ainsi une adresse Internet 
  simple et immuable pour référencer ton site personnel, quelle que soit la solution 
  d'hébergement retenue (free.fr, wanadoo.fr, ifrance.com, etc.).
</p>
<div class="ssrubrique">
  Pourquoi le nom de domaine carva.org ?
</div>
<p>
  Dans le jargon de l'école, un 'carva' signifiait un 'X' lorsque celle-ci était 
  située sur la montagne Ste Geneviève (<a href="javascript:x()" onclick="popWin('../aide.php#carva','remplissage','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=500')">
    voir la FAQ à ce sujet</a>). 
</p>
<br />

<div class="rubrique">
  <a name="charte"></a>Conditions d'usage de la redirection de page WEB
</div>
<p>
  L'utilisateur s'engage à ce que le contenu du site référencé soit en conformité 
  avec les lois et règlements en vigueur et d'une manière générale ne porte pas 
  atteinte aux droits des tiers.
</p>
<hr />
<p>
  Notamment, mais non exclusivement, l'utilisateur s'engage à ce que le contenu 
  du site référencé :
</p>
<ul>
  <li> 
    ne porte pas atteinte ou ne soit pas contraire à l'ordre public ou aux bonnes 
    m&oelig;urs ou ne puisse pas heurter la sensibilité des mineurs ;
  </li>
  <li> 
    ne porte pas atteinte de quelque manière que ce soit aux droits, à la 
    réputation, à la vie privée de tiers ;
  </li>
  <li>
    ne contienne pas de propos ou d'images dénigrantes, diffamatoires ou portant 
    atteinte à l'image ou à la réputation d'une marque ou d'une quelconque personne 
    physique ou morale de quelque que manière que ce soit ;
  </li>
  <li>
    ne présente pas de caractère pornographique ou pédophile ;
  </li>
  <li>
    ne propose pas la vente, le don ou l'échange de biens volés ou issus d'un 
    détournement, d'une escroquerie, d'un abus de confiance ou de tout autre 
    infraction pénale ;
  </li>
  <li>
    ne propose pas la vente, le don ou l'échange de biens pouvant présenter de 
    vices et de défauts de fabrication de nature à causer un danger pour les 
    personnes et les biens ;
  </li>
  <li>
    ne porte pas atteinte aux droits de propriété intellectuelle protégés par la loi ;
  </li>
  <li>
    n'incite pas à la haine, à la violence, au suicide, au racisme, à l'antisémitisme, 
    à la xénophobie, ne fasse pas l'apologie des crimes de guerre ou des crimes contre 
    l'humanité ;
  </li>
  <li>
    n'incite pas à la discrimination d'une personne ou d'une groupe de personne en 
    raison de son appartenance à une ethnie ou à une religion ;
  </li>
  <li>
    ne porte pas atteinte à la sécurité ou à l'intégrité d'un Etat ou d'un territoire, 
    quel qu'il soit ;
  </li>
  <li>
    n'incite pas à commettre un crime, un délit ou un acte de terrorisme ;
  </li>
  <li>
    ne permette pas à des tiers de se procurer des logiciels piratés ou des numéros 
    de série de logiciels, ou tout logiciel pouvant nuire ou porter atteinte, de 
    quelque manière que ce soit, aux droits ou aux biens des tiers.
  </li>
</ul>
<p>
  Cette liste doit être considérée comme non limitative.
</p>
<p>
  Polytechnique.org ne peut être considéré comme responsable du contenu des pages 
  WEB redirigées.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}

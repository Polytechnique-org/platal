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
        $Id: step2.tpl,v 1.4 2004-10-24 14:41:13 x2000habouzit Exp $
 ***************************************************************************}


{include file="applis.js.tpl"}

<h1>
  Formulaire de pré-inscription
</h1>

{dynamic}

<p class="erreur">{$erreur|smarty:nodefaults|nl2br}</p>

<form action="{"inscription/step3.php"|url}" method="post">
  {if $homonyme}
  <p>
  Ton adresse sera : <strong>{$forlife}@polytechnique.org</strong>
  </p>
  {else}
  <p>
  Tu n'as pour le moment aucun homonyme dans notre base de données, nous allons
  donc te donner l'adresse <strong>{$mailorg}@polytechnique.org</strong>, en plus
  de ton adresse à vie <strong>{$forlife}@polytechnique.org</strong>.
  Sache que tu peux perdre cette adresse si un homonyme s'inscrit (même si cela reste assez rare).
  </p>
  {/if}
  
  <p>
  Elle pointera sur les e-mails de ton choix, indique-s-en un pour commencer
  (tu pourras indiquer les autres une fois l'inscription terminée).
  Attention, il doit <strong>impérativement être correct</strong> pour que nous puissions 
  te recontacter.
  </p>
  <table class="bicol" cellpadding="3" cellspacing="0" summary="Préinscription">
    <tr>
      <th colspan="2">
        Contact et sécurité
      </th>
    </tr>
    <tr>
      <td class="titre">
        E-mail<br />
        <span class="smaller">(Première redirection)</span>
      </td>
      <td>
        <input type="text" size="35" maxlength="50" name="email" value="{$smarty.request.email}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Date de naissance<br />
        <span class="smaller">(Format JJMMAAAA)</span>
      </td>
      <td>
        <input type="text" size="8" maxlength="8" name="naissance"  value="{$smarty.request.naissance}" />
        (demandée si perte de mot de passe)
      </td>
    </tr>
    <tr>
      <th colspan="2">
        Fiche personnelle
      </th>
    </tr>
    <tr>
      <td class="titre">
        Identité
      </td>
      <td>
        {$prenom} {$nom}, X{$smarty.request.promo}
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nom de mariage
      </td>
      <td>
        Si ton nom de mariage est différent de {$nom}, tu pourras le préciser dans
        ton profil une fois que tu auras confirmé ton inscription.
      </td>
    </tr>
    <tr>
      <td class="titre">
        Nationalité
      </td>
      <td>
        <select name="nationalite">
          {select_db_table table="nationalites" valeur=$smarty.request.nationalite}
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Appli graduate
      </td>
      <td>
        <select name="appli_id1" onchange="fillType(this.form.appli_type1, this.selectedIndex-1);">
          {applis_options selected=$smarty.request.appli_id1}
        </select>
        <br />
        <select name="appli_type1">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--        
          fillType(document.infos.appli_type1, document.infos.appli_id1.selectedIndex-1);
          selectType(document.infos.appli_type1, '{$smarty.request.appli_type1}');
          //-->       
        </script>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Post-graduate
      </td>
      <td>
        <select name="appli_id2" onchange="fillType(this.form.appli_type2, this.selectedIndex-1);">
          {applis_options selected=$smarty.request.appli_id2}
        </select>
        <br />
        <select name="appli_type2">
          <option value=""></option>
        </select>
        <script type="text/javascript">
          <!--        
          fillType(document.infos.appli_type2, document.infos.appli_id2.selectedIndex-1);
          selectType(document.infos.appli_type2, '{$smarty.request.appli_type2}');
          //-->       
        </script>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="hidden" value="{$envoidirect|default:$smarty.request.envoidirect}" name="envoidirect" />
        <input type="hidden" value="{$smarty.request.nom}" name="nom" />
        <input type="hidden" value="{$smarty.request.prenom}" name="prenom" />
        <input type="hidden" value="{$smarty.request.promo}" name="promo" />
        <input type="hidden" value="{$smarty.request.matricule}" name="matricule" />
        <input type="submit" value="Terminer la pré-inscription" name="submit" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

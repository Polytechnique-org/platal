{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

{include file="register/breadcrumb.tpl"}

<h1>Bravo !!!</h1>

<p>
Tu as maintenant accès au site !!!<br />
Ton adresse électronique à vie <strong>{$smarty.session.forlife}@{#globals.mail.domain#}</strong> est déjà ouverte, essaie-la !
</p>
<p class="smaller">
  <strong>Remarque&nbsp;:</strong> m4x.org est un domaine "discret" qui veut dire "mail for X" et
  qui comporte exactement les mêmes adresses que le domaine polytechnique.org.
</p>


<h2>Mot de passe</h2>

{if $mdpok}

<p class="erreur">
ton mot de passe a bien été mis à jour !
</p>

{else}

<p>
Tu as recu un mot de passe par défaut, si tu souhaites en changer, tu peux le faire ici&nbsp;:
</p>

<form action="register/success" method="post" id="changepass">
  <table class="tinybicol" cellpadding="3" cellspacing="0">
    <tr>
      <th colspan="2">
        Saisie du nouveau mot de passe
      </th>
    </tr>
    <tr>
      <td class="titre">
        Nouveau mot de passe&nbsp;:
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois&nbsp;:
      </td>
      <td>
        <input type="password" size="10" maxlength="10" name="nouveau2" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Changer" name="submitn" onclick="EnCryptedResponse(); return false;" />
      </td>
    </tr>
  </table>
</form>

<form action="register/success" method="post" id="changepass2">
<div><input type="hidden" name="response2"  value="" /></div>
</form>

<p class="smaller">
<strong>N'oublie pas&nbsp;:</strong> si tu perds ton mot de passe, nous n'avons aucun engagement, en
particulier en termes de rapidité, mais pas seulement, à te redonner accès au
site. Cela peut prendre plusieurs semaines, les pertes de mot de passe sont
traitées avec la priorité minimale.
</p>

{/if}

<h2>Rejoindre la communauté</h2>

<form action='register/save' method='post'>
  <p>
  Pour rejoindre la communauté des X sur le web, nous te convions le plus vivement à remplir ton profil !!!
  </p>

  <p>
  Cette fonctionnalité n'est pas du tout redondante avec l'annuaire de l'AX, car nous pouvons synchroniser pour toi
  les données qu'il contient&nbsp;:
  </p>

  <dl>
    <dt><input type="checkbox" value="1" checked="checked" name="send_to_ax" disabled="disabled" /> vers l'AX</dt>
    <dd>
      tu peux choisir dans ton profil sur Polytechnique.org d'automatiquement transmettre à l'AX des parties de ta fiche,
      au fur et à mesure que tu les modifies.
      (Les données transmises seront <strong>uniquement</strong> celles que tu as décidé de transmettre).
    </dd>
    <dt><input type='checkbox' value='1' checked="checked" name='register_from_ax_question' /> depuis l'AX</dt>
    <dd>
    nous mettons à jour ta fiche depuis les données de l'annuaire de l'AX si tu le souhaites. <br/>
    (si tu ne le souhaites pas, décoche la case ci-dessus)
    </dd>
  </dl>

  <p>
  Pour profiter pleinement de ta nouvelle inscription, nous te proposons
  </p>

  <dl>
    <dt><input type='checkbox' value='1' checked="checked" name='add_to_nl' /> lettre mensuelle*</dt>
    <dd>
      de recevoir chaque mois la lettre mensuelle de Polytechnique.org contenant les activités et nouvelles de la communauté des X.
    </dd>
    <dt><input type="checkbox" value="1" checked="checked" name="add_to_ax" /> envois de l'AX*</dt>
    <dd>
      de recevoir les informations importantes de l'AX.
    </dd>
    <dt><input type='checkbox' value='1' checked="checked" name='add_to_promo' /> ta promo*</dt>
    <dd>
      de recevoir les informations plus spécifiques de ta promotion pour pouvoir participer plus facilement aux événements
      qu'elle organise. Nous t'inscrivons donc dans le groupe de la promotion {$smarty.session.promo}.
    </dd>
  </dl>

  {if $lists|@count neq 0}
  <p>
    Des camarades souhaitent que tu t'inscrives aux listes suivantes&nbsp;:
  </p>

  <dl>
    {foreach from=$lists key=list item=details}
    <dt><input type='checkbox' value='1' checked="checked" name="sub_ml[{$list}]" /> {$list}*&nbsp;: {$details.desc}</dt>
    {if $details.info}
    <dd>
      {$details.info|nl2br}
    </dd>
    {/if}
    {/foreach}
  </dl>
  {/if}

  <p class="smaller">* décoche les cases si tu ne souhaites pas être inscrit à la liste de diffusion correspondante</p>

  <div class="center">
    <input type="submit" value="Rejoindre les X sur le Net !" class="erreur" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}

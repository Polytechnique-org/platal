{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
  Création d'une liste de diffusion
</h1>

{if !$created}

<p>
N'importe qui peut faire la demande de création d'une liste de diffusion, il suffit pour cela d'être au
moins 4 polytechniciens inscrits sur le site, et de fournir les informations suivantes concernant la
liste&nbsp;:
</p>

<form action='lists/create' method='post' enctype="multipart/form-data">
  {xsrf_token_field}
  <table class='bicol' cellspacing='0' cellpadding='2'>
    <tr>
      <th colspan='5'>Caractéristiques de la liste</th>
    </tr>
    <tr>
      <td class='titre'>C'est une liste pour&nbsp;:</td>
      <td colspan='2'><label><input type='radio' name='asso' value='groupex'
        {if $smarty.post.asso eq 'groupex' && $smarty.post}checked='checked'{/if} />un groupe X</label></td>
      <td colspan='2'><label><input type='radio' name='asso' value=''
        {if !$smarty.post.asso || !$smarty.post}checked='checked'{/if} />une liste de portée générale ou d'amis</label></td>
    </tr>
    {if $young_promo}
    <tr>
      <td></td>
      <td colspan='2'><label><input type='radio' name='asso' value='binet'
        {if $smarty.post.asso eq 'binet' && $smarty.post}checked='checked'{/if} />un binet</label></td>
      {if $very_young_promo}
      <td colspan='2'><label><input type='radio' name='asso' value='alias'
        {if $smarty.post.asso eq 'alias' && $smarty.post}checked='checked'{/if} />un alias psc&hellip;</label></td>
      {else}
      <td colspan='2'></td>
      {/if}
    </tr>
    {/if}
    <tr class='promo'>
      <td class='titre'>Promotion&nbsp;:</td>
      <td><input type='text' name='promo' size='4' maxlength='4'
        {assign var="profile" value=$smarty.session.user->profile()}
        {if $smarty.post.promo}value='{$smarty.post.promo}'{else}value='{$profile->yearpromo()}'{/if} /></td>
      <td class='smaller' colspan='3'>Par exemple&nbsp;: 2004</td>
    </tr>
    <tr class='groupex'>
      <td class='titre'>Nom du groupe X&nbsp;:</td>
      <td colspan='4'>
        <input type='text' name='groupex_name' value='{$smarty.post.groupex_name}' /><br />
        <span class='smaller'><strong>Attention&nbsp;:</strong> le nom du groupe doit être écrit comme sur <a
        href="http://www.polytechnique.net">Polytechnique.net</a>.</span>
      </td>
    </tr>
    <tr>
      <td class='titre'>Adresse&nbsp;souhaitée&nbsp;:</td>
      <td colspan='4'>
        <input type='text' name='liste' size='15' value='{$smarty.post.liste}' />@<span class='promo'><span id='promotion'></span>.</span><span class='groupex'><span class='smaller'>diminutifdugroupe</span>.</span>polytechnique.org
      </td>
    </tr>
    <tr>
      <td class='titre'>Sujet (bref)&nbsp;:</td>
      <td colspan='4'>
        <input type='text' name='desc' size='50' value='{$smarty.post.desc}' />
      </td>
    </tr>
    <tr style="white-space: nowrap">
      <td class='titre'>Propriétés&nbsp;:</td>
      <td>visibilité&nbsp;:</td>
      <td><label><input type='radio' name='advertise' value='0'
        {if $smarty.post.advertise eq 0 && $smarty.post}checked='checked'{/if} />publique</label></td>
      <td><label><input type='radio' name='advertise' value='1'
        {if $smarty.post.advertise neq 0 || !$smarty.post}checked='checked'{/if} />privée</label></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td class='smaller' colspan='4'>(est-ce que les non membres peuvent voir l'existence de cette liste&nbsp;?)</td>
    </tr>
    <tr>
      <td></td>
      <td>diffusion&nbsp;:</td>
      <td><label><input type='radio' name='modlevel' value='0'
        {if !$smarty.post.modlevel}checked='checked'{/if} />libre</label></td>
      <td><label><input type='radio' name='modlevel' value='1'
        {if $smarty.post.modlevel eq 1}checked='checked'{/if} />restreinte</label></td>
      <td><label><input type='radio' name='modlevel' value='2'
        {if $smarty.post.modlevel eq 2}checked='checked'{/if} />modérée</label></td>
    </tr>
    <tr>
      <td></td>
      <td class='smaller' colspan='4'>(l'envoi d'un email à cette liste est-il libre, modéré
      lorsque l'expéditeur n'appartient pas à la liste ou modéré dans tous les cas&nbsp;?)</td>
    </tr>
    <tr>
      <td></td>
      <td>inscription&nbsp;:</td>
      <td><label><input type='radio' name='inslevel' value='0'
        {if $smarty.post.inslevel eq 0 && $smarty.post}checked='checked'{/if} />libre</label></td>
      <td><label><input type='radio' name='inslevel' value='1'
        {if $smarty.post.inslevel neq 0 || !$smarty.post}checked='checked'{/if} />modérée</label></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td class='smaller' colspan='4'>(détermine si les inscriptions à la liste sont modérées
      par les modérateurs de la liste ou non.)</td>
    </tr>
    <tr><th colspan='5'>Membres et gestionnaires</th></tr>
    <tr>
      <td class='titre'>Gestionnaires&nbsp;:</td>
      <td colspan='4'>
        <input type='hidden' name='owners' value='{$owners}' />
        {$owners|nl2br|default:'<span class="erreur">pas de gestionnaires</span>'}
        <br />
        <input type='text' name='add_owner' />
        <input type='submit' name='add_owner_sub' value='Ajouter' />
      </td>
    </tr>
    <tr>
      <td class='titre'>Membres&nbsp;:</td>
      <td colspan='4'>
        <input type='hidden' name='members' value='{$members}' />
        {$members|nl2br|default:'<span class="erreur">pas de membres</span>'}
        <br />
        <input type='text' name='add_member' /><br />
        <input type="file" name="add_member_file" /><br />
        <input type='submit' name='add_member_sub' value='Ajouter' />
      </td>
    </tr>
    <tr>
      <td colspan='5'>
        <small>
          Tu peux entrer une liste de membres en entrant plusieurs adresses séparées par des espaces, des virgules ou des point-virgules.
          Tu peux aussi fournir un fichier avec une adresse email par ligne.
        </small>
      </td>
    </tr>
  </table>
  <script type="text/javascript">//<![CDATA[
    {literal}
    $(function() {
      $(":radio[name=asso]").change(function() {
        var asso = $(":radio[name=asso]:checked").val();
        if ((asso == "binet") || (asso == "alias")) {
          $(".groupex").hide();
          $(".promo").show();
        } else if (asso == "groupex") {
          $(".promo").hide();
          $(".groupex").show();
        } else {
          $(".groupex").hide();
          $(".promo").hide();
        }
      }).change();
    });
    $(function() {
      $(":text[name=promo]").change(function () {
        var str = $(":text[name=promo]").val();
        $("span#promotion").text(str);
      }).change();
    });
    {/literal}
  // ]]></script>
  <p>
  La création de la liste sera soumise à un contrôle manuel avant d'être validée. Ce contrôle a
  pour but notamment de vérifier qu'il n'y aura pas ambiguité entre les membres de la liste et son
  identité. Exemple&nbsp;: n'importe qui ne peut pas ouvrir pointgamma@polytechnique.org, il ne suffit
  pas d'être le premier à le demander.
  </p>
  <p>
  La liste est habituellement créée dans les jours qui suivent la demande sauf exception. Pour plus
  d'informations écris-nous à l'adresse {mailto address='listes@polytechnique.org'} en mettant dans
  le sujet de ton email le nom de la liste souhaité afin de faciliter les échanges d'emails
  ultérieurs éventuels.
  </p>
  <div class='center'>
    <br />
    <input type='submit' name='submit' value='Soumettre' />
  </div>
</form>

{/if}


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}

{* $Id: referent.tpl,v 1.4 2004-08-24 12:23:40 x2000habouzit Exp $ *}

{literal}
<script language="JavaScript" type="text/javascript">
  <!-- Begin
  function showPage( pNumber ) {
    document.form_result.page_courante.value = pNumber;
    document.form_result.submit();
  }
  // End -->
</script>
{/literal}

<div class="rubrique">
  Rechercher un camarade pouvant m'aider à orienter mon parcours professionnel
</div>
{dynamic}
{if $recherche_trop_large}
<p class="normal">
Les critères de recherche que tu as rentrés n'ont pas produit de résultats,
sans doute car ta requête était trop générale. Nous t'invitons à
<a href="referent.php">procéder à une nouvelle recherche</a>, en essayant
d'être plus précis.
</p>
{elseif $resultats}
<form action="{$smarty.server.PHP_SELF}" method="POST" name="form_result">
  <input type="hidden" name="pays" value="{$pays_selectionne}" />
  <input type="hidden" name="expertise" value="{$expertise_champ}" />
  <input type="hidden" name="secteur" value="{$secteur_selectionne}" />
  <input type="hidden" name="ss_secteur" value="{$ss_secteur_selectionne}" />
  <input type="hidden" name="page_courante" value="1" />
  <input type="hidden" name="Chercher" value="1" />
  <table class="rechresult" cellpadding="0" cellspacing="0" summary="Résultats">
    {section name="resultat" loop=$personnes}
    <tr>
      <td class="rechnom">
        {$personnes[resultat].nom} {$personnes[resultat].prenom}
      </td>
      <td class="rechdetails">
        <span class="rechdiplo">X{$personnes[resultat].promo}</span>
      </td>
      <td class="rechdetails" style="width:15%">
        <a style="font-size: smaller;" href="javascript:x()"  onclick="popWin('fiche.php?user={$personnes[resultat].username}')">voir sa fiche</a>
      </td>
      <td class="rechdetails" style="width:25%">
        <a class="smaller" href="javascript:x()"  onclick="popWin('fiche_referent.php?user={$personnes[resultat].username}')">voir sa fiche référent</a>
      </td>
    </tr>
    <tr>
      <td class="rechtitreitem">Expertise :</td>
      <td class="rechitem" colspan="2">{$personnes[resultat].expertise|nl2br}</td>
    </tr>
    <tr>
      <td>
        &nbsp;
      </td>
    </tr>
    {/section}
  </table>
  <br />
  <span style="font-size: normal;">Pages&nbsp;:&nbsp;
    {section name="page_number" start=1 loop=$nb_pages_total+1}
    {if $smarty.section.page_number.index == $page_courante}
    {$page_courante} {else}
    <a href="javascript:showPage({$smarty.section.page_number.index})">{$smarty.section.page_number.index} </a> 
    {/if}
    {/section}
  </span>
</FORM>
{/if}
{if $show_formulaire}
<span class="erreur">
  Si tu utilises ce service pour la première fois, lis attentivement le texte
  qui suit.
</span>
<p class="normal">
En <a href="profil.php">renseignant sa fiche dans l'annuaire</a>, chacun
d'entre nous a la possibilité de renseigner, dans la section "Mentoring",
s'il accepte de recevoir des messages de la part de camarades qui pourraient
souhaiter lui poser quelques questions et recevoir quelques conseils.<br />
Ces informations sont rentrées par chacun sur la base du volontariat et sont
totalement déclaratives. Chaque X qui complète cette rubrique accepte alors
de recevoir un courrier électronique des jeunes camarades qui sont en train
de bâtir leur projet professionnel, mais aussi des moins jeunes qui cherchent
à réorienter leur carrière. Bien entendu, chacun se réserve le droit de ne
pas donner suite à une sollicitation !<br />
Pour que ce système soit profitable, il est nécessaire que dans ta recherche
de conseils professionnels, tu agisses sagement, en évitant de contacter
un trop grand nombre de camarades. De même, pense bien que les quelques
personnes que tu vas éventuellement contacter suite à ta recherche
accepteront éventuellement de t'aider et de te guider <strong>sur la base du
  volontariat</strong>. Il va de soi que plus ton comportement lors de votre
contact sera éthique et reconnaissant, plus cette pratique de conseil
inter-générations sera encouragée et bien perçue par ceux qui la pratiquent.
<br />
Nous avons peiné à trouver un nom pour désigner ceux qui sont volontaires
pour guider les camarades qui en ressentent le besoin : nous avons finalement
retenu le terme de <em>mentors</em> pour désigner ceux qui sont prêts à aider de
manière suivie un camarade plus jeune, à plusieurs moments de sa carrière,
et avons appelé <em>référents</em> ceux qui s'impliquent plutôt en tant que
"relai d'informations", dans le sens où ils ont vécu des expériences
professionnelles susceptibles d'intéresser certains d'entre nous (expérience
de stage ou d'emploi à l'étranger), sans forcément souhaiter consacrer
autant de temps à quelqu'un que le ferait un mentor attentionné.
La recherche proposée ici permet de trouver les deux types d'aide.
</p>

<p class="normal">
Dans le formulaire ci-dessous, tu peux rechercher des avis en fonction des
domaines de compétence recherchés, des cultures professionnelles des pays
connues par le référent, et enfin une dernière case te permettra de faire
une recherche par mots-clefs.<br />
Nous t'incitons à prendre plutôt 2 ou 3 contacts qu'un seul, cela te
permettant certainement d'avoir une vision des choses plus complète.
</p>

<p class="normal">
Actuellement, {$mentors_number} mentors et référents se sont déclarés sur Polytechnique.org.
</p>

<form action="{$smarty.server.REQUEST_URI}" method="post" name="form_ref">
  <table cellpadding="0" cellspacing="0" summary="Formulaire de recherche de referents" class="bicol">
    <tr class="impair">
      <td class="bicoltitre">
        Secteur de compétence <br /> du référent
      </td>
      <td >
        <select name="secteur" OnChange="javascript:submit()">
          {html_options options=$secteurs selected=$secteur_selectionne}
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td class="bicoltitre">
        Sous-Secteur
      </td>
      <td >
        <select name="ss_secteur">
          {html_options options=$ss_secteurs selected=$ss_secteur_selectionne}
        </select>
      </td>
    </tr>
    <tr class="impair">
      <td class="bicoltitre">
        Pays bien connu du référent
      </td>
      <td >
        <select name="pays">
          {html_options options=$pays selected=$pays_selectionne}
        </select>
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2">
        &nbsp;
      </td>
    </tr>
    <tr class="impair">
      <td class="bicoltitre">
        Expertise (rentre un ou plusieurs mots clés)
      </td>
      <td >
        <input type="text" name="expertise" size="30" value="{$expertise_champ}" />
      </td>
    </tr>
  </table>
  <div style="text-align: center; margin-top: 1em;">
    <input type="submit" value="Chercher" name="Chercher" />
  </div>
</form>

{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

{* $Id: evenements.tpl,v 1.1 2004-07-19 13:35:35 x2000habouzit Exp $ *}

<div class="rubrique">
  Proposition d'information événementielle
</div>

{dynamic}

{if $action eq "proposer"}

<p class="normal">
Voici ton annonce :
</p>

<table class="bicol" width="98%">
  <tr>
    <th>{$titre|nl2br}</th>
  </tr>
  <tr>
    <td>{$texte|nl2br}</td>
  </tr>
</table>

<p class="normal">
Ce message est à destination
{if $promo_min || $promo_max}
des promotions {if $promo_min}X{$promo_min}{/if} {if $promo_max}jusqu'à X{$promo_max}{else}et plus{/if}
{else}
de toutes les promotions
{/if}
et sera affiché sur la page d'accueil jusqu'au {$peremption|date_format:"%e %b %Y"}
</p>

{if $validation_message}
<p class="normal">
Tu as ajouté le message suivant à l'intention du validateur : {$validation_message|nl2br}
</p>
{/if}

<form action="{$smarty.request.PHP_SELF}" method="post" name="evenement_nouveau">
  <input type="hidden" name="titre" value="{$titre|escape}" />
  <input type="hidden" name="texte" value="{$texte|escape}" />
  <input type="hidden" name="promo_min" value="{$promo_min}" />
  <input type="hidden" name="promo_max" value="{$promo_max}" />
  <input type="hidden" name="peremption" value="{$peremption}" />
  <input type="hidden" name="validation_message" value="{$validation_message|escape}" />
  <input type="submit" name="action" value="Confirmer" />
  <input type="submit" name="action" value="Modifier" />
</form>


{elseif $action eq "confirmer"}

{if $ok}
<p class="normal">
Ta proposition a bien été enregistrée, un administrateur va se charger de la valider aussi rapidement que possible.
</p>
<p class="normal">
Merci pour ta contribution à la vie du site!
</p>
<p class="normal">
<a href="login.php">Retour à la page d'accueil</a>
</p>
{else}
<p class="erreur">
Une erreur s'est produite pendant l'enregistrement de ta proposition.  Merci de nous <a href="contacts.php">contacter</a>!
</p>
{/if}

{else}

{include file="include/form.evenement.tpl"}

{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

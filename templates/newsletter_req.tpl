{* $Id: newsletter_req.tpl,v 1.1 2004-02-09 17:36:44 x2000habouzit Exp $ *}

<div class="rubrique">
  Proposer un article pour la newsletter
</div>

<p class="normal">
  La newsletter mensuelle est un excellent moyen de faire passer une 
  information. Nous devons cependant nous astreindre à certaines règles
  dans la rédaction pour en conserver la qualité et l'efficacité.
</p>
<ul>
  <li>
    Longueur maximale du texte justifié (hors téléphone, adresses, liens
    internet) : <strong>8 lignes de 68 caractères</strong>
  </li>
  <li>
    Les liens internet (URL, mail) et adresses, téléphones, apparaîtront
    en-dessous pour plus de clarté
  </li>
  <li>
    L'équipe de rédaction se réserve le droit de modifier la mise en 
    forme des articles
  </li>
</ul>

{dynamic}
{if $smarty.request.action}
  {if $erreur}
  {$erreur}
  {/if}
  
  {if $preview}
    <p class="normal">
      Le texte de ton annonce aura sensiblement l'allure suivante :
    </p>
    <div styleclass="center">
      <table class="bicol">
        <tr>
          <td "padding: 1em;">
            <tt>
              &lt;------------------------------------------------------------------&gt;<br />
              <br />
              {$preview|replace:" ":"&nbsp;"|nl2br}
              <br />
              <br />
              &lt;------------------------------------------------------------------&gt;
            </tt>
          </td>
        </tr>
      </table>

    {if $sent}
    <p class="erreur">
      Ton annonce a été envoyée à l'équipe de rédaction. Merci de ta contribution !
    </p>
    {elseif $nb_lines<9}
    <p class="normal">
    Félicitations, ton article respecte les règles de pagination de la 
    newsletter !!! Il pourra cependant être revu en fonction des 
    nécéssités de la newsletter.
    </p>
    <p class="normal">
    Tu peux le soumettre à l'équipe de validation en validant ta demande.
    Tu seras recontacté par mail par un rédacteur pour te confirmer la
    bonne récéption de ta demande.
    </p>
    <form action="{$smarty.server.PHP_SELF}" method="POST">
      <input type="hidden" name="titre" value="{$titre}" />
      <input type="hidden" name="article" value="{$article}" />
      <input type="hidden" name="bonus" value="{$bonus}" />
      <input type="submit" name="action" value="valider" />
    </form>
    <p class="normal">
    Si tu n'es pas satisfait de ton annonce, tu peux la retravailler :
    </p>
    {elseif $nb_lines>9}
    <p class="erreur">
      Ton annonce est trop longue, il faut que tu la modifie pour qu'elle fasse moins de huit lignes
    </p>
    {/if}
  {/if}

{/if}

{if !$sent}
<form action="{$smarty.server.PHP_SELF}" method="POST">
  <table class="bicol" cellpadding="3" cellspacing="0" summary="Proposition d'article newsletter">
    <thead>
      <tr>
        <th>
          Proposition d'article
        </th>
      </tr>
    </thead>
    <tbody>
      <tr class="pair">
        <td class="bicoltitre">
          Titre
        </td>
      </tr>
      <tr class="pair">
        <td>
          <input type="text" value="{$titre}" name="titre" size="68">
        </td>
      </tr>
      <tr class="impair">
        <td class="bicoltitre">
          Article :
        </td>
      </tr>
      <tr class="impair">
        <td>
          <textarea cols="70" rows="10" name="article">{$article}</textarea>
        </td>
      </tr>
      <tr class="pair">
        <td class="bicoltitre">
          Adresses, url, mail, contact, téléphone, etc. :
        </td>
      </tr>
      <tr class="pair">
        <td>
          <textarea cols="70" rows="10" name="bonus">{$bonus}</textarea>
        </td>
      </tr>
      <tr class="impair">
        <td class="bouton">
          <input type="submit" name="action" value="Tester" />
        </td>
      </tr>
    </tbody>
  </table>
</form>
{/if}

{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}

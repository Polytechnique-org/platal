{* $Id: emails.tpl,v 1.2 2004-02-12 02:03:08 x2000habouzit Exp $ *}

<div class="rubrique">
Gestion de mes courriers électroniques
</div>

{dynamic}

<table class="bicol">
  <tr>
    <th>Mes adresses polytechniciennes à vie {if !$is_homonyme}*{/if}</th>
  </tr>
  <tr class="impair">
    <td>
      Tes adresses polytechniciennes sont
      <strong>{$smarty.session.username}@polytechnique.org</strong> et
      <strong>{$smarty.session.username}@m4x.org</strong>
      (M4X signifie <em>mail for X</em>, son intérêt est de te doter d'une adresse à vie
      moins "voyante" que l'adresse @polytechnique.org).
      {if $alias}
      Tu disposes également des adresses {$alias}@polytechnique.org et {$alias}@m4x.org
      {/if}
    </td>
  </tr>
  <tr class="pair">
    <td>
      Elles seront prochainement <strong>complétées d'une adresse @polytechnique.edu</strong>,
      plus lisible dans les pays du monde où "Polytechnique" n'évoque pas grand chose,
      .edu étant le suffixe propre aux universités et établissements d'enseignement supérieur.
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th>Où est-ce que je reçois le courrier qui m'y est adressé ?</th>
  </tr>
  <tr>
    <td>
      Actuellement, tout courrier électronique qui t'y est adressé, est envoyé
      {if $nb_mails eq 1} à l'adresse {else} aux adresses {/if}
      {section name=mail loop=$mails}
      <strong>{$mails[mail].email}</strong>{if $smarty.section.mail.last}.{else}, {/if}
      {/section}
      <br />
      Si tu souhaites <strong>modifier ce reroutage de ton courrier,</strong>
      <a href="{"routage-mail.php"|url}">il te suffit de te rendre ici !</a>
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th colspan="2">Antivirus, antispam</th>
  </tr>
  <tr>
    <td class="half">
      Tous les courriers qui te sont envoyés sur tes adresses polytechniciennes sont
      <strong>filtrés par un logiciel antivirus</strong> très performant. Il te protège de ces
      vers très gênants, qui se propagent souvent par le courrier électronique.
    </td>
    <td class="half">
      De même, un <strong>service antispam évolué</strong> est en place. Tu peux lui demander
      de te débarrasser des spams que tu reçois. Pour en savoir plus, et l'activer,
      <a href="antispam.php">c'est très simple, suis ce lien </a>!
      <br />
    </td>
  </tr>
</table>

<br />

<table class="bicol">
  <tr>
    <th>Un alias sympatique : melix !</th>
  </tr>
  <tr class="impair">
    <td>
      Tu peux ouvrir en supplément une adresse synonyme de ton adresse @polytechnique.org, 
      sur les domaines @melix.org et @melix.net (melix = Mél X).
    </td>
  </tr>
  <tr class="pair">
    <td>
      {if $melix}
      Tu disposes à l'heure actuelle des adresses <strong>{$melix}net</strong> et <strong>{$melix}org</strong>.
      Pour <strong>demander à la place un autre alias melix</strong>,
      <a href="alias.php">il te suffit de te rendre ici</a>
      {else}
      A l'heure actuelle <strong>tu n'as pas activé d'adresse melix</strong>.
      Si tu souhaites le faire, <a href="alias.php">il te suffit de venir ici</a>
      {/if}
    </td>
  </tr>
</table>


{if !$is_homonyme}
<p class="smaller">
* Tu les garderas toute ta vie, sauf si un jour un homonyme d'une autre promotion
s'inscrit à Polytechnique.org (les cas d'homonymie sont <em>très</em> rares),
auquel cas ces adresses deviendraient
{$smarty.session.username}{$smarty.session.promo|regex_replace:"/^../":""}@polytechnique.org et
{$smarty.session.username}{$smarty.session.promo|regex_replace:"/^../":""}@m4x.org
</p>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

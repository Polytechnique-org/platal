{* $Id: coupure.tpl,v 1.1 2004-02-04 19:47:48 x2000habouzit Exp $ *}

{dynamic}

{if $cp}

<table class="bicol" summary="Ruptures de service">
  <tr>
    <th colspan="2">détails de l'interruption de service<th>
  </tr>
  <tr>
    <td class="titre">début</td>
    <td>{$cp.debut|date_forma:"%d/%m/%Y, %Hh%M"}</td>
  </tr>
  <tr>
    <td class="titre">durée</td>
    <td>{$cp.duree}</td>
  </tr>
  <tr>
    <td class="titre">résumé</td>
    <td>{$cp.resume}</td>
  </tr>
  <tr>
    <td class="titre">services</td>
    <td>
    {insert name="serv_to_str" arg=$cp.services script="insert.pattecassee.inc.php"}
    </td>
  </tr>
  <tr>
    <td class="titre">description </td>
    <td>{$cp.description}</td>
  </tr>
</table>

<p class="center">
<a href="{$smarty.server.PHP_SELF}">retour à la liste</a>
</p>

{else}

<p class="normal">
  Tu trouveras ici les interruptions de service de Polytechnique.org qui ont été
  constatées <b>durant les trois dernières semaines</b>, ou qui sont prévues dans le futur.
  Il est à noter qu'à ce jour la quasi-totalité des coupures proviennent 
  de défaillances du réseau de l'Ecole, où nos serveurs sont hébergés (rupture de la
  connexion internet de l'Ecole, problème électrique, etc...).
</p>
<p class="normal">
  Pour avoir les détails d'une interruption particulière il te suffit de cliquer dessus.
</p>

<table class="bicol" align="center" summary="Détail de la coupure">
  <tr>
    <th>date</th>
    <th>résumé</th>
    <th>services affectés</th>
  </tr>
{foreach item=cp from=$coupures}
  <tr class="{cycle values="pair,impair"}">
    <td>
      <span class="smaller">
        {$cp.debut|date_format:"%d/%m/%Y"}
      </span>
    </td>
    <td>
      <span class="smaller">
        <a href="{$smarty.server.PHP_SELF}?cp_id={$cp.id}">{$cp.resume}</a>
      </span>
    </td>
    <td>
      <span class="smaller">
        {$cp.services}
      </span>
    </td>
  </tr>
{/foreach}
</table>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

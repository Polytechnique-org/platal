{* $Id: answer.tpl,v 1.1 2004-04-26 14:45:53 x2000habouzit Exp $ *}

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="show.php?tr_id={$smarty.get.tr_id}">Revenir au tracker</a>]
[<a href="show_rq.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$smarty.get.rq_id}">Revenir à la requete</a>]
</p>

<div class="rubrique">
  {$request.summary} (posté le {$request.date|date_format:"%d %b %Y"})
</div>

<table class="bicol">
  <tr><th>Texte posté</th></tr>
  <tr><td><tt>{$request.texte|escape|nl2br}</tt></td></tr>
</table>

<br />

<div class="rubrique">
  Répondre
</div>

<form action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="bicol">
    <tr>
      <th>texte de la réponse</th>
    </tr>
    <tr>
      <td class="center">
        <textarea name="a_text" cols="74" rows="20"></textarea>
      </td>
    </tr>
    <tr>
      <td class="center">
        <input type="submit" name="a_sub" />
      </td>
    </tr>
  </table>
</form>

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}

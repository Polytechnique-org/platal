{* $Id: newsletter.tpl,v 1.5 2004-08-24 20:05:35 x2000habouzit Exp $ *}

{dynamic}

{$erreur}

{if $nl_titre}

<p class="center">
[<a href="{$smarty.server.REQUEST_URI}&amp;send_mail=1">me l'envoyer par mail</a>]
</p>
<table class="bicol" summary="Archives de la NL">
  <tr>
    <th>
      {$nl_titre} - {$nl_date|date_format:"%d/%m/%Y"}
    </th>
  </tr>
  <tr>
    <td style="padding: 1em;">
      <tt>{$nl_text|smarty:nodefaults|replace:" ":"&nbsp;"|nl2br}</tt>
    </td>
  </tr>
</table>
<p class="center">
[<a href="{$smarty.server.PHP_SELF}">retour à la liste</a>]
</p>

{else}

<div class="rubrique">
  Lettre de Polytechnique.org
</div>
<p class="normal">
Tu trouveras ici les archives de la lettre d'information de Polytechnique.org.  Pour t'abonner à
cette lettre, il te suffit de te <a href="listes/">rendre sur la page des listes</a> et de cocher la
case "newsletter". Enfin, <strong>pour demander l'ajout d'une annonce dans la prochaine lettre
  mensuelle</strong>, <a href="mailto:info_newsletter@polytechnique.org">écris-nous !</a>
</p>

{include file=include/newsletter.list.tpl nl_list=$nl_list}

{/if}

{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}

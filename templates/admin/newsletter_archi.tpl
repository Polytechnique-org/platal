{* $Id: newsletter_archi.tpl,v 1.1 2004-02-11 11:51:53 x2000habouzit Exp $ *}

<div class="rubrique">
  Gestion des archives de la newsletter
</div>

{dynamic}

{if $smarty.request.action eq 'edit'}

{include file=include/form.newsletter.tpl form_title='modifier une newsletter' nl_id=$nl.id nl_date=$nl.date nl_titre=$nl.titre nl_text=$nl.text}
  
{else}
  
{include file=include/newsletter.list.tpl admin=1}
<br />
{include file=include/form.newsletter.tpl form_title='ajouter une newsletter' nl_id=0}

{/if}

{/dynamic}
{* vim:set et sw=2 sts=2 sws=2: *}

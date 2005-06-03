<h1>{$asso.nom} :
{if ($smarty.request.add || $smarty.request.mod) && $admin}
<a href='{$smarty.server.PHP_SELF}'>Evénements</a>
{else}
Evénements
{/if}
</h1>
{if !$logged}
<p class="descr">
   Aucune manifestation publique n'a été saisie par ce groupe pour l'instant...
</p>
{elseif $get_form}
    {include file='xnet/groupe/form_evenement.tpl'}
{else}
{if $admin}
<p class="center">
        <a href="{$marty.server.PHP_SELF}?add=1">Annoncer un nouvel événement</a>
</p>
{/if}

{if $nb_evt eq 0}
<p class="descr">
	Aucun événement n'a été référencé par les animateurs du groupe.
</p>
{else}
<ul class="decr">
	{iterate from=$evenements item=e}
	<li>{$e.intitule}
	{if $admin || $e.show_participants}
		<a href="evt-admin.php?eid={$e.eid}"><img src="{rel}/images/loupe.gif" title="Liste des participants" alt="Liste des participants" /></a>
	{/if}
	{if $admin}
		<a href="{$smarty.session.PHP_SELF}?mod=1&eid={$e.eid}"><img src="{rel}/images/profil.png" title="Edition de l'événement" alt="Edition de l'événement" /></a>
		<a href="{$smarty.session.PHP_SELF}?sup=1&eid={$e.eid}"><img src="{rel}/images/del.png" alt="Suppression de {$e.intitule}" title="Suppression de {$e.intitule}" /></a>
	{/if}
	<br />
	    &nbsp;&nbsp;&nbsp;&nbsp;date : {$e.debut}{if $e.fin} - {$e.fin}{/if}
	    <br />
	    &nbsp;&nbsp;&nbsp;&nbsp;annonceur : <a href='https://polytechnique.org/fiche.php?user={$e.alias}' target='new'>{$e.prenom} {$e.nom} ({$e.promo})</a>
	    <br />
	    <a href='evt-detail.php?eid={$e.eid}'><i>Détails...</i></a> 
	    {if $e.inscrit}
	        <small>tu es inscrit à cet événément !</small>
	    {else}
	        <small>pour l'instant, tu n'es pas inscrit à cet événément !</small>
	    {/if}
	</li>
	{/iterate}
</ul>
{/if}

{/if}


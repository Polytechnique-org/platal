{dynamic}
{if $formulaire==0 and !$error}
  <div class="rubrique">
    Résultats
  </div>
  <div class="sstitre">
    {if $nb_resultats_total==0}Aucune{else}{$nb_resultats_total}{/if} réponse{if $nb_resultats_total>1}s{/if}.
    <div class="floatright">
      {if $with_soundex==0}
        <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&with_soundex=1&rechercher=1&{$url_args}">
          Etendre à la recherche par proximité sonore
        </a>
      {/if}
      &nbsp;
      <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}">Nouvelle recherche</a>
    </div>
  </div>
  <p class="normal">
    <div class="contact-list">
      {section name=resultat loop=$resultats}
      <div class="contact">
      <div class="{if $resultats[resultat].inscrit==1}pri3{else}pri1{/if}">
        {include file="search.result.public.tpl" result=$resultats[resultat]}
        {if $public_directory!=1}
          {include file="search.result.private.tpl" result=$resultats[resultat]}
        {/if}
      </div>
      </div>
      {/section}
    </div>
  </p>
  {if $perpage<$nb_resultats_total}
  <p class="normal">
    {if $offset!=0}
      <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&with_soundex={$with_soundex}&rechercher=1&{$url_args}&offset={$offset-$perpage}">
        Précédent
      </a>
      &nbsp;
    {/if}
    {section name=offset loop=$offsets}
      {if $offset!=$smarty.section.offset.index*$perpage}
        <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&with_soundex={$with_soundex}&rechercher=1&{$url_args}&offset={$smarty.section.offset.index*$perpage}">
          {$smarty.section.offset.index+1}
        </a>
      {else}
        <strong>{$smarty.section.offset.index+1}</strong>
      {/if}
      &nbsp;
    {/section}
    {if $offset<$nb_resultats_total-$perpage}
      <a href="{$smarty.server.PHP_SELF}?public_directory={$public_directory}&with_soundex={$with_soundex}&rechercher=1&{$url_args}&offset={$offset+$perpage}">
        Suivant
      </a>
      &nbsp;
    {/if}
  </p>
  {/if}
{else}
  {include file="search.form.tpl"}
{/if}
{/dynamic}

<div class="nom">
  {$result.nom} {$result.prenom}
  {if $result.epouse neq ""}
    <br />({$result.epouse} {$result.prenom})
  {/if}
  {if $result.decede == 1}
    (décédé)
  {/if}
</div>
<div class="appli">
  {strip}
  (X {$result.promo}
  {if $result.app0text},
    {applis_fmt type=$result.app0type text=$result.app0text url=$result.app0url}
  {/if}
  {if $c.app1text},
    {applis_fmt type=$result.app1type text=$result.app1text url=$result.app1url}
  {/if})
  {/strip}
</div>

{* $Id: admin.tpl,v 1.1 2004-02-22 21:04:23 x2000habouzit Exp $ *}

{literal}
<script type="text/javascript">
  <!--
  function action( action, myid ) {
    document.operations.action.value = action;
    document.operations.tr_id.value = myid;
    document.operations.submit();
    return true;
  }
  
  function del( myid ) {
    if( confirm ("You are about to delete this tracker !\nDo you still want to proceed ?") ) {
      document.operations.action.value = "del";
      document.operations.tr_id.value = myid;
      document.operations.submit();
      return true;
    }
  }
  -->
</script>
{/literal}

<form id="operations" method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" id="action" value="" />
  <input type="hidden" id="tr_id" value="" />
</form>

<div class="rubrique">
  Administration des trackers
</div>
<div class="ssrubrique">
  Nettoyer
</div>
<p class="normal">
  [<a href="javascript:action('clean',-1)">Nettoyer la table des mailing-lists</a>]
</p>
<div class="ssrubrique">
  Ajouter un tracker
</div>
<p class="normal">
  [<a href="javascript:action('edit',-1)">Ajouter un tracker</a>]
</p>

<br />  

<div class="rubrique">
  Liste des trackers
</div>

<br />

<table class="bicol" summary="Liste des trackers">
  <tr>
    <th>Tracker</th>
    <th>Description</th>
    <th>Géré&nbsp;par</th>
  </tr>
{foreach item=t from=$persos}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="{"tracker_show.php?tr_id=`$t.tr_id`"|url}">{$t.tr_name}</a></td>
    <td>{$t.description}</td>
    <td class="right"><a href="mailto:{$t.ml_name}">{$t.short}</a></td>
    <td class="action">
      <a href="javascript:action('edit',{$t.tr_id})">edit</a>
      <a href="javascript:del({$t.tr_id})">del</a>
  </tr>
{/foreach}
</table>

<br />

<div class="rubrique">
  Liste des trackers persos
</div>

<br />

<table class="bicol" summary="Liste des trackers">
  <tr>
    <th>Tracker</th>
    <th>Description</th>
    <th>Géré&nbsp;par</th>
  </tr>
{foreach item=t from=$trackers}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="{"tracker_show.php?tr_id=`$t.tr_id`"|url}">{$t.tr_name}</a></td>
    <td>{$t.description}</td>
    <td class="right"><a href="mailto:{$t.ml_name}">{$t.short}</a></td>
    <td class="action">
      <a href="javascript:action('edit',{$t.tr_id})">edit</a>
      <a href="javascript:del({$t.tr_id})">del</a>
  </tr>
{/foreach}
</table>

<br />
{* vim:set et sw=2 sts=2 sws=2: *}

{* $Id: admin.tpl,v 1.3 2004-02-23 17:10:11 x2000habouzit Exp $ *}

{literal}
<script type="text/javascript">
  <!--
  function action( action, myid ) {
    ops = document.getElementById('operations');
    ops.action.value = action;
    ops.trid.value = myid;
    ops.submit();
    return true;
  }
  
  function del( myid ) {
    if( confirm ("You are about to delete this tracker !\nDo you still want to proceed ?") ) {
      return action('del', myid);
    }
  }
  -->
</script>
{/literal}

<form id="operations" method="post" action="{$smarty.server.PHP_SELF}">
  <input type="hidden" id="action" name="action" value="" />
  <input type="hidden" id="trid" name="trid" value="" />
</form>

<p class="normal">
  [<a href="javascript:action('clean',-1)">Nettoyer la table des mailing-lists</a>]
  [<a href="javascript:action('edit',-1)">Ajouter un tracker</a>]
</p>

<div class="rubrique">
  Liste des trackers
</div>

<table class="bicol" summary="Liste des trackers">
  <tr>
    <th>Tracker</th>
    <th>Description</th>
    <th>Géré&nbsp;par</th>
    <th>Action</th>
  </tr>
{foreach item=t from=$trackers}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="{"show.php?tr_id=`$t.tr_id`"|url}">{$t.tr_name}</a></td>
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

<table class="bicol" summary="Liste des trackers">
  <tr>
    <th>Tracker</th>
    <th>Description</th>
    <th>Géré&nbsp;par</th>
    <th>Action</th>
  </tr>
{foreach item=t from=$persos}
  <tr class="{cycle values="impair,pair"}">
    <td><a href="{"show.php?tr_id=`$t.tr_id`"|url}">{$t.tr_name}</a></td>
    <td>{$t.description}</td>
    <td class="right"><a href="mailto:{$t.ml_name}">{$t.short}</a></td>
    <td class="action">
      <a href="javascript:action('edit',{$t.tr_id})">edit</a>
      <a href="javascript:del({$t.tr_id})">del</a>
  </tr>
{/foreach}
</table>

{* vim:set et sw=2 sts=2 sws=2: *}

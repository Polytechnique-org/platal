{* $Id: show.tpl,v 1.7 2004-04-26 14:45:53 x2000habouzit Exp $ *}

{literal}
<script type="text/javascript">
  <!--
  function del( myid ) {
    if( confirm ("You are about to delete this request !\nDo you still want to proceed ?") ) {
      ops = document.getElementById('op');
      ops.id.value = myid;
      ops.submit();
      return action('del', myid);
    }
  }
  -->
</script>
{/literal}

<form id="op" method="post" action="{$smarty.server.REQUEST_URI}">
  <input type="hidden" id="id" name="id" value="" />
</form>

{dynamic}

<p class="normal">
  [<a href="index.php">Liste des trackers</a>]
  [<a href="post.php?tr_id={$smarty.get.tr_id}">Poster dans ce tracker</a>]
</p>

<div class="rubrique">
  Tracker {$tracker->name}
</div>
<table class="bicol" cellpadding="3">
  <tr>
    <th>Date</th>
    <th>Sujet</th>
    <th>Assigné à</th>
  </tr>
{foreach item=rq from=$requests}
  <tr class="pri{$rq.pri}">
    <td>{$rq.date|date_format:"%d&nbsp;%b&nbsp;%Y"}</td>
    <td><a href="show_rq.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$rq.rq_id}">{$rq.summary}</a></td>
    <td class="right">{if $rq.username}<a href="mailto:{$rq.username}@polytechnique.org">{$rq.username}</a>{else}-{/if}</td>
  </tr>
{/foreach}
<tr><th colspan="3"></th></tr>
{foreach item=rq from=$close}
  <tr>
    <td>{$rq.date|date_format:"%d&nbsp;%b&nbsp;%Y"}</td>
    <td><a href="show_rq.php?tr_id={$smarty.get.tr_id}&amp;rq_id={$rq.rq_id}">{$rq.summary}</a></td>
    <td class="action"><a href="javascript:del({$rq.rq_id})">delete</a></td>
  </tr>
{/foreach}
</table>
{/dynamic}

<br />
<div class="ssrubrique">
  Couleurs des priorités
</div>
<table summary="priorités">
  <tr>
    <td class="pri1">1</td>
    <td class="pri2">2</td>
    <td class="pri3">3</td>
    <td class="pri4">4</td>
    <td class="pri5">5</td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}

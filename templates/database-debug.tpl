{dynamic}

{foreach item=query from=$trace_data}
<br />
<table class="bicol" style="width: 75%; font-family: fixed">
  <tr class="impair">
    <td><strong>QUERY:</strong><br />{$query.query|nl2br}</td>
  </tr>
  {if $query.error}
  <tr>
    <td><strong>ERROR:</strong><br />{$query.error|nl2br}</td>
  </tr>
  {/if}
</table>
<table class="bicol" style="width: 75%; font-family: fixed">
  <tr>
    <th>table</th>
    <th>type</th>
    <th>possible_keys</th>
    <th>key</th>
    <th>key_len</th>
    <th>ref</th>
    <th>rows</th>
    <th>extra</th>
  </tr>
  <tr class="impair">
    <td class="center">{$query.explain.table}</td>
    <td class="center">{$query.explain.type}</td>
    <td class="center">{$query.explain.possible_keys}</td>
    <td class="center">{$query.explain.key}</td>
    <td class="center">{$query.explain.key_len}</td>
    <td class="center">{$query.explain.ref}</td>
    <td class="center">{$query.explain.rows}</td>
    <td class="center">{$query.explain.Extra}</td>
  </tr>
</table>
<br />
{/foreach}

{/dynamic}

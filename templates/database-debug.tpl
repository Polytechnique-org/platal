{dynamic}

{foreach item=query from=$trace_data}
<table class="bicol" style="width: 75%; font-family: fixed; margin-left:2px; margin-top: 3px;">
  <tr class="impair">
    <td>
      <strong>QUERY:</strong><br />
      {$query.query|regex_replace:"/(\n|^|$) */":"\n  "|replace:" ":"&nbsp"|nl2br}
      <br />
    </td>
  </tr>
  {if $query.error}
  <tr>
    <td>
      <strong>ERROR:</strong><br />
      {$query.error|nl2br}
    </td>
  </tr>
  {/if}
</table>
<table class="bicol" style="width: 75%; font-family: fixed; margin-left: 2px; margin-bottom: 3px; margin-top: -1px;">
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
{/foreach}

{/dynamic}

{dynamic}

{foreach item=query from=$trace_data}
<table class="bicol" style="width: 75%; font-family: fixed; margin-left:2px; margin-top: 3px;">
  <tr class="impair">
    <td>
      <strong>QUERY:</strong><br />
      {$query.query|regex_replace:"/(\n|^|$) */":"\n  "|replace:" ":"&nbsp;"|nl2br}
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
{if $query.explain}
<table class="bicol" style="width: 75%; font-family: fixed; margin-left: 2px; margin-bottom: 3px;">
  <tr>
    {foreach key=key item=item from=$query.explain}
    <th>{$key}</th>
    {/foreach}
  </tr>
  <tr class="impair">
    {foreach item=item from=$query.explain}
    <td class="center">{$item}</td>
    {/foreach}
  </tr>
</table>
{/if}
{/foreach}

{/dynamic}

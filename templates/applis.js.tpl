<script type="text/javascript">
//<![CDATA[
    applisType = new Array(
{applis_type}
);

applisTypeAll = new Array(
{applis_type_all}
);
{literal}
function fillType( selectCtrl, appli, fill ) {
var i;
var i0=0;

for (i = selectCtrl.options.length; i >=0; i--) {
selectCtrl.options[i] = null;
}

if (fill || appli <0) {
  selectCtrl.options[0] = new Option(' ');
  i0=1;
}
if (appli>=0) 
  for (i=0; i < applisType[appli].length; i++) 
    selectCtrl.options[i0+i] = new Option(applisType[appli][i]);
else if (fill)
  for (i=0; i < applisTypeAll.length; i++) 
    selectCtrl.options[i0+i] = new Option(applisTypeAll[i]);
}

function selectType( selectCtrl, type ) {
  for (i = 0; i < selectCtrl.options.length; i++) {
    if (selectCtrl.options[i].text == type)
      selectCtrl.selectedIndex=i;
  }
}
{/literal}
//]]>
</script>

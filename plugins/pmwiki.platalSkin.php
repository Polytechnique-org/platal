<?php

// set default author
$Author = $_SESSION['forlife']."|".$_SESSION['prenom']." ".$_SESSION['nom'];

// set profiles to point to plat/al fiche
Markup('[[~platal', '<[[~', '/\[\[~([^|\]]*)\|([^\]]*)\]\]/e',
    'PreserveText("=", \'<a href="'.$globals->baseurl
    .'/fiche.php?user=$1" class="popup2">$2</a>\', "")');

## [[#anchor]] in standard XHTML
Markup('[[#','<[[','/(?>\\[\\[#([A-Za-z][-.:\\w]*))\\]\\]/e',
  "Keep(\"<a id='$1'></a>\",'L')");
  
Markup('tablebicol', '<block', '/\(:tablebicol ?([a-z_]+)?:\)/e', 'doBicol("$1")');
Markup('pairrows', '_end', '/class=\'pair\_pmwiki\_([0-9]+)\'/e', 
    "($1 == 1)?'':('class=\"'.(($1 % 2 == 0)?'impair':'pair').'\"')");
Markup('noclassth', '_end', '/<th class=\'[a-z_]+\'/', '<th');

Markup('div', '<links', '/\(:div([^:]*):([^\)]*):\)/i', '<div$1>$2</div>');

function doBicol($column=false)
{
    global $TableRowIndexMax, $TableRowAttrFmt, $TableCellAttrFmt;
    $TableRowAttrFmt = "class='pair_pmwiki_\$TableRowCount'";
    if ($column) {
        $TableCellAttrFmt = "class='$column'";
    }
}

?>

<?php if (!defined('PmWiki')) exit();

$EnablePathInfo   = 1;   # in fact works with apache rewrite, name is misleading
$EnableGUIButtons = 1;
$EnableUpload     = 1;
$UploadMaxSize    = 500000;
$LinkWikiWords    = 0;   # disable WikiWord links
$EnableIMSCaching = 1;   # allow browser caching

$ScriptUrl        = '.';          #\
$UploadUrlFmt     = './uploads';  # } works thanks to the <base /> in skin
$PubDirUrl        = './wiki';     #/

$WorkDir          = '../spool/wiki.d';
$WikiDir          = new PageStore('$FarmD/'.$WorkDir.'/$FullName');
$InterMapFiles[]  = $globals->spoolroot.'/configs/pmwiki.intermap.txt';

$Skin             = 'empty';

@include_once("$FarmD/cookbook/e-protect.php");
include_once($FarmD.'/scripts/xlpage-utf-8.php');

// Theme-ing {{{

##  The following lines make additional editing buttons appear in the
##  edit page for subheadings, lists, tables, etc.
$GUIButtons['h2'] = array(400, '\\n!! ', '\\n', '$[Heading]',
                 '$GUIButtonDirUrlFmt/h2.gif"$[Heading]"');
$GUIButtons['h3'] = array(402, '\\n!!! ', '\\n', '$[Subheading]',
                 '$GUIButtonDirUrlFmt/h3.gif"$[Subheading]"');
$GUIButtons['indent'] = array(500, '\\n->', '\\n', '$[Indented text]',
                 '$GUIButtonDirUrlFmt/indent.gif"$[Indented text]"');
$GUIButtons['outdent'] = array(510, '\\n-<', '\\n', '$[Hanging indent]',
                 '$GUIButtonDirUrlFmt/outdent.gif"$[Hanging indent]"');
$GUIButtons['ol'] = array(520, '\\n# ', '\\n', '$[Ordered list]',
                 '$GUIButtonDirUrlFmt/ol.gif"$[Ordered (numbered) list]"');
$GUIButtons['ul'] = array(530, '\\n* ', '\\n', '$[Unordered list]',
                 '$GUIButtonDirUrlFmt/ul.gif"$[Unordered (bullet) list]"');
$GUIButtons['hr'] = array(540, '\\n----\\n', '', '',
                 '$GUIButtonDirUrlFmt/hr.gif"$[Horizontal rule]"');
$GUIButtons['table'] = array(600,
                   '||border=1 width=80%\\n||!Hdr ||!Hdr ||!Hdr ||\\n||     ||     ||     ||\\n||     ||     ||     ||\\n', '', '', 
                 '$GUIButtonDirUrlFmt/table.gif"$[Table]"');

// set default author
$Author = $_SESSION['forlife'].'|'.$_SESSION['prenom'].' '.$_SESSION['nom'];

$InputTags['e_form'] = array(
  ':html' => "<form action='{\$PageUrl}?action=edit' method='post'><div><input 
    type='hidden' name='action' value='edit' /><input 
    type='hidden' name='n' value='{\$FullName}' /><input 
    type='hidden' name='basetime' value='\$EditBaseTime' /></div>");

// set profiles to point to plat/al fiche
Markup('[[~platal', '<[[~', '/\[\[~([^|\]]*)(?:\|([^\]]*))?\]\]/e',
    'PreserveText("=", doPlatalLink("$1", "$2"), "")');

// prevent restorelinks before block apply (otherwise [[Sécurité]] will give
//  .../S<span class='e9curit'>e9'>Sécurité</a>
Markup('restorelinks','<%%',"//", '');

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

function doPlatalLink($link, $text)
{
    if (strlen(trim($text)) == 0) { 
        $res = XDB::query("SELECT u.nom, u.prenom, u.promo, q.profile_nick AS surnom
                             FROM auth_user_md5   AS u
                       INNER JOIN auth_user_quick AS q USING(user_id)
                       INNER JOIN aliases         AS a ON u.user_id = a.id
                            WHERE a.alias = {?}", $link);
        $row = $res->fetchOneAssoc();
        $text = $row['prenom'] . ' ' . $row['nom'] . ' X' . $row['promo'];
        if ($row['surnom']) {
            $text .= ' (alias ' . $row['surnom'] . ')';
        }
    }
    return '<a href="profile/' . $link . '" class="popup2">' . $text . '</a>';
}

// }}}

$AuthFunction = 'ReadPage';

$HandleAuth['diff']   = 'edit';
$HandleAuth['source'] = 'edit';

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

<?php if (!defined('PmWiki')) exit();

$ScriptUrl       = $globals->baseurl;
$UploadUrlFmt    = $ScriptUrl."/uploads";
$WorkDir         = '../spool/wiki.d';
$WikiDir         = new PageStore('$FarmD/'.$WorkDir.'/$FullName');
$PubDirUrl       = $globals->baseurl.'/wiki';
$InterMapFiles[] = $globals->spoolroot.'plugins/pmwiki.intermap.txt';

# Authorize group name to start with a number (for promo groups)
$GroupPattern = '[[:upper:]0-9][\\w]*(?:-\\w+)*';

$EnablePathInfo = 1;

$Skin = 'empty';

XLSDV('en', array('EnterAttributes' =>
    "Entre ici les différents droit pour la page. Les champs laissés en blanc ne seront pas modifiés.
    Pour enlever une restriction ou une autorisation entre <strong>clear</strong>.
    Les différentes restrictions possibles sont :
    <ul>
        <li><strong>public:</strong> (pour tout le monde)</li>
        <li><strong>logged:</strong> (pour ceux qui ont rentré leur mot de passe ou qui ont un cookie permanent)</li>
        <li><strong>identified:</strong> (exige une identification par mot de passe)</li>
        <li><strong>has_perms:</strong> (pour les administrateurs de la page)</li>
    </ul>
    Le <strong>:</strong> à la fin de chaque mot clef est important. Tu peux également combiner plusieurs mots clefs avec <strong>and:</strong>
    ou des espaces (qui remplace le <em>ou</em> logique)<br/>"));

include_once($globals->spoolroot."/plugins/pmwiki.platalAuth.php");
@include_once("$FarmD/cookbook/e-protect.php");

$DefaultPasswords['read']   = 'has_perms: and: identified:';
$DefaultPasswords['edit']   = 'has_perms: and: identified:';
$DefaultPasswords['attr']   = 'has_perms: and: identified:';
$DefaultPasswords['admin']  = 'has_perms: and: identified:';
$DefaultPasswords['upload'] = 'has_perms: and: identified:';

$EnableGUIButtons = 1;
$EnableUpload     = 1;
$LinkWikiWords    = 0;   # disable WikiWord links
$EnableIMSCaching = 1;   # allow browser caching

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
$Author = $_SESSION['forlife']."|".$_SESSION['prenom']." ".$_SESSION['nom'];

$InputTags['e_form'] = array(
  ':html' => "<form action='{\$PageUrl}?action=edit' method='post'><div><input 
    type='hidden' name='action' value='edit' /><input 
    type='hidden' name='n' value='{\$FullName}' /><input 
    type='hidden' name='basetime' value='\$EditBaseTime' /></div>");

// set profiles to point to plat/al fiche
Markup('[[~platal', '<[[~', '/\[\[~([^|\]]*)\|([^\]]*)\]\]/e',
    'PreserveText("=", \'<a href="profile/$1" class="popup2">$2</a>\', "")');

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
?>

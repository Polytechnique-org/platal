<?php if (!defined('PmWiki')) exit();

require_once 'wiki.inc.php';

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
include_once($globals->spoolroot."/plugins/pmwiki.platalSkin.php");
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

?>

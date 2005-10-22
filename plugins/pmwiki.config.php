<?php if (!defined('PmWiki')) exit();

$ScriptUrl       = $globals->baseurl;
$UploadUrlFmt    = $ScriptUrl."/uploads";
$WorkDir         = '../spool/wiki.d';
$WikiDir         = new PageStore('$FarmD/../spool/wiki.d/$FullName');
$PubDirUrl       = $globals->baseurl.'/wiki';
$InterMapFiles[] = $globals->spoolroot.'plugins/pmwiki.intermap.txt';

$EnablePathInfo = 1;

$Skin = 'empty';

include_once($globals->spoolroot."/plugins/pmwiki.platalAuth.php");
include_once($globals->spoolroot."/plugins/pmwiki.platalSkin.php");
@include_once("$FarmD/cookbook/e-protect.php");

$DefaultPasswords['read']   = 'has_perms: and: identified:';
$DefaultPasswords['edit']   = 'has_perms: and: identified:';
$DefaultPasswords['attr']   = 'has_perms: and: identified:';
$DefaultPasswords['admin']  = 'has_perms: and: identified:';
$DefaultPasswords['upload'] = 'has_perms: and: identified:';

$EnableGUIButtons = 1;
$EnableUpload = 1;                       
$LinkWikiWords = 0;                      # disable WikiWord links
$EnableIMSCaching = 1;                   # allow browser caching

##  If you want to have to approve links to external sites before they
##  are turned into links, uncomment the line below.  See PmWiki.UrlApprovals.
##  Also, setting $UnapprovedLinkCountMax limits the number of unapproved
##  links that are allowed in a page (useful to control wikispam).
# include_once('scripts/urlapprove.php');
# $UnapprovedLinkCountMax = 10;

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

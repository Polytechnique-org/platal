<?php if (!defined('PmWiki')) exit();
                                                                      /*
  * Copyright *
  Copyright 2004, by Steven Leite (steven_leite@kitimat.net).
  Changes 2004, by Karl Loncarek (Klonk)

  * License *
  Same as PmWiki (GNU GPL)

  * Special Thanks *
  Thanks to  Pm (Patrick Michaud, www.pmichaud.com), for creating
  PmWiki, and for sharing his knowledge and insightfulness with the
  PmWiki community.

  * Description *
  eProtect is an email obfuscation add-on for PmWiki. It intercepts
  pages before they are saved, and rewrites email addresses in a
  protected format.

     Example: "[[mailto:username@domain.net]]" is automatically replaced with:
     "[[hidden-email:hfre@qbznva.arg]]".

  When a WikiPage is requested the [[hidden-email:]] directive is
  detected, and then translated into a small javascript.  Once the page
  is rendered to the in website, it's at that point which the javascript
  decodes the protected address. Viewing the source-code of the page
  won't reveal the true email address, nor will clicking on the link to
  "Edit This Page".

  Also you could give some text that is displayed instead the email address.

    Example: "[[mailto:username@domain.net | Special Guy]]" is automatically
     replaced with: "[[hidden-email:hfre@qbznva.arg | Special Guy]]". On the
     page you only can see "Special Guy" as urllink.

  But beware: giving the email address as alternative text renders this script
  useless, because this alternative text is NOT encoded, and thus is also visible
  in HTML source code!


  * Installation Instructions *
  1. Copy this script (e-protect.php) to local/scripts/
  2. In your config.php file, add the following line:
     include_once('scripts/e-protect.php');
  3. That's it!

  * History *
  Apr  7 2005 - * Replaced str_rot13 with strtr to make it work with PHP <4.2.0,
                  and to have the @ recoded.
  Feb 18 2005 - * Added some comment signs to get valid XHTML when validating (Klonk)
  Feb 15 2005 - * Modified encoding and added additional markup for handling (Klonk)
                  [[text -> mailto:...]]
  Jan  7 2005 - * Moved decoding script to Header, due to problem, when SideBar and Main Text
                  contains addresses to decode (Klonk)

  Nov 17 2004 - * Calling the decoding function as Custom Markup for PmWiki2
   (by Klonk)   * made decoding of [[hidden-email:...]] working
                  BONUS: [[hidden-email:... |DisplayedText]] works also now
                * added class='urllink' to decoded output for same CSS formating
                  as for other links in PmWiki2
                * inserted own function call in array $EditFunctions before 'ReplaceOnSave'

  May 11, 2004 - Working Beta.  Still a few improvements to be made, but
                 the script is ready for public testing.  Please feel
         free to email me your comments/suggestions.  Thanks!
  May 8, 2004  - Alpha release.  Not released to public.

  * Configuration *
  There aren't (yet) any configuration variables for this script.    */

//----------------------------------------------------------------------

## [[hidden-email:target]]
Markup('hidden-email','<links',
  "/\\[\\[hidden-email:([^\\s$UrlExcludeChars]*)\\s*\\]\\]($SuffixPattern)/e",
    "eProtectDecode('$1','')");

## [[hidden-email:target | text]]
Markup('hidden-email|','<hidden-email',
  "/\\[\\[hidden-email:([^\\s$UrlExcludeChars]*)\\s*\\|\\s*(.*?)\\s*\\]\\]($SuffixPattern)/e",
    "eProtectDecode('$1','$2')");

## [[ text -> hidden-email:target]]
Markup('-hidden-email','<hidden-email',
  "/\\[\\[(.*?)\\s*-+&gt;\\s*hidden-email:([^\\s$UrlExcludeChars]*)\\s*\\]\\]($SuffixPattern)/e",
    "eProtectDecode('$2','$1')");

## Add own function in array $EditFunctions before ReplaceOnSave, so it is called, when saving is performed.
array_splice ($EditFunctions, array_search('ReplaceOnSave',$EditFunctions), 1,
  array('eProtectEncode','ReplaceOnSave'));

## Add decoding script to Header
$HTMLHeaderFmt['eProtect']= "\n<script type='text/JavaScript'>\n<!--\nNix={map:null,convert:function(a){Nix.init();var s='';for(i=0;i<a.length;i++){var b=a.charAt(i);s+=((b>='A'&&b<='Z')||(b>='a'&&b<='z')?Nix.map[b]:b);}return s;},init:function(){if(Nix.map!=null)return;var map=new Array();var s='abcdefghijklmnopqrstuvwxyz';for(i=0;i<s.length;i++)map[s.charAt(i)]=s.charAt((i+13)%26);for(i=0;i<s.length;i++)map[s.charAt(i).toUpperCase()]=s.charAt((i+13)%26).toUpperCase();Nix.map=map;},decode:function(a){document.write(Nix.convert(a));}}\n//-->\n</script>\n";

//----------------------------------------------------------------------
function eProtectStrRecode ($s) {
/* str_rot13, extended to recode digits and @#. */
//----------------------------------------------------------------------
  return strtr ($s,
    'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM');
}

//----------------------------------------------------------------------
function eProtectDecode ($CompressedEmailAddress,$AlternateText) {
//----------------------------------------------------------------------
  $email = $CompressedEmailAddress;
  $html = "\n<!--eProtect-->\n";
  if ($AlternateText=='')
    $html .= "<script type='text/JavaScript'>\n<!--\nNix.decode" .
      "(\"<n pynff='heyyvax' uers='znvygb:$email'>$email</n>\");" . "\n//-->\n</script>";
  else
    $html .= "<script type='text/JavaScript'>\n<!--\nNix.decode" .
      "(\"<n pynff='heyyvax' uers='znvygb:$email'>\");" . "\n//-->\n</script>" . $AlternateText . "<script
      type='text/JavaScript'><!--\nNix.decode" .
      "(\"</n>\");" . "\n//-->\n</script>";
  $html .= "\n<!--/eProtect-->\n";
  return Keep($html);
}

//----------------------------------------------------------------------
function eProtectEncode ($pagename,&$page,&$new) {
//----------------------------------------------------------------------
  global $KeepToken, $KPV, $UrlExcludeChars;
  if (!@$_POST['post']) return; // only Encode, when posting
  $text = $new['text'];
  $text = preg_replace_callback("/\\[\\=(.*?)\\=\\]/s", create_function('$str', 'return Keep($str[0]);'), $text);    // extract the [= .. =] and temporarily store in $KPV[]
#  $text = preg_replace_callback("/\\[\\[mailto:([^\\s$UrlExcludeChars]*)/", create_function('$m','return "[[hidden-email:".trim(eProtectStrRecode($m[1]));'), $text);
  $text = preg_replace_callback("/\\[\\[(.*?)mailto:([^\\s$UrlExcludeChars]*)(.*?)\\]\\]/", create_function('$m','return "[[".$m[1]."hidden-email:".trim(eProtectStrRecode($m[2])).$m[3]."]]";'), $text);
  $text = preg_replace("/$KeepToken(\\d+)$KeepToken/e",'$KPV[$1]',$text);   // put the [= .. =] back in to the text
  $new['text'] = $text;
}

?>
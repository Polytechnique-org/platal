<?php

// spool config in spool.inc.php
$news['maxspool'] = 2000;

// encoded headers
$news['hdecode'] = array('from','name','organization','subject');

// headers in post
$news['head'] = array('from', 'subject', 'newsgroups', 'message-id', 'followup-to', 'date',
    'x-org-id', 'organization', 'references', 'x-face', 'content-type', 'content-transfer-encoding');

// headers in article.php
$news['headdisp'] = array(
  'from',
  'subject',
  'newsgroups',
  'followup-id',
  'date',
  'organization',
  'references',
  'x-org-id',
  'x-face',
);
$locale['headers']['xorgid']='Identité';

// overview configuration in article.php
$news['threadtop'] = 5;
$news['threadbottom'] = 5;

// wordwrap configuration
$news['wrap'] = 80;

// overview configuration in thread.php
$news['max'] = 50;

// custom headers in post.php
$news['customhdr'] = 
   "Date: ".date("r")."\n"
  ."Content-Type: text/plain; charset=iso-8859-15\n"
  ."Mime-Version: 1.0\n"
  ."Content-Transfer-Encoding: 8bit\n"
  ."HTTP-Posting-Host: ".gethostbyname($_SERVER['REMOTE_ADDR'])."\n"
  ."User-Agent: Banana 0.7.1\n";

$css = array(
 'bananashortcuts' => 'bananashortcuts',
 'bicol' => 'bicol',
 'bicoltitre' => 'bicoltitre',
 'bicolvpadd' => 'bicolvpadd',
 'pair' => 'pair',
 'impair' => 'impair',
 'bouton' => 'bouton',
 'error' => 'erreur',
 'normal' => 'normal',
 'total' => 'bananatotal',
 'unread' => 'bananaunread',
 'group' => 'bananagroup',
 'description' => 'bananadescription',
 'date' => 'bananadate',
 'subject' => 'bananasubject',
 'from' => 'bananafrom',
 'author' => 'author',
 'nopadd' => 'banananopadd',
 'overview' => 'bananaoverview',
 'tree' => 'bananatree'
);

?>

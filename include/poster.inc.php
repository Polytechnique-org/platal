<?php

/** classe pour poter des messages
 */
class poster {
  var $header, $body;
  var $from, $newsgroups, $subject;

  function poster($from, $newsgroups, $subject) {
    $this->from = $from;
    $this->newsgroups = $newsgroups;
    $this->subject = $subject;
    $this->body = "";
    $this->header = "User-Agent: PHP/" . phpversion()."\n".
                    "Mime-Version: 1.0\n";
    $this->header .=
	    "Content-Type: text/plain; charset=iso-8859-1\n".
    	"Content-Transfer-Encoding: 8bit\n";
  }

  function addHeader($text)
  {
    $this->header .= "$text\n";
  }
  
  function setBody($text)
  {
    $this->body = $text;
  }

  function post()
  {
    global $news_server,$news_port,$news_web_pass,$news_web_user;
    $this->header .= "From: {$this->from}\n";
    $this->header .= "Subject: {$this->subject}\n";
    $this->header .= "Newsgroups: {$this->newsgroups}\n";
    $this->header .= "\n";

    $nntp = new nntp("$news_server:$news_port");
    if (!$nntp->authinfo($news_web_user,$news_web_pass)) {
      $nntp->quit();
      return false;
    }
    $res = $nntp->post($this->header.$this->body);
    $nntp->quit();
    return $res;
  }
}
?>

<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: poster.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/


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

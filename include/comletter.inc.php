<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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
 ***************************************************************************/

// {{{ class ComLArticle

class ComLArticle
{
    // Maximum number of lines per article, as wanted by the NL master
    const MAX_LINES_PER_ARTICLE = 4;
    const MAX_CHARACTERS_PER_LINE = 68;

    // {{{ properties

    public $aid;
    public $cid;
    public $pos;
    public $title;
    public $body;
    public $append;

    // }}}
    // {{{ constructor

    function __construct($title='', $body='', $append='', $aid=-1, $cid=0, $pos=0)
    {
        $this->body   = $body;
        $this->title  = $title;
        $this->append = $append;
        $this->aid    = $aid;
        $this->cid    = $cid;
        $this->pos    = $pos;
    }

    // }}}
    // {{{ function title()

    public function title()
    { return trim($this->title); }

    // }}}
    // {{{ function body()

    public function body()
    { return trim($this->body); }

    // }}}
    // {{{ function append()

    public function append()
    { return trim($this->append); }

    // }}}
    // {{{ function toText()

    public function toText($hash = null, $login = null)
    {
        $title = '*'.$this->title().'*';
        $body = MiniWiki::WikiToText($this->body, true);
        $app = MiniWiki::WikiToText($this->append, false, 4);
        $text = trim("$title\n\n$body\n\n$app")."\n";
        if (!is_null($hash) && !is_null($login)) {
            $text = str_replace('%HASH%', "$hash/$login", $text);
        } else {
            $text = str_replace('%HASH%', '', $text);
        }
        return $text;
    }

    // }}}
    // {{{ function toHtml()

    public function toHtml($hash = null, $login = null)
    {
        $title = "<h2 class='xorg_nl'><a id='art{$this->aid}'></a>".pl_entities($this->title()).'</h2>';
        $body  = MiniWiki::WikiToHTML($this->body);
        $app   = MiniWiki::WikiToHTML($this->append);

        $art   = "$title\n";
        $art  .= "<div class='art'>\n$body\n";
        if ($app) {
            $art .= "<div class='app'>$app</div>";
        }
        $art  .= "</div>\n";
        if (!is_null($hash) && !is_null($login)) {
            $art = str_replace('%HASH%', "$hash/$login", $art);
        } else {
            $art = str_replace('%HASH%', '', $art);
        }

        return $art;
    }

    // }}}
    // {{{ function check()

    public function check()
    {
        $rest = $this->remain();

        return $rest['remaining_lines'] >= 0;
    }

    // }}}
    // {{{ function remain()

    public function remain()
    {
        $text  = MiniWiki::WikiToText($this->body);
        $array = explode("\n", wordwrap($text, self::MAX_CHARACTERS_PER_LINE));
        $lines_count = 0;
        foreach ($array as $line) {
            if (trim($line) != '') {
                ++$lines_count;
            }
        }

        return array(
            'remaining_lines'                    => self::MAX_LINES_PER_ARTICLE - $lines_count,
            'remaining_characters_for_last_line' => self::MAX_CHARACTERS_PER_LINE - strlen($array[count($array) - 1])
       );
    }
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>

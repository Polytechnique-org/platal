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
 ***************************************************************************/

// {{{ class XOrgWikiAST

class XOrgWikiAST
{
    // {{{ properties

    var $type;
    var $childs;

    // }}}
    // {{{ constructor
    
    function XOrgWikiAST($type, $childs = Array())
    {
        $this->type   = $type;
        $this->childs = $childs;
    }

    // }}}
    // {{{ debug function (more or less dunp 2 html)

    function _dump($level = 0) {
        echo str_pad('', 2*$level, ' ')."<{$this->type}>\n";
        foreach ($this->childs as $val) {
            if (is_string($val)) {
                echo str_pad('', 2*$level+2)."$val\n";
            } else {
                $val->_dump($level+1);
            }
        }
        echo str_pad('', 2*$level, ' ')."</{$this->type}>\n";
    }

    // }}}
}

// }}}
// {{{ class XOrgWiki

class XOrgWikiParser
{
    // {{{ constructor
    
    function XOrgWikiParser()
    {
    }

    // }}}
    // {{{ function parse

    function parse($in)
    {
        $input = str_replace("\r", '', $in);
        $input = str_replace("\n=", "\r", $input);
        $lines = array_map(Array($this, '_analyse'), split("\n", $input));
        return $this->_share_nests($lines);
    }

    // }}}
    /* private functions */
    // {{{ function _analyse

    function _analyse(&$line)
    {
        $modes = Array('!'=>'h1', '!!'=>'h2', '!!!'=>'h3', '>'=>'blockquote', '.'=>'pre', '*'=>'ul', '#'=>'ol');
        $types = Array();
        /* non - nesting blocks */
        if (preg_match('/^(!!?!?|[.>])/', $line, $m)) {
            $types[] = $modes[$m[1]];
            return Array($types, substr($line, strlen($m[1])));
        }

        /* nesting blocks */
        $pos = 0;
        while ($line{$pos} == '*' || $line{$pos} == '#') {
            $types[] = $modes[$line{$pos}];
            $pos ++;
        }

        /* nesting blocks ore special lines */
        if ($pos) {
            return Array($types, substr($line, $pos));
        } elseif ($line == '----') {
            return Array(Array('hr'), '');
        } elseif ($line) {
            return Array(Array('p'), $line);
        } else {
            return Array(Array('br'), '');
        }
    }
    
    // }}}
    // {{{ function _merge_nested

    function _share_nests($lines)
    {
        $res  = new XOrgWikiAST('div');

        while (count($lines)) {
            list($types, $line) = array_shift($lines);
            $nest = Array();
            while ($types[0] == 'ul' || $types[0] == 'ol') {
                $this->_merge_into($types, $line, $nest);
                list($types, $line) = array_shift($lines);
            }
            $nest[] = new XOrgWikiAST($types[0], $this->_parse_line($line));
            $res->childs = array_merge($res->childs, $nest);
        }

        return $res;
    }

    // }}}
    // {{{ function _merge_nest

    function _merge_into($types, $line, &$nest)
    {
        $ptr =& $nest;
        while (($l = count($ptr)) && count($types) && $ptr[$l-1]->type == $types[0]) {
            $pos =& $ptr[$l-1]->childs;
            $ptr =& $pos[count($pos)-1]->childs;
            array_shift($types);
        }

        $elt = $this->_parse_line($line);
        if (count($types)) {
            while (count($types)) {
                $elt = Array(new XOrgWikiAST(array_pop($types), Array(new XOrgWikiAST('li', $elt))));
            }
            $ptr[] = $elt[0];
        } else {
            $pos[] = new XOrgWikiAST('li', $elt);
        }
    }

    // }}}
    // {{{ function _parse_line

    function _parse_line($line)
    {
        // TODO
        return Array($line);
    }

    // }}}
}

// }}}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker: */
?>

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
    var $attrs;

    // }}}
    // {{{ constructor
    
    function XOrgWikiAST($type, $childs = Array(), $attrs = Array())
    {
        $this->type   = $type;
        $this->childs = $childs;
        $this->attrs  = $attrs;
    }

    // }}}
    // {{{ function normalize

    function normalize()
    {
        if ($this->type == 'pre') {
            return;
        }
        foreach ($this->childs as $key=>$val) {
            if (is_string($val)) {
                $val = preg_replace(',\s+,', ' ', $val);
                if ($key == 0) {
                    $val = ltrim($val);
                }
                if (empty($val)) {
                    unset($this->childs[$key]);
                } else {
                    $this->childs[$key] = $val;
                }
            } else {
                $this->childs[$key]->normalize();
            }
        }

        while ($val = end($this->childs)) {
            if (is_string($val)) {
                if ($val = rtrim($val)) {
                    $this->childs[key($this->childs)] = $val;
                    break;
                } else {
                    unset($this->childs[key($this->childs)]);
                }
            } else {
                break;
            }
        }
    }
    
    // }}}
    // {{{ function render

    function render($engine)
    {
        return $engine->render($this);
    }

    // }}}

    /* private */

    // {{{ debug function (more or less dunp 2 html)

    function _dump() {
        echo '<'.$this->type;
        foreach ($this->attrs as $attr=>$val) {
            printf(' %s="%s"', $attr, $val);
        }
        if (!count($this->childs)) { echo " />"; return; }
        echo '>';
        foreach ($this->childs as $val) {
            if (is_string($val)) {
                echo $val;
            } else {
                $val->_dump();
            }
        }
        echo "</{$this->type}>";
    }

    // }}}
}

// }}}
// {{{ class XOrgWikiParser

class XOrgWikiParser
{
    // {{{ properties

    var $max_title_level = 3;
    var $enable_img      = true;
    var $enable_hr       = true;

    // }}}
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
        $input = preg_replace(',(^|[^|])((https?|ftp)://[^\r\n\t ]*),', '\1[\2|\2]', $input);
        $input = preg_replace(',(^|[^|])(([a-zA-Z0-9\-_+.]*@[a-zA-Z0-9\-_+.]*)(?:\?[^\r\n\t ]*)?),','\1[\2|\2]', $input);
        $lines = array_map(Array($this, '_analyse'), split("\n", $input));
        return $this->_share_nests($lines);
    }

    // }}}

    /* private functions */

    // {{{ function _analyse

    function _analyse(&$line)
    {
        $types = Array();
        $modes = Array( '>'=>'blockquote', '.'=>'pre', '-'=>'ul', '#'=>'ol');

        for ($i = 1; $i <= $this->max_title_level; $i++) {
            $modes[str_pad('!', $i, '!')] = "h$i";
        }

        /* non - nesting blocks */
        $hre = $this->max_title_level ? str_pad('!', $this->max_title_level * 2 - 1, '!?') : '';
        
        if (preg_match("/^($hre|[.>])/", $line, $m)) {
            $types[] = $modes[$m[1]];
            return Array($types, substr($line, strlen($m[1])));
        }

        /* hr */
        if ($this->enable_hr && $line == '----') {
            return Array(Array('hr'), '');
        }

        /* nesting blocks */
        $pos = 0;
        while ($line{$pos} == '-' || $line{$pos} == '#') {
            $types[] = $modes[$line{$pos}];
            $pos ++;
        }

        /* nesting blocks or special lines */
        if ($pos) {
            return Array($types, substr($line, $pos));
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

        $res->normalize();
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
        $lexm = Array();
        $i    = 0;
        $cur  = '';
        $len  = strlen($line);
        while ($i < $len) {
            switch ($c = $line{$i}) {
                case "\r":
                    $lexm[] = $cur;
                    $lexm[] = new XOrgWikiAST('br');
                    $cur = '';
                    $i ++;
                    break;

                case '\\':
                    if ($i + 1 < $len) {
                        if (strpos('*/_{}[()', $d = $line{$i+1}) !== false) {
                            $cur .= $d;
                            $i += 2;
                            break;
                        }
                    }
                    $cur .= '\\';
                    $i ++;
                    break;

                case '*':
                case '/':
                case '_':
                    if (preg_match(",^[$c][$c](.*?[^\\\\])[$c][$c],", substr($line, $i), $m)) {
                        $lexm[] = $cur;
                        $type   = ($c == '*' ? 'strong' : ($c == '/' ? 'em' : 'u'));
                        $lexm[] = new XOrgWikiAST($type, $this->_parse_line($m[1]));
                        $cur    = '';
                        $i     += strlen($m[0]);
                        break;
                    }
                    $cur .= $line{$i};
                    $i ++;
                    break;

                case '{':
                    if (preg_match(",^{{(.*?[^\\\\])}},", substr($line, $i), $m)) {
                        $lexm[] = $cur;
                        $lexm[] = new XOrgWikiAST('tt', $this->_parse_line($m[1]));
                        $cur    = '';
                        $i     += strlen($m[0]);
                        break;
                    }
                    $cur .= $line{$i};
                    $i ++;
                    break;

                case '(':
                    if (!$this->enable_img) {
                        break;
                    }
                case '[':
                    $re = ( $c=='[' ? ',^\[([^|]*)\|([^]]*)\],' : ',^\(([^|]*)\|([^)]*)\),' );
                    if (preg_match($re, substr($line, $i), $m)) {
                        $lexm[] = $cur;
                        if ($c == '[') {
                            $lexm[] = new XOrgWikiAST('a', Array($m[1]), Array('href'=>$m[2]));
                        } else {
                            $lexm[] = new XOrgWikiAST('img', Array($m[1]), Array('src'=>$m[2]));
                        }
                        $cur    = '';
                        $i     += strlen($m[0]);
                        break;
                    }
                    $cur .= $line{$i};
                    $i ++;
                    break;

                default:
                    $cur .= $line{$i};
                    $i ++;
            }
        }
        $lexm[] = $cur;
        return $lexm;
    }

    // }}}
}

// }}}
// {{{ class XOrgWikiToText

class XOrgWikiToText
{
    // {{{ constructor
    
    function XOrgWikiToText()
    {
    }

    // }}}
    // {{{ function render

    function render($AST, $idt = '') {
        $res = $this->_render($AST, $idt, false, true);
        return substr($res, $res{0} == "\n")."\n";
    }

    // }}}

    /* private */

    // {{{ function _render
    
    function _render($AST, $idt, $list, $fst)
    {
        $res = '';
        if ($AST->type == 'hr') {
            return "\n ---------------------------------------------------------------------- ";
        }
        if (strpos('|br|div|p|pre|h1|h2|h3|li|', "|{$AST->type}|")!==false && !$fst) {
            $res .= "\n$idt";
        }
        if ($AST->type == 'ol' || $AST->type == 'ul') {
            if ($list) {
                $res .= $idt;
            } else {
                $list = true;
            }
        } elseif ($AST->type == 'li') {
            $res .= '  - ';
            $idt .= '    ';
        } elseif ($AST->type == 'u') {
            $res .= '_';
        } elseif ($AST->type == 'em') {
            $res .= '/';
        } elseif ($AST->type == 'strong') {
            $res .= '*';
        } elseif ($AST->type == 'a') {
            return "[{$AST->attrs['href']}]";
        } elseif ($AST->type == 'img') {
            return "[{$AST->attrs['src']}]";
        }
        foreach ($AST->childs as $val) {
            $fst  = false;
            $res .= is_string($val) ?  $val : $this->_render($val, $idt, $list, $fst);
        }
        if ($AST->type == 'u') {
            $res .= '_';
        } elseif ($AST->type == 'em') {
            $res .= '/';
        } elseif ($AST->type == 'strong') {
            $res .= '*';
        }
        return $res;
    }

    // }}}
}

// }}}

/* vim: set expandtab shiftwidth=4 tabstop=4 softtabstop=4 foldmethod=marker: */
?>

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

// {{{ class VarStream
 
class VarStream
{
    // {{{ properties
    
    // Stream handler to read from global variables
    var $varname;
    var $position;

    // }}}
    // {{{ stream_open

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url            = parse_url($path);
        $this->varname  = $url['host'];
        $this->position = 0;
        if (!isset($GLOBALS[$this->varname]))
        {
            trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
            return false;
        }
        return true;
    }

    // }}}
    // {{{ stream_close

    function stream_close()
    {
    }
    
    // }}}
    // {{{ stream_read

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    // }}}
    // {{{ stream_write

    function stream_write($data)
    {
        $len = strlen($data);
        if ($len > $this->position + strlen($GLOBALS[$this->varname])) {
            str_pad($GLOBALS[$this->varname], $len);
        }

        $GLOBALS[$this->varname] = substr_replace($GLOBALS[$this->varname], $data, $this->position, $len);
        $this->position += $len;
    }
    
    // }}}
    // {{{ stream_eof

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    // }}}
    // {{{ stream_tell

    function stream_tell()
    {
        return $this->position;
    }

    // }}}
    // {{{ stream_seek

    function stream_seek($offs, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                $final = $offs;
                break;

            case SEEK_CUR:
                $final += $offs;
                break;

            case SEEK_END:
                $final = strlen($GLOBALS[$this->varname]) + $offs;
                break;
        }

        if ($final < 0) {
            return -1;
        }
        $this->position = $final;
        return 0;
    }

    // }}}
    // {{{ stream_flush

    function stream_flush()
    {
    }
    
    // }}}
}

// }}}

stream_wrapper_register('var','VarStream');

// vim:set et sw=4 sts=4 sws=4:
?>

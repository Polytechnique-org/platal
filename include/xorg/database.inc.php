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
 **************************************************************************/

require_once('diogenes.database.inc.php');

// {{{ class XOrgDB

class XOrgDB
{
    // {{{ properties
   
    var $_db;
    
    // }}}
    // {{{ constructor

    function XOrgDB(&$diog_db)
    {
        $this->_db =& $diog_db;
    }

    // }}}
    // {{{ function query

    function &query()
    {
        $args     = func_get_args();
        $query    = array_map(Array($this, '_db_escape'), $args);
        $query[0] = $args[0];
        return new XOrgDBResult(call_user_func_array('sprintf', $query), $this->_db);
    }

    // }}}
    // {{{ function iterator()

    function &iterator()
    {
        $args     = func_get_args();
        $query    = array_map(Array($this, '_db_escape'), $args);
        $query[0] = $args[0];
        return new XOrgDBIterator(call_user_func_array('sprintf', $query), $this->_db);
    }
    
    // }}}
    // {{{ function _db_escape

    function _db_escape(&$var)
    {
        switch (gettype($var)) {
            case 'boolean':
                return $var ? 1 : 0;
            
            case 'integer':
            case 'double':
            case 'float':
                return $var;

            case 'string':
                if (get_magic_quotes_gpc()) {
                    return addslashes(stripslashes($var));
                } else {
                    return addslashes($var);
                }

            case 'NULL':
                return 'NULL';

            case 'object':
            case 'array':
                return addslashes(serialize($var));

            default:
                die(var_export($var, true).' is not a valid for a database entry');
        }
    }

    // }}}
}

// }}}
// {{{ class XOrgDBResult

class XOrgDBResult
{
    // {{{ properties

    var $_res;

    // }}}
    // {{{ constructor

    function XOrgDBResult($query, &$db)
    {
        $this->_res =& $db->query($query);
    }

    // }}}
    // {{{ destructor

    function free()
    {
        mysql_free_result($this->_res);
        unset($this);
    }

    // }}}
    // {{{ function fetchRow

    function &fetchRow()
    {
        return mysql_fetch_row($this->_res);
    }

    // }}}
    // {{{ function fetchAssoc

    function &fetchAssoc()
    {
        return mysql_fetch_assoc($this->_res);
    }

    // }}}
    // {{{ function fetchAllRow

    function &fetchAllRow()
    {
        $result = Array();
        while ($result[] = mysql_fetch_row($this->_res)) { }
        array_pop($result);
        return $result;
    }

    // }}}
    // {{{ function fetchAssoc

    function &fetchAllAssoc()
    {
        $result = Array();
        while ($result[] = mysql_fetch_assoc($this->_res)) { }
        array_pop($result);
        return $result;
    }

    // }}}
    // {{{ function numRows
    
    function numRows()
    {
        return mysql_num_rows($this->_res);
    }

    // }}}
}

// }}}
// {{{ class XOrgDBIterator

class XOrgDBIterator extends XOrgIterator
{
    // {{{ properties

    var $_result;
    var $_pos;
    var $_total;
    var $_mode = MYSQL_ASSOC;

    // }}}
    // {{{
    
    function XOrgDBIterator($query, &$db, $mode = MYSQL_ASSOC)
    {
        $this->_result =& new XOrgDBResult($query, $db);
        $this->_pos    = 0;
        $this->_total  = $this->_result->numRows();
        $this->_mode   = $mode;
    }

    // }}}
    // {{{ function next ()
    
    function &next()
    {
        $this->_pos ++;
        if ($this->_pos > $this->_total) {
            $this->_result->free();
            unset($this);
            return null;
        }
        return $this->_mode != MYSQL_ASSOC ? $this->_result->fetchRow() : $this->_result->fetchAssoc();
    }

    // }}}
    // {{{ function first

    function first()
    {
        return $this->_pos == 1;
    }

    // }}}
    // {{{ function last

    function last()
    {
        return $this->_last == $this->_total;
    }

    // }}}
    // {{{ function total()

    function total()
    {
        return $this->_total;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

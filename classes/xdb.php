<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

class XDB
{
    var $_trace_data = array();

    public static function _prepare($args)
    {
        $query    = array_map(Array('XDB', '_db_escape'), $args);
        $query[0] = str_replace('{?}', '%s', str_replace('%',   '%%', $args[0]));
        return call_user_func_array('sprintf', $query);
    }

    public static function _reformatQuery($query)
    {
        $query  = preg_split("/\n\\s*/", trim($query));
        $length = 0;
        foreach ($query as $key=>$line) {
            $local = -2;
            if (preg_match('/^([A-Z]+(?:\s+(?:JOIN|BY|FROM|INTO))?)\s+(.*)/', $line, $matches)
            && $matches[1] != 'AND' && $matches[1] != 'OR')
            {
                $local  = strlen($matches[1]);
                $line   = $matches[1] . '  ' . $matches[2];
                $length = max($length, $local);
            }
            $query[$key] = array($line, $local);
        }
        $res = '';
        foreach ($query as $array) {
            list($line, $local) = $array;
            $local   = max(0, $length - $local);
            $res    .= str_repeat(' ', $local) . $line . "\n";
            $length += 2 * (substr_count($line, '(') - substr_count($line, ')'));
        }
        return $res;
    }

    public static function _query($query)
    {
        global $globals;

        if ($globals->debug & 1) {
            $_res = mysql_query("EXPLAIN $query");
            $explain = array();
            while ($row = @mysql_fetch_assoc($_res)) {
                $explain[] = $row;
            }
            $trace_data = array('query' => XDB::_reformatQuery($query), 'explain' => $explain);
            @mysql_free_result($_res);
            $time_start = microtime();
        }

        $res = mysql_query($query);

        if ($globals->debug & 1) {
            list($ue, $se) = explode(" ", microtime());
            list($us, $ss) = explode(" ", $time_start);
            $time = intval((($ue - $us) + ($se - $ss)) * 1000);            
            $trace_data['error'] = mysql_error();
            $trace_data['exectime'] = $time;
            $trace_data['rows'] = @mysql_num_rows() ? mysql_num_rows() : mysql_affected_rows();
            $GLOBALS['XDB::trace_data'][] = $trace_data;
            if (mysql_errno()) {
                $GLOBALS['XDB::error'] = true;
            }
        }

        return $res;
    }

    public static function query()
    {
        return new XOrgDBResult(XDB::_prepare(func_get_args()));
    }

    public static function execute()
    {
        return XDB::_query(XDB::_prepare(func_get_args()));
    }

    public static function iterator()
    {
        return new XOrgDBIterator(XDB::_prepare(func_get_args()));
    }

    public static function iterRow()
    {
        return new XOrgDBIterator(XDB::_prepare(func_get_args()), MYSQL_NUM);
    }

    public static function insertId()
    {
        return mysql_insert_id();
    }

    public static function _db_escape($var)
    {
        switch (gettype($var)) {
          case 'boolean':
            return $var ? 1 : 0;

          case 'integer':
          case 'double':
          case 'float':
            return $var;

          case 'string':
            return "'".addslashes($var)."'";

          case 'NULL':
            return 'NULL';

          case 'object':
          case 'array':
            return "'".addslashes(serialize($var))."'";

          default:
            die(var_export($var, true).' is not a valid for a database entry');
        }
    }

    public static function trace_format(&$page, $template = 'skin/common.database-debug.tpl') {
        $page->assign('trace_data', @$GLOBALS['XDB::trace_data']);
        $page->assign('db_error', @$GLOBALS['XDB::error']);
        return $page->fetch($template);
    }
}

class XOrgDBResult
{

    var $_res;

    function XOrgDBResult($query)
    {
        $this->_res = XDB::_query($query);
    }

    function free()
    {
        mysql_free_result($this->_res);
        unset($this);
    }

    function _fetchRow()
    {
        return mysql_fetch_row($this->_res);
    }

    function _fetchAssoc()
    {
        return mysql_fetch_assoc($this->_res);
    }

    function fetchAllRow()
    {
        $result = Array();
        while ($result[] = mysql_fetch_row($this->_res)) { }
        array_pop($result);
        $this->free();
        return $result;
    }

    function fetchAllAssoc()
    {
        $result = Array();
        while ($result[] = mysql_fetch_assoc($this->_res)) { }
        array_pop($result);
        $this->free();
        return $result;
    }

    function fetchOneAssoc()
    {
        $tmp = $this->_fetchAssoc();
        $this->free();
        return $tmp;
    }

    function fetchOneRow()
    {
        $tmp = $this->_fetchRow();
        $this->free();
        return $tmp;
    }

    function fetchOneCell()
    {
        $tmp = $this->_fetchRow();
        $this->free();
        return $tmp[0];
    }

    function fetchColumn($key = 0)
    {
        $res = Array();
        if (is_numeric($key)) {
            while($tmp = $this->_fetchRow()) {
                $res[] = $tmp[$key];
            }
        } else {
            while($tmp = $this->_fetchAssoc()) {
                $res[] = $tmp[$key];
            }
        }
        $this->free();
        return $res;
    }

    function numRows()
    {
        return mysql_num_rows($this->_res);
    }
}

class XOrgDBIterator
{
    private $_result;
    private $_pos;
    private $_total;
    private $_mode = MYSQL_ASSOC;

    function __construct($query, $mode = MYSQL_ASSOC)
    {
        $this->_result = new XOrgDBResult($query);
        $this->_pos    = 0;
        $this->_total  = $this->_result->numRows();
        $this->_mode   = $mode;
    }

    function next()
    {
        $this->_pos ++;
        if ($this->_pos > $this->_total) {
            $this->_result->free();
            unset($this);
            return null;
        }
        return $this->_mode != MYSQL_ASSOC ? $this->_result->_fetchRow() : $this->_result->_fetchAssoc();
    }

    function first()
    {
        return $this->_pos == 1;
    }

    function last()
    {
        return $this->_last == $this->_total;
    }

    function total()
    {
        return $this->_total;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>

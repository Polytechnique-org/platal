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
    private static $mysqli = null;

    public static function connect()
    {
        global $globals;
        XDB::$mysqli = new mysqli($globals->dbhost, $globals->dbuser, $globals->dbpwd, $globals->dbdb);
        if (mysqli_connect_errno() && $globals->debug & 1) {
            $GLOBALS['XDB::trace_data'][] = array('query' => 'MySQLI connection', 'explain' => array(),
                                                  'error' => mysqli_connect_error(), 'exectime' => 0, 'rows' => 0);
            $GLOBALS['XDB::error'] = true;
            return false;
        }
        XDB::$mysqli->autocommit(true);
        XDB::$mysqli->set_charset($globals->dbcharset);
        return true;
    }

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
            if (preg_match('/^([A-Z]+(?:\s+(?:JOIN|BY|FROM|INTO))?)\s+(.*)/u', $line, $matches)
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

        if (!XDB::$mysqli && !XDB::connect()) {
            return false;
        }

        if ($globals->debug & 1) {
            $explain = array();
            if (strpos($query, 'FOUND_ROWS()') === false) {
                $res = XDB::$mysqli->query("EXPLAIN $query");
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        $explain[] = $row;
                    }
                    $res->free();
                }
            }
            $trace_data = array('query' => XDB::_reformatQuery($query), 'explain' => $explain);
            $time_start = microtime();
        }

        $res = XDB::$mysqli->query($query);
        
        if ($globals->debug & 1) {
            list($ue, $se) = explode(" ", microtime());
            list($us, $ss) = explode(" ", $time_start);
            $time = intval((($ue - $us) + ($se - $ss)) * 1000);            
            $trace_data['error'] = XDB::$mysqli->error;
            $trace_data['errno'] = XDB::$mysqli->errno;
            $trace_data['exectime'] = $time;
            $trace_data['rows'] = @$res->num_rows ? $res->num_rows : XDB::$mysqli->affected_rows;
            $GLOBALS['XDB::trace_data'][] = $trace_data;
            if (XDB::$mysqli->errno) {
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
        return XDB::$mysqli->insert_id;
    }

    public static function errno()
    {
        global $globals;
        if ($globals->debug & 1) {
            $count = count($GLOBALS['XDB::trace_data']);
            if (!$count) {
                return 0;
            }
            return $GLOBALS['XDB::trace_data'][$count - 1]['errno'];
        }
        return XDB::$mysqli->errno;
    }

    public static function error()
    {       
        global $globals;
        if ($globals->debug & 1) {
            $count = count($GLOBALS['XDB::trace_data']);
            if (!$count) {
                return null;
            }
            return $GLOBALS['XDB::trace_data'][$count - 1]['error'];
        }   
        return XDB::$mysqli->error;
    }

    public static function affectedRows()
    {
        return XDB::$mysqli->affected_rows;
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

    private $_res;

    function XOrgDBResult($query)
    {
        $this->_res = XDB::_query($query);
    }

    function free()
    {
        $this->_res->free();
        unset($this);
    }

    function _fetchRow()
    {
        return $this->_res->fetch_row();
    }

    function _fetchAssoc()
    {
        return $this->_res->fetch_assoc();
    }

    function fetchAllRow()
    {
        $result = Array();
        while ($result[] = $this->_res->fetch_row());
        array_pop($result);
        $this->free();
        return $result;
    }

    function fetchAllAssoc()
    {
        $result = Array();
        while ($result[] = $this->_res->fetch_assoc());
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

    function fetchOneField()
    {
        return $this->_res->fetch_field();
    }

    function fetchFields()
    {
        $res = array();
        while ($res[] = $this->fetchOneField());
        return $res;
    }

    function numRows()
    {
        return $this->_res->num_rows;
    }

    function fieldCount()
    {
        return $this->_res->field_count;
    }
}

class XOrgDBIterator
{
    private $_result;
    private $_pos;
    private $_total;
    private $_fpos;
    private $_fields;
    private $_mode = MYSQL_ASSOC;

    function __construct($query, $mode = MYSQL_ASSOC)
    {
        $this->_result = new XOrgDBResult($query);
        $this->_pos    = 0;
        $this->_total  = $this->_result->numRows();
        $this->_fpost  = 0;
        $this->_fields = $this->_result->fieldCount();
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
        return $this->_pos == $this->_total;
    }

    function total()
    {
        return $this->_total;
    }

    function nextField()
    {
        $this->_fpos++;
        if ($this->_fpos > $this->_fields) {
            return null;
        }
        return $this->_result->fetchOneField();
    }

    function firstField()
    {
        return $this->_fpos == 1;
    }

    function lastField()
    {
        return $this->_fpos == $this->_fields;
    }

    function totalFields()
    {
        return $this->_fields;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

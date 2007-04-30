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
        if ($globals->debug & DEBUG_BT) {
            $bt = new PlBacktrace('MySQL');
            if (mysqli_connect_errno()) {
                $bt->newEvent("MySQLI connection", 0, mysqli_connect_error());
                return false;
            }
        }
        XDB::$mysqli->autocommit(true);
        XDB::$mysqli->set_charset($globals->dbcharset);
        return true;
    }

    public static function _prepare($args)
    {
        $query    = array_map(Array('XDB', 'escape'), $args);
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

        if ($globals->debug & DEBUG_BT) {
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
            PlBacktrace::$bt['MySQL']->start(XDB::_reformatQuery($query));
        }

        $res = XDB::$mysqli->query($query);

        if ($globals->debug & DEBUG_BT) {
            PlBacktrace::$bt['MySQL']->stop(@$res->num_rows ? $res->num_rows : XDB::$mysqli->affected_rows,
                                            XDB::$mysqli->error,
                                            $explain);
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
        return XDB::$mysqli->errno;
    }

    public static function error()
    {       
        return XDB::$mysqli->error;
    }

    public static function affectedRows()
    {
        return XDB::$mysqli->affected_rows;
    }

    public static function escape($var)
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
}

class XOrgDBResult
{

    private $_res;

    public function __construct($query)
    {
        $this->_res = XDB::_query($query);
    }

    public function free()
    {
        if ($this->_res) {
            $this->_res->free();
        }
        unset($this);
    }

    protected function _fetchRow()
    {
        return $this->_res ? $this->_res->fetch_row() : null;
    }

    protected function _fetchAssoc()
    {
        return $this->_res ? $this->_res->fetch_assoc() : null;
    }

    public function fetchAllRow()
    {
        $result = Array();
        if (!$this->_res) {
            return $result;
        }
        while ($result[] = $this->_res->fetch_row());
        array_pop($result);
        $this->free();
        return $result;
    }

    public function fetchAllAssoc()
    {
        $result = Array();
        if (!$this->_res) {
            return $result;
        }
        while ($result[] = $this->_res->fetch_assoc());
        array_pop($result);
        $this->free();
        return $result;
    }

    public function fetchOneAssoc()
    {
        $tmp = $this->_fetchAssoc();
        $this->free();
        return $tmp;
    }

    public function fetchOneRow()
    {
        $tmp = $this->_fetchRow();
        $this->free();
        return $tmp;
    }

    public function fetchOneCell()
    {
        $tmp = $this->_fetchRow();
        $this->free();
        return $tmp[0];
    }

    public function fetchColumn($key = 0)
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

    public function fetchOneField()
    {
        return $this->_res ? $this->_res->fetch_field() : null;
    }

    public function fetchFields()
    {
        $res = array();
        while ($res[] = $this->fetchOneField());
        return $res;
    }

    public function numRows()
    {
        return $this->_res ? $this->_res->num_rows : 0;
    }

    public function fieldCount()
    {
        return $this->_res ? $this->_res->field_count : 0;
    }
}

require_once dirname(__FILE__) . '/pliterator.php';

class XOrgDBIterator extends XOrgDBResult implements PlIterator
{
    private $_result;
    private $_pos;
    private $_total;
    private $_fpos;
    private $_fields;
    private $_mode = MYSQL_ASSOC;

    public function __construct($query, $mode = MYSQL_ASSOC)
    {
        parent::__construct($query);
        $this->_pos    = 0;
        $this->_total  = $this->numRows();
        $this->_fpost  = 0;
        $this->_fields = $this->fieldCount();
        $this->_mode   = $mode;
    }

    public function next()
    {
        $this->_pos ++;
        if ($this->_pos > $this->_total) {
            $this->free();
            unset($this);
            return null;
        }
        return $this->_mode != MYSQL_ASSOC ? $this->_fetchRow() : $this->_fetchAssoc();
    }

    public function first()
    {
        return $this->_pos == 1;
    }

    public function last()
    {
        return $this->_pos == $this->_total;
    }

    public function total()
    {
        return $this->_total;
    }

    public function nextField()
    {
        $this->_fpos++;
        if ($this->_fpos > $this->_fields) {
            return null;
        }
        return $this->fetchOneField();
    }

    public function firstField()
    {
        return $this->_fpos == 1;
    }

    public function lastField()
    {
        return $this->_fpos == $this->_fields;
    }

    public function totalFields()
    {
        return $this->_fields;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

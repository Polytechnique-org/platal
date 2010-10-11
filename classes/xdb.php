<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
        self::$mysqli = new mysqli($globals->dbhost, $globals->dbuser, $globals->dbpwd, $globals->dbdb);
        if ($globals->debug & DEBUG_BT) {
            $bt = new PlBacktrace('MySQL');
            if (mysqli_connect_errno()) {
                $bt->newEvent("MySQLI connection", 0, mysqli_connect_error());
                return false;
            }
        }
        self::$mysqli->autocommit(true);
        self::$mysqli->set_charset($globals->dbcharset);
        return true;
    }

    public static function prepare($args)
    {
        global $globals;
        $query    = array_map(Array('XDB', 'escape'), $args);
        $query[0] = preg_replace('/#([a-z0-9]+)#/', $globals->dbprefix . '$1', $args[0]);
        $query[0] = str_replace('%',   '%%', $query[0]);
        $query[0] = str_replace('{?}', '%s', $query[0]);
        return call_user_func_array('sprintf', $query);
    }

    public static function reformatQuery($query)
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

    public static function run($query)
    {
        global $globals;

        if (!self::$mysqli && !self::connect()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            Platal::page()->kill('Impossible de se connecter à la base de données.');
            exit;
        }

        if ($globals->debug & DEBUG_BT) {
            $explain = array();
            if (strpos($query, 'FOUND_ROWS()') === false && strpos($query, 'AUTOCOMMIT') === false) {
                $res = self::$mysqli->query("EXPLAIN $query");
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        $explain[] = $row;
                    }
                    $res->free();
                }
            }
            PlBacktrace::$bt['MySQL']->start(XDB::reformatQuery($query));
        }

        $res = XDB::$mysqli->query($query);

        if ($globals->debug & DEBUG_BT) {
            PlBacktrace::$bt['MySQL']->stop(@$res->num_rows ? $res->num_rows : self::$mysqli->affected_rows,
                                            self::$mysqli->error,
                                            $explain);
        }

        if ($res === false) {
            throw new XDBException(XDB::reformatQuery($query), XDB::$mysqli->error);
        }
        return $res;
    }

    private static function queryv($query)
    {
        return new XDBResult(self::prepare($query));
    }

    public static function query()
    {
        return self::queryv(func_get_args());
    }

    public static function rawQuery($query)
    {
        return new XDBResult($query);
    }

    public static function format()
    {
        return self::prepare(func_get_args());
    }

    // Produce the SQL statement for setting/unsetting a flag
    public static function changeFlag($fieldname, $flagname, $state)
    {
        if ($state) {
            return XDB::format($fieldname . ' = CONCAT({?}, \',\', ' . $fieldname . ')', $flagname);
        } else {
            return XDB::format($fieldname . ' = REPLACE(' . $fieldname . ', {?}, \'\')', $flagname);
        }
    }

    // Produce the SQL statement representing an array
    public static function formatArray(array $array)
    {
        return self::escape($array);
    }

    const WILDCARD_EXACT    = 0x00;
    const WILDCARD_PREFIX   = 0x01;
    const WILDCARD_SUFFIX   = 0x02;
    const WILDCARD_CONTAINS = 0x03; // WILDCARD_PREFIX | WILDCARD_SUFFIX

    // Produce a valid XDB argument that get formatted as a wildcard
    // according to the given mode.
    //
    // Example:
    // XDB::query("SELECT * FROM table WHERE field {?}", XDB::wildcard($text, WILDCARD_EXACT));
    public static function wildcard($mode, $value)
    {
      return new XDBWildcard($value, $mode);
    }

    // Returns the SQL statement for a wildcard search.
    public static function formatWildcards($mode, $text)
    {
        return XDB::wildcard($mode, $text)->format();
    }

    // Returns a FIELD(blah, 3, 1, 2) for use in an order with custom orders
    public static function formatCustomOrder($field, $values)
    {
        return 'FIELD( ' . $field . ', ' . implode(', ', array_map(array('XDB', 'escape'), $values)) . ')';
    }

    public static function execute()
    {
        global $globals;
        $args = func_get_args();
        if ($globals->mode != 'rw' && !strpos($args[0], 'logger')) {
            return;
        }
        return self::run(XDB::prepare($args));
    }

    public static function rawExecute($query)
    {
        global $globals;
        if ($globals->mode != 'rw') {
            return;
        }
        return self::run($query);
    }

    private static $inTransaction = false;
    public static function startTransaction()
    {
        if (self::$inTransaction) {
            throw new XDBException('START TRANSACTION', 'Already in a transaction');
        }
        self::$inTransaction = true;
        self::rawExecute('SET AUTOCOMMIT = 0');
        self::rawExecute('START TRANSACTION');
    }

    public static function commit()
    {
        self::rawExecute('COMMIT');
        self::rawExecute('SET AUTOCOMMIT = 1');
        self::$inTransaction = false;
    }

    public static function rollback()
    {
        self::rawExecute('ROLLBACK');
        self::rawExecute('SET AUTOCOMMIT = 1');
        self::$inTransaction = false;
    }

    public static function runTransactionV($callback, array $args)
    {
        self::startTransaction();
        try {
            if (call_user_func_array($callback, $args)) {
                self::commit();
                return true;
            } else {
                self::rollback();
                return false;
            }
        } catch (Exception $e) {
            self::rollback();
            throw $e;
        }
    }

    /** This function takes a callback followed by the arguments to be passed to the callback
     * as arguments. It starts a transaction and execute the callback. If the callback fails
     * (return false or raise an exception), the transaction is rollbacked, if the callback
     * succeeds (return true), the transaction is committed.
     */
    public static function runTransaction()
    {
        $args = func_get_args();
        $cb = array_shift($args);
        self::runTransactionV($cb, $args);
    }

    public static function iterator()
    {
        return new XDBIterator(self::prepare(func_get_args()));
    }

    public static function rawIterator($query)
    {
        return new XDBIterator($query);
    }

    public static function iterRow()
    {
        return new XDBIterator(self::prepare(func_get_args()), MYSQL_NUM);
    }

    public static function rawIterRow($query)
    {
        return new XDBIterator($query, MYSQL_NUM);
    }

    private static function findQuery($params, $default = array())
    {
        for ($i = 0 ; $i < count($default) ; ++$i) {
            $is_query = false;
            foreach (array('insert', 'select', 'replace', 'delete', 'update') as $kwd) {
                if (stripos($params[0], $kwd) !== false) {
                    $is_query = true;
                    break;
                }
            }
            if ($is_query) {
                break;
            } else {
                $default[$i] = array_shift($params);
            }
        }
        return array($default, $params);
    }

    /** Fetch all rows returned by the given query.
     * This functions can take 2 optional arguments (cf XDBResult::fetchAllRow()).
     * Optional arguments are given *before* the query.
     */
    public static function fetchAllRow()
    {
        list($args, $query) = self::findQuery(func_get_args(), array(false, false));
        return self::queryv($query)->fetchAllRow($args[0], $args[1]);
    }

    public static function rawFetchAllRow($query, $id = false, $keep_array = false)
    {
        return self::rawQuery($query)->fetchAllRow($id, $keep_array);
    }

    /** Fetch all rows returned by the given query.
     * This functions can take 2 optional arguments (cf XDBResult::fetchAllAssoc()).
     * Optional arguments are given *before* the query.
     */
    public static function fetchAllAssoc()
    {
        list($args, $query) = self::findQuery(func_get_args(), array(false, false));
        return self::queryv($query)->fetchAllAssoc($args[0], $args[1]);
    }

    public static function rawFetchAllAssoc($query, $id = false, $keep_array = false)
    {
        return self::rawQuery($query)->fetchAllAssoc($id, $keep_array);
    }

    public static function fetchOneCell()
    {
        list($args, $query) = self::findQuery(func_get_args());
        return self::queryv($query)->fetchOneCell();
    }

    public static function rawFetchOneCell($query)
    {
        return self::rawQuery($query)->fetchOneCell();
    }

    public static function fetchOneRow()
    {
        list($args, $query) = self::findQuery(func_get_args());
        return self::queryv($query)->fetchOneRow();
    }

    public static function rawFetchOneRow($query)
    {
        return self::rawQuery($query)->fetchOneRow();
    }

    public static function fetchOneAssoc()
    {
        list($args, $query) = self::findQuery(func_get_args());
        return self::queryv($query)->fetchOneAssoc();
    }

    public static function rawFetchOneAssoc($query)
    {
        return self::rawQuery($query)->fetchOneAssoc();
    }

    /** Fetch a column from the result of the given query.
     * This functions can take 1 optional arguments (cf XDBResult::fetchColumn()).
     * Optional arguments are given *before* the query.
     */
    public static function fetchColumn()
    {
        list($args, $query) = self::findQuery(func_get_args(), array(0));
        return self::queryv($query)->fetchColumn($args[0]);
    }

    public static function rawFetchColumn($query, $key = 0)
    {
        return self::rawQuery($query)->fetchColumn($key);
    }

    public static function insertId()
    {
        return self::$mysqli->insert_id;
    }

    public static function errno()
    {
        return self::$mysqli->errno;
    }

    public static function error()
    {
        return self::$mysqli->error;
    }

    public static function affectedRows()
    {
        return self::$mysqli->affected_rows;
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
            if ($var instanceof XDBFormat) {
                return $var->format();
            } else {
                return "'".addslashes(serialize($var))."'";
            }

          case 'array':
            return '(' . implode(', ', array_map(array('XDB', 'escape'), $var)) . ')';

          default:
            die(var_export($var, true).' is not a valid for a database entry');
        }
    }
}

class XDBException extends PlException
{
    public function __construct($query, $error)
    {
        if (strpos($query, 'INSERT') === false && strpos($query, 'UPDATE') === false
            && strpos($query, 'REPLACE') === false && strpos($query, 'DELETE') === false) {
            $text = 'Erreur lors de l\'interrogation de la base de données';
        } else {
            $text = 'Erreur lors de l\'écriture dans la base de données';
        }
        parent::__construct($text, $query . "\n" . $error);
    }
}

interface XDBFormat
{
    public function format();
}

class XDBWildcard implements XDBFormat
{
    private $value;
    private $mode;

    public function __construct($value, $mode)
    {
        $this->value = $value;
        $this->mode  = $mode;
    }

    public function format()
    {
        if ($this->mode == XDB::WILDCARD_EXACT) {
            return XDB::format(' = {?}', $this->value);
        } else {
            $text = str_replace(array('%', '_'), array('\%', '\_'), $this->value);
            if ($this->mode & XDB::WILDCARD_PREFIX) {
                $text = $text . '%';
            }
            if ($this->mode & XDB::WILDCARD_SUFFIX) {
                $text = '%' . $text;
            }
            return XDB::format(" LIKE {?}", $text);
        }
    }
}


class XDBResult
{
    private $res;

    public function __construct($query)
    {
        $this->res = XDB::run($query);
    }

    public function free()
    {
        if ($this->res) {
            $this->res->free();
        }
        unset($this);
    }

    protected function fetchRow()
    {
        return $this->res ? $this->res->fetch_row() : null;
    }

    protected function fetchAssoc()
    {
        return $this->res ? $this->res->fetch_assoc() : null;
    }

    public function fetchAllRow($id = false, $keep_array = false)
    {
        $result = Array();
        if (!$this->res) {
            return $result;
        }
        while (($data = $this->res->fetch_row())) {
            if ($id !== false) {
                $key = $data[$id];
                unset($data[$id]);
                if (!$keep_array && count($data) == 1) {
                    reset($data);
                    $result[$key] = current($data);
                } else {
                    $result[$key] = $data;
                }
            } else {
                $result[] = $data;
            }
        }
        $this->free();
        return $result;
    }

    public function fetchAllAssoc($id = false, $keep_array = false)
    {
        $result = Array();
        if (!$this->res) {
            return $result;
        }
        while (($data = $this->res->fetch_assoc())) {
            if ($id !== false) {
                $key = $data[$id];
                unset($data[$id]);
                if (!$keep_array && count($data) == 1) {
                    reset($data);
                    $result[$key] = current($data);
                } else {
                    $result[$key] = $data;
                }
            } else {
                $result[] = $data;
            }
        }
        $this->free();
        return $result;
    }

    public function fetchOneAssoc()
    {
        $tmp = $this->fetchAssoc();
        $this->free();
        return $tmp;
    }

    public function fetchOneRow()
    {
        $tmp = $this->fetchRow();
        $this->free();
        return $tmp;
    }

    public function fetchOneCell()
    {
        $tmp = $this->fetchRow();
        $this->free();
        return $tmp[0];
    }

    public function fetchColumn($key = 0)
    {
        $res = Array();
        if (is_numeric($key)) {
            while($tmp = $this->fetchRow()) {
                $res[] = $tmp[$key];
            }
        } else {
            while($tmp = $this->fetchAssoc()) {
                $res[] = $tmp[$key];
            }
        }
        $this->free();
        return $res;
    }

    public function fetchOneField()
    {
        return $this->res ? $this->res->fetch_field() : null;
    }

    public function fetchFields()
    {
        $res = array();
        while ($res[] = $this->fetchOneField());
        return $res;
    }

    public function numRows()
    {
        return $this->res ? $this->res->num_rows : 0;
    }

    public function fieldCount()
    {
        return $this->res ? $this->res->field_count : 0;
    }
}


class XDBIterator extends XDBResult implements PlIterator
{
    private $result;
    private $pos;
    private $total;
    private $fpos;
    private $fields;
    private $mode = MYSQL_ASSOC;

    public function __construct($query, $mode = MYSQL_ASSOC)
    {
        parent::__construct($query);
        $this->pos    = 0;
        $this->total  = $this->numRows();
        $this->fpost  = 0;
        $this->fields = $this->fieldCount();
        $this->mode   = $mode;
    }

    public function next()
    {
        $this->pos ++;
        if ($this->pos > $this->total) {
            $this->free();
            unset($this);
            return null;
        }
        return $this->mode != MYSQL_ASSOC ? $this->fetchRow() : $this->fetchAssoc();
    }

    public function first()
    {
        return $this->pos == 1;
    }

    public function last()
    {
        return $this->pos == $this->total;
    }

    public function total()
    {
        return $this->total;
    }

    public function nextField()
    {
        $this->fpos++;
        if ($this->fpos > $this->fields) {
            return null;
        }
        return $this->fetchOneField();
    }

    public function firstField()
    {
        return $this->fpos == 1;
    }

    public function lastField()
    {
        return $this->fpos == $this->fields;
    }

    public function totalFields()
    {
        return $this->fields;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

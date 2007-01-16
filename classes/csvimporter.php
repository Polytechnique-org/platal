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
 ***************************************************************************/

define('CSV_INSERT',  'insert'); // INSERT IGNORE
define('CSV_REPLACE', 'replace'); // REPLACE
define('CSV_UPDATE',  'update'); // INSERT and UPDATE on error

class CSVImporter
{
    private $table;
    private $key;
    private $do_sql;

    private $index;
    private $data = array();

    private $user_functions = array();
    private $field_desc = array();
    private $field_value = array();

    public function __construct($table, $key = 'id', $do_sql = true)
    {
        $this->table     = $table;
        $this->key       = $key;
        $this->do_sql    = $do_sql;
    }

    private function processLine(array $array)
    {
        if (is_null($this->index)) {
            $this->index = array_map('strtolower', $array);
            return true;
        }

        if (count($array) != count($this->index)) {
            return false;
        }
        $assoc = array();
        $i     = 0;
        foreach ($this->index as $key) {
            $assoc[$key] = $array[$i];
            $i++;
        }
        $this->data[] = $assoc;
        return true;
    }

    private function getValue($line, $key, $action)
    {
        if (@array_key_exists($action, $line)) {
            $value = $line[$action];
        } elseif (is_callable($action, false)) {
            $value = call_user_func($action, $line, $key);
        } else {
            $value = $action;
        }
        if (is_null($value) || $value == 'NULL') {
            $value = 'NULL';
        }
        return $value;
    }

    private function makeAssoc($line, $relation)
    {
        $ops = array();
        foreach ($relation as $key=>$ref) {
            $ops[$key] = $this->getValue($line, $key, $ref);
        }
        return $ops;
    }

    private function makeRequestArgs($line, $relation)
    {
        $ops = array();
        foreach ($relation as $key=>$ref) {
            $value = $this->getValue($line, $key, $ref);
            if (!is_null($value) && $value != 'NULL') {
                $value = "'" . addslashes($value) . "'";
            }
            $ops[$key] = "$key = $value";
        }
        return $ops;
    }

    private function makeRelation()
    {
        $relation = array();
        foreach ($this->index as $title) {
            $relation[$title] = $title;
        }
        return $relation;
    }

    private function execute($query)
    {
        if (!$this->do_sql) {
            echo "$query;\n";
            return false;
        }
        return XDB::execute($query);
    }

    private function getFieldList()
    {
        $res = XDB::query("SHOW COLUMNS FROM {$this->table}");
        if ($res->numRows()) {
            return $res->fetchColumn();
        }
        return null;
    }

    public function setCSV($csv, $index = null, $separator = ';')
    {
        require_once dirname(__FILE__) . '/varstream.php';
        VarStream::init();
        global $csv_source;
        $this->index     = null;
        
        $csv_source = $csv;
        $res        = fopen('var://csv_source', 'r');

        while (!feof($res)) {
            $this->processLine(fgetcsv($res, 0, $separator));
        }
    }

    public function run($action = CSV_UPDATE, $insert_relation = null, $update_relation = null)
    {
        if (is_null($insert_relation)) {
            $insert_relation = $this->makeRelation();
        }
        if (is_null($update_relation)) {
            $update_relation = $insert_relation;
        }
        foreach ($this->data as $line) {
            $set = join(', ', $this->makeRequestArgs($line, $insert_relation));
            switch ($action) {
              case CSV_INSERT:
                $this->execute("INSERT IGNORE INTO {$this->table} SET $set");
                break;
              case CSV_REPLACE:
                $this->execute("REPLACE INTO {$this->table} SET $set");
                break;
              case CSV_UPDATE:
                if (!$this->execute("INSERT INTO {$this->table} SET $set")) {
                    $ops = $this->makeRequestArgs($line, $update_relation);
                    $set = join(', ', $ops);
                    $this->execute("UPDATE {$this->table} SET $set WHERE {$ops[$this->key]}");
                }
                break;
            }
        }
    }

    static public function dynamicCond($line, $key)
    {
        static $fields, $conds, $values, $thens, $elses;

        if (!isset($fields)) {
            $fields = Env::v('csv_cond_field');
            $conds  = Env::v('csv_cond');
            $values = Env::v('csv_cond_value');
            $thens  = Env::v('csv_cond_then');
            $elses  = Env::v('csv_cond_else');
        }
        $field = $line[$fields[$key]];
        $cond  = $conds[$key];
        $value = $values[$key];
        if (is_numeric($field) && is_numeric($value)) {
            $field = floatval($field);
            $value = floatval($value);
        }
        switch ($cond) {
            case 'defined':          $ok = (!empty($field)); break;
            case 'equals':           $ok = ($field == $value); break;
            case 'contains':         $ok = (strpos($field, $value) !== false); break;
            case 'contained':        $ok = (strpos($value, $field) !== false); break;
            case 'greater':          $ok = ($field > $value); break;
            case 'greater_or_equal': $ok ($field >= $value); break;
            case 'lower':            $ok = ($field < $value); break;
            case 'lower_or_equal':   $ok = ($field <= $value); break;
            default:                 $ok = false;
        }
        if ($ok) {
            return $thens[$key];
        } else {
            return $elses[$key];
        }
    }

    public function registerFunction($name, $desc, $callback)
    {
        if (is_callable($callback, false, $ref)) {
            $this->user_functions['func_' . $name] = array('desc' => $desc, 'callback' => $callback);
            return true;
        }
        return false;
    }

    public function describe($name, $desc)
    {
        $this->field_desc[$name] = $desc;
    }

    public function forceValue($name, $value)
    {
        $this->field_value[$name] = $value;
    }

    /** Handle insertion form
     * @param $page  PlatalPage to process
     * @param $url   URI of the page
     * @param $field Editable fields
     */
    public function apply(&$page, $url, $fields = null)
    {
        if (is_null($fields) || empty($fields)) {
            $fields = $this->getFieldList();
        }
        if (is_null($fields)) {
            return false;
        }
        foreach ($this->field_value as $key=>$value) {
            unset($fields[$key]);
        }

        $current = Env::v('csv_page');
        if (empty($current)) {
            $current = 'source';
        }
        $next = Env::v('csv_next_page');
        if (empty($next)) {
            $next = $current;
        }
        $csv  = Env::v('csv');
        if ($current == 'source' && Env::has('csv_valid')) {
            $csv = Env::v('csv_source');
            $next = 'values';
        }
        if ($csv) {
            $sep = Env::v('csv_separator');
            if (empty($sep)) {
                $sep = ';';
            }
            $this->setCSV($csv, null, $sep);
        }
        if ($current == 'values' && Env::has('csv_valid')) {
            $next = 'valid';
        }    
        if (empty($csv)) {
            $next = 'source';
        }
        if ($next == 'valid') {
            $insert   = Env::v('csv_value');
            $values   = Env::v('csv_user_value');
            $update   = Env::v('csv_update');
            foreach ($insert as $key=>$value) {
                if (empty($value)) {
                    $insert[$key] = null;
                } elseif ($value == 'user_value') {
                    $insert[$key] = $values[$key];
                } elseif ($value == 'cond_value') {
                    $insert[$key] = array($this, 'dynamicCond');
                } elseif (array_key_exists($value, $this->user_functions)) {
                    $insert[$key] = $this->user_functions[$value]['callback'];
                }
                if (isset($update[$key])) {
                    $update[$key] = $insert[$key];
                }
            }
            foreach ($this->field_value as $key=>$value) {
                $insert[$key] = $value;
            }
            if ($current == 'valid' && Env::has('csv_valid')) {
                $this->run(Env::v('csv_action'), $insert, $update);
                $page->assign('csv_done', true);
            } else {
                $preview = array();
                foreach ($this->data as $line) {
                    $preview[] = $this->makeAssoc($line, $insert);
                }
                $page->assign('csv_preview', $preview);
            }
        }
        $page->assign('csv_index', $this->index);
        $page->assign('csv_functions', $this->user_functions);
        $page->assign('csv_field_desc', $this->field_desc);
        $page->assign('csv_page', $next);
        $page->assign('csv_path', $url);
        $page->assign('csv_fields', $fields);  
        $page->assign('csv', $csv);
    }
}

?>

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
 ***************************************************************************/

class PlDBBadValueException extends PlException
{
    public function __construct($value, PlDBTableField $field, $reason)
    {
        parent::__construct('Erreur lors de l\'accès à la base de données',
                            'Illegal value '. (is_null($value) ? '(null)' : '(\'' . $value . '\')')
                            . ' for field (\'' . $field->table->table . '.' . $field->name . '\'): '
                            . $reason);
    }
}

class PlDBNoSuchFieldException extends PlException
{
    public function __construct($field, PlDBTable $table)
    {
        parent::__construct('Erreur lors de l\'accès à la base de données',
                            'No such field ' . $field . ' in table ' . $table->table);
    }
}

class PlDBIncompleteEntryDescription extends PlException
{
    public function __construct($field, PlDBTable $table)
    {
        parent::__construct('Erreur lors de l\'accès à la base de données',
                            'The field ' . $field . ' is required to describe an entry in table '
                            . $table->table);
    }
}

class PlDBTableField
{
    public $table;

    public $name;
    public $inPrimaryKey;
    public $inUniqueKey;
    public $inKey;

    public $type;
    public $typeLength;
    public $typeParameters;

    public $allowNull;
    public $defaultValue;
    public $autoIncrement;

    public function __construct(array $column)
    {
        $this->name = $column['Field'];
        $this->typeParameters = explode(' ', str_replace(array('(', ')', ',', '\''), ' ',
                                                         $column['Type']));
        $this->type = array_shift($this->typeParameters);
        if ($this->type == 'enum' || $this->type == 'set') {
            $this->typeParameters = new PlFlagSet(implode(',', $this->typeParameters));
        } else if (ctype_digit($this->typeParameters[0])) {
            $this->typeLength = intval($this->typeParameters[0]);
            array_shift($this->typeParameters);
        }
        $this->allowNull = ($column['Null'] === 'YES');
        $this->autoIncrement = (strpos($column['Extra'], 'auto_increment') !== false);
        $this->inPrimaryKey = ($column['Key'] === 'PRI');
        $this->inUniqueKey = $this->inPrimaryKey || ($column['Key'] === 'UNI');
        $this->inKey = $this->inUniqueKey || ($column['Key'] === 'MUL');

        try {
            $this->defaultValue = $this->format($column['Default']);
        } catch (PlDBBadValueException $e) {
            $this->defaultValue = null;
        }
    }

    public function format($value, $badNullFallbackToDefault = false)
    {
        if (is_null($value)) {
            if ($this->allowNull || $this->autoIncrement) {
                return $value;
            }
            if ($badNullFallbackToDefault) {
                return $this->defaultValue;
            }
            throw new PlDBBadValueException($value, $this, 'null not allowed');
        } else if ($this->type == 'enum') {
            if (!$this->typeParameters->hasFlag($value)) {
                throw new PlDBBadValueException($value, $this, 'invalid value for enum ' . $this->typeParameters->flags());
            }
            return $value;
        } else if ($this->type == 'set') {
            $value = new PlFlagSet($value);
            foreach ($value as $flag) {
                if (!$this->typeParameters->hasFlag($flag)) {
                    throw new PlDBBadValueException($value, $this, 'invalid flag for set ' . $this->typeParameters->flags());
                }
            }
            return $value;
        } else if (ends_with($this->type, 'int')) {
            if (!is_int($value) && !ctype_digit($value)) {
                throw new PlDBBadValueException($value, $this, 'value is not an integer');
            }
            $value = intval($value);
            if (count($this->typeParameters) > 0 && $this->typeParameters[0] == 'unsigned') {
                if ($value < 0) {
                    throw new PlDBBadValueException($value, $this, 'value is negative in an unsigned field');
                }
            }
            /* TODO: Check bounds */
            return $value;
        } else if ($this->type == 'varchar') {
            if (strlen($value) > $this->typeLength) {
                throw new PlDBBadValueException($value, $this, 'value is expected to be at most ' . $this->typeLength . ' characters long, ' . strlen($value) . ' given');
            }
            return $value;
        } else if ($this->type == 'char') {
            if (strlen($value) != $this->typeLength) {
                throw new PlDBBadValueException($value, $this, 'value is expected to be ' . $this->typeLength . ' characters long, ' . strlen($value) . ' given');
            }
            return $value;
        }
        /* TODO: Support data and times */
        return $value;
    }
}


/** This class aims at providing a simple interface to interact with a single
 * table of a database. It is implemented as a wrapper around XDB.
 */
class PlDBTable
{
    public $table;

    private $schema;
    private $keyFields;
    private $mutableFields;

    public function __construct($table)
    {
        $this->table = $table;
        $this->schema();
    }

    private function parseSchema(PlIterator $schema)
    {
        $this->schema = array();
        $this->keyFields = array();
        $this->mutableFields = array();
        while ($column = $schema->next()) {
            $field = new PlDBTableField($column);
            $this->schema[$field->name] = $field;
            if ($field->inPrimaryKey) {
                $this->keyFields[] = $field->name;
            } else {
                $this->mutableFields[] = $field->name;
            }
        }
    }


    private function schema()
    {
        if (!$this->schema) {
            $schema = XDB::iterator('DESCRIBE ' . $this->table);
            $this->parseSchema($schema);
        }
        return $this->schema;
    }

    private function field($field)
    {
        $schema = $this->schema();
        if (!isset($schema[$field])) {
            throw new PlDBNoSuchFieldException($field, $this);
        }
        return $schema[$field];
    }

    public function formatField($field, $value)
    {
        return $this->field($field)->format($value);
    }

    public function defaultValue($field)
    {
        return $this->field($field)->defaultValue;
    }

    public function primaryKey(PlDBTableEntry $entry)
    {
        $key = array();
        foreach ($this->keyFields as $field) {
            if (!isset($entry->$field)) {
                throw new PlDBIncompleteEntryDescription($field, $this);
            } else {
                $key[] = XDB::escape($this->$field);
            }
        }
        return implode('-', $key);
    }

    private function buildKeyCondition(PlDBTableEntry $entry, $allowIncomplete)
    {
        $condition = array();
        foreach ($this->keyFields as $field) {
            if (!isset($entry->$field)) {
                if (!$allowIncomplete) {
                    throw new PlDBIncompleteEntryDescription($field, $this);
                }
            } else {
                $condition[] = XDB::format($field . ' = {?}', $entry->$field);
            }
        }
        return implode(' AND ', $condition);
    }

    public function fetchEntry(PlDBTableEntry $entry)
    {
        $result = XDB::rawFetchOneAssoc('SELECT  *
                                           FROM  ' . $this->table . '
                                          WHERE  ' . $this->buildKeyCondition($entry, false));
        if (!$result) {
            return false;
        }
        return $entry->fillFromDBData($result);
    }

    public function iterateOnEntry(PlDBTableEntry $entry)
    {
        $it = XDB::rawIterator('SELECT  *
                                  FROM  ' . $this->table . '
                                 WHERE  ' . $this->buildKeyCondition($entry, true));
        return PlIteratorUtils::map($it, array($entry, 'cloneAndFillFromDBData'));
    }

    public function updateEntry(PlDBTableEntry $entry)
    {
        $values = array();
        foreach ($this->mutableFields as $field) {
            if ($entry->hasChanged($field)) {
                $values[] = XDB::format($field . ' = {?}', $entry->$field);
            }
        }
        if (count($values) > 0) {
            XDB::rawExecute('UPDATE ' . $this->table . '
                                SET ' . implode(', ', $values) . '
                              WHERE ' . $this->buildKeyCondition($entry, false));
        }
    }

    public static function get($name)
    {
        var_dump('blah');
        return new PlDBTable($name);
    }
}

class PlDBTableEntry extends PlAbstractIterable
{
    private $table;
    private $changed;
    private $fetched = false;
    private $autoFetch;

    private $data = array();

    public function __construct($table, $autoFetch = false)
    {
        if ($table instanceof PlDBTable) {
            $this->table = $table;
        } else {
            $this->table = PlCache::getGlobal('pldbtable_' . $table, array('PlDBTable', 'get'), array($table));
        }
        $this->autoFetch = $autoFetch;
        $this->changed = new PlFlagSet();
    }

    /** This hook is called when the entry is going to be updated in the db.
     *
     * A typical usecase is a class that stores low-level representation of
     * an object in db and perform a conversion between this low-level representation
     * and a higher-level representation.
     *
     * @return true in case of success
     */
    protected function preSave()
    {
        return true;
    }

    /** This hook is called when the entry has just been fetched from the db.
     *
     * This is the counterpart of @ref preSave and a typical use-case is the conversion
     * from a high-level representation of the objet to a representation suitable for
     * storage in the database.
     *
     * @return true in case of success.
     */
    protected function postFetch()
    {
        return true;
    }

    public function __get($field)
    {
        if (isset($this->data[$field])) {
            return $this->data[$field];
        } else if (!$this->fetched && $this->autoFetch) {
            $this->fetch();
            if (isset($this->data[$field])) {
                return $this->data[$field];
            }
        }
        return $this->table->defaultValue($field);
    }

    public function __set($field, $value)
    {
        $this->data[$field] = $this->table->formatField($field, $value);
        $this->changed->addFlag($field);
    }

    public function __isset($field)
    {
        return isset($this->data[$field]);
    }

    public function primaryKey()
    {
        $this->table->primaryKey($this);
    }

    public function hasChanged($field)
    {
        return $this->changed->hasFlag($field);
    }

    public function fillFromArray(array $data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    public function fillFromDBData(array $data)
    {
        $this->fillFromArray($data);
        $this->changed->clear();
        return $this->postFetch();
    }

    public function cloneAndFillFromDBData(array $data)
    {
        $clone = clone $this;
        $clone->fillFromDBData($data);
        return $clone;
    }

    public function fetch()
    {
        return $this->table->fetchEntry($this);
    }

    public function iterate()
    {
        return $this->table->iterateOnEntry($this);
    }

    public function save()
    {
        if (!$this->preSave()) {
            return false;
        }
        $this->table->updateEntry($this);
        $this->changed->clear();
        return true;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

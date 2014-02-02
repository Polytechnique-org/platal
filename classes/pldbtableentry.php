<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class PlDBNoSuchKeyException extends PlException
{
    public function __construct($key, PlDBTable $table)
    {
        parent::__construct('Erreur lors de l\'accès à la base de données',
                            'No such key ' . $key . ' in table ' . $table->table);
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

    public $type;
    public $typeLength;
    public $typeParameters;

    public $allowNull;
    public $defaultValue;
    public $autoIncrement;

    private $validator;
    private $formatter;

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
        $this->inPrimaryKey  = ($column['Key'] == 'PRI');

        try {
            $this->defaultValue = $this->format($column['Default']);
        } catch (PlDBBadValueException $e) {
            $this->defaultValue = null;
        }
    }

    public function registerFormatter($class)
    {
        $this->formatter = $class;
    }

    public function registerValidator($class)
    {
        $this->validator = $class;
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
        }
        if (!is_null($this->validator)) {
            $class = $this->validator;
            new $class($this, $value);
        }
        if (!is_null($this->formatter)) {
            $class = $this->formatter;
            $value = new $class($this, $value);
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
        } else if (ends_with($this->type, 'char')) {
            if (strlen($value) > $this->typeLength) {
                throw new PlDBBadValueException($value, $this, 'value is expected to be at most ' . $this->typeLength . ' characters long, ' . strlen($value) . ' given');
            }
            return $value;
        } else if (starts_with($this->type, 'date') || $this->type == 'timestamp') {
            return new DateFieldFormatter($this, $value);
        }
        return $value;
    }
}

interface PlDBTableFieldValidator
{
    public function __construct(PlDBTableField $field, $value);
}

interface PlDBTableFieldFormatter extends PlDBTableFieldValidator, XDBFormat, PlExportable
{
}

class DateFieldFormatter implements PlDBTableFieldFormatter
{
    private $datetime;
    private $storageFormat;

    public function __construct(PlDBTableField $field, $date)
    {
        $this->datetime = make_datetime($date);
        if (is_null($this->datetime)) {
            throw new PlDBBadValueException($date, $field, 'value is expected to be a date/time, ' . $date . ' given');
        }
        if ($field->type == 'date') {
            $this->storageFormat = 'Y-m-d';
        } else if ($field->type == 'datetime') {
            $this->storageFormat = 'Y-m-d H:i:s';
        } else {
            $this->storageFormat = 'U';
        }
    }

    public function format()
    {
        return XDB::escape($this->export());
    }

    public function date($format)
    {
        return $this->datetime->format($format);
    }

    public function export()
    {
        return $this->datetime->format($this->storageFormat);
    }
}

class JSonFieldFormatter implements PlDBTableFieldFormatter, ArrayAccess
{
    private $data;

    public function __construct(PlDBTableField $field, $data)
    {
        if (strpos($field->type, 'text') === false) {
            throw new PlDBBadValueException($data, $field, 'json formatting requires a text field');
        }

        if (is_string($data)) {
            $this->data = json_decode($data, true);
        } else if (is_object($data)) {
            if ($data instanceof PlExportable) {
                $this->data = $data->export();
            } else {
                $this->data = json_decode(json_encode($data), true);
            }
        } else if (is_array($data)) {
            $this->data = $data;
        }

        if (is_null($this->data)) {
            throw new PlDBBadValueException($data, $field, 'cannot interpret data as json: ' . $data);
        }
    }

    public function format()
    {
        return XDB::escape(json_encode($this->data));
    }

    public function export()
    {
        return $this->data;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}


/** This class aims at providing a simple interface to interact with a single
 * table of a database. It is implemented as a wrapper around XDB.
 */
class PlDBTable
{
    const PRIMARY_KEY = 'PRIMARY';

    public $table;

    private $schema;
    private $primaryKey;
    private $uniqueKeys;
    private $multipleKeys;
    private $mutableFields;

    public function __construct($table)
    {
        $this->table = $table;
        $this->schema();
    }

    private function parseSchema(PlIterator $schema, PlIterator $keys)
    {
        $this->schema = array();
        $this->primaryKey = array();
        $this->uniqueKeys = array();
        $this->multipleKeys = array();
        $this->mutableFields = array();
        while ($column = $schema->next()) {
            $field = new PlDBTableField($column);
            $this->schema[$field->name] = $field;
            if (!$field->inPrimaryKey) {
                $this->mutableFields[] = $field->name;
            }
        }
        while ($column = $keys->next()) {
            $name     = $column['Key_name'];
            $multiple = intval($column['Non_unique']) != 0;
            $field    = $column['Column_name'];
            if ($multiple) {
                if (!isset($this->multipleKeys[$name])) {
                    $this->multipleKeys[$name] = array();
                }
                $this->multipleKeys[$name][] = $field;
            } else if ($name == self::PRIMARY_KEY) {
                $this->primaryKey[] = $field;
            } else {
                if (!isset($this->uniqueKeys[$name])) {
                    $this->uniqueKeys[$name] = array();
                }
                $this->uniqueKeys[$name][] = $field;
            }
        }
    }


    private function schema()
    {
        if (!$this->schema) {
            $schema = XDB::iterator('DESCRIBE ' . $this->table);
            $keys   = XDB::iterator('SHOW INDEX FROM ' . $this->table);
            $this->parseSchema($schema, $keys);
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

    public function registerFieldFormatter($field, $class)
    {
        return $this->field($field)->registerFormatter($class);
    }

    public function registerFieldValidator($field, $class)
    {
        return $this->field($field)->registerValidator($class);
    }


    public function defaultValue($field)
    {
        return $this->field($field)->defaultValue;
    }

    private function hasKeyField(PlDBTableEntry $entry, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($entry->$field)) {
                return true;
            }
        }
        return false;
    }

    private function keyFields($keyName)
    {
        if ($keyName == self::PRIMARY_KEY) {
            return $this->primaryKey;
        } else if (isset($this->uniqueKeys[$keyName])) {
            return $this->uniqueKeys[$keyName];
        } else if (isset($this->multipleKeys[$keyName])) {
            return $this->multipleKeys[$keyName];
        }
        throw new PlDBNoSuchKeyException($keyName, $this);
    }

    private function bestKeyFields(PlDBTableEntry $entry, $allowMultiple)
    {
        if ($this->hasKeyField($entry, $this->primaryKey)) {
            return $this->primaryKey;
        }
        foreach ($this->uniqueKeys as $fields) {
            if ($this->hasKeyField($entry, $fields)) {
                return $fields;
            }
        }
        if ($allowMultiple) {
            foreach ($this->multipleKeys as $fields) {
                if ($this->hasKeyField($entry, $fields)) {
                    return $fields;
                }
            }
        }
        return $this->primaryKey;
    }

    public function key(PlDBTableEntry $entry, array $keyFields)
    {
        $key = array();
        foreach ($keyFields as $field) {
            if (!isset($entry->$field)) {
                throw new PlDBIncompleteEntryDescription($field, $this);
            } else {
                $key[] = XDB::escape($this->$field);
            }
        }
        return implode('-', $key);
    }

    public function primaryKey(PlDBTableEntry $entry)
    {
        return $this->key($this->keyFields(self::PRIMARY_KEY));
    }

    private function buildKeyCondition(PlDBTableEntry $entry, array $keyFields, $allowIncomplete)
    {
        $condition = array();
        foreach ($keyFields as $field) {
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
                                          WHERE  ' . $this->buildKeyCondition($entry,
                                                                              $this->bestKeyFields($entry, false),
                                                                              false));
        if (!$result) {
            return false;
        }
        return $entry->fillFromDBData($result);
    }

    public function iterateOnCondition(PlDBTableEntry $entry, $condition, $sortField)
    {
        if (empty($sortField)) {
            $sortField = $this->primaryKey;
        }
        if (!is_array($sortField)) {
            $sortField = array($sortField);
        }
        $sort = ' ORDER BY ' . implode(', ', $sortField);
        $it = XDB::rawIterator('SELECT  *
                                  FROM  ' . $this->table . '
                                 WHERE  ' . $condition . '
                                        ' . $sort);
        return PlIteratorUtils::map($it, array($entry, 'cloneAndFillFromDBData'));
    }

    public function iterateOnEntry(PlDBTableEntry $entry, $sortField)
    {
        return $this->iterateOnCondition($entry,
                                         $this->buildKeyCondition($entry,
                                                                  $this->bestKeyFields($entry, true),
                                                                  true),
                                         $sortField);
    }

    const SAVE_INSERT_MISSING   = 0x01;
    const SAVE_UPDATE_EXISTING  = 0x02;
    const SAVE_IGNORE_DUPLICATE = 0x04;
    public function saveEntries(array $entries, $flags)
    {
        $flags &= (self::SAVE_INSERT_MISSING | self::SAVE_UPDATE_EXISTING | self::SAVE_IGNORE_DUPLICATE);
        Platal::assert($flags != 0, "Hey, the flags ($flags) here are so stupid, don't know what to do");
        if ($flags == self::SAVE_UPDATE_EXISTING) {
            foreach ($entries as $entry) {
                $values = array();
                foreach ($this->mutableFields as $field) {
                    if ($entry->hasChanged($field)) {
                        $values[] = XDB::format($field . ' = {?}', $entry->$field);
                    }
                }
                if (count($values) > 0) {
                    XDB::rawExecute('UPDATE ' . $this->table . '
                                        SET ' . implode(', ', $values) . '
                                      WHERE ' . $this->buildKeyCondition($entry,
                                                                         $this->keyFields(self::PRIMARY_KEY),
                                                                         false));
                }
            }
        } else {
            $fields = new PlFlagSet();
            foreach ($entries as $entry) {
                foreach ($this->schema as $field=>$type) {
                    if ($type->inPrimaryKey || $entry->hasChanged($field)) {
                        $fields->addFlag($field);
                    }
                }
            }
            if (count($fields->export()) > 0) {
                foreach ($entries as $entry) {
                    $v = array();
                    foreach ($fields as $field) {
                        $v[$field] = XDB::escape($entry->$field);
                    }
                    $values[] = '(' . implode(', ', $v) . ')';
                }

                $query = $this->table . ' (' . implode(', ', $fields->export()) . ')
                               VALUES ' . implode(",\n", $values);
                if (($flags & self::SAVE_UPDATE_EXISTING)) {
                    $update = array();
                    foreach ($this->mutableFields as $field) {
                        if (isset($values[$field])) {
                            $update[] = "$field = VALUES($field)";
                        }
                    }
                    if (count($update) > 0) {
                        $query = 'INSERT INTO ' . $query;
                        $query .= "\n  ON DUPLICATE KEY UPDATE " . implode(', ', $update);
                    } else {
                        $query = 'INSERT IGNORE INTO ' . $query;
                    }
                } else if (($flags & self::SAVE_IGNORE_DUPLICATE)) {
                    $query = 'INSERT IGNORE INTO ' . $query;
                } else {
                    $query = 'INSERT INTO ' . $query;
                }
                XDB::rawExecute($query);
                if (count($entries) == 1) {
                    $id = XDB::insertId();
                    if ($id) {
                        $entry = end($entries);
                        foreach ($this->primaryKey as $field) {
                            if ($this->schema[$field]->autoIncrement) {
                                $entry->$field = $id;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    public function deleteEntry(PlDBTableEntry $entry, $allowIncomplete)
    {
        XDB::rawExecute('DELETE FROM ' . $this->table . '
                               WHERE ' . $this->buildKeyCondition($entry,
                                                                  $this->bestKeyFields($entry, $allowIncomplete),
                                                                  $allowIncomplete));
    }

    public function exportEntry(PlDBTableEntry $entry)
    {
        $export = array();
        foreach ($this->schema as $key=>$field) {
            $value = $entry->$key;
            if ($value instanceof PlExportable) {
                $value = $value->export();
            }
            $export[$key] = $value;
        }
        return $export;
    }

    public static function get($name)
    {
        return new PlDBTable($name);
    }
}

class PlDBTableEntry extends PlAbstractIterable implements PlExportable
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

    /** Register a custom formatter for a field.
     *
     * A formatter can be used to perform on-the-fly conversion from db storage to a user-friendly format.
     * For example, if you have a textual field that contain json, you can use a JSonFieldFormatter on this
     * field to perform automatic decoding when reading from the database (or when assigning the field)
     * and automatic json_encoding when storing the object back to the db.
     */
    protected function registerFieldFormatter($field, $formatterClass)
    {
        $this->table->registerFieldFormatter($field, $formatterClass);
    }

    /** Register a custom validator for a field.
     *
     * A validator perform a pre-filter on the value of a field. As opposed to the formatters, it does
     * not affects how the value is stored in the database.
     */
    protected function registerFieldValidator($field, $validatorClass)
    {
        $this->table->registerFieldValidator($field, $validatorClass);
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

    /** This hook is called when the entry has been save in the database.
     *
     * It can be used to perform post-actions on save like storing extra data
     * in database or sending a notification.
     */
    protected function postSave()
    {
    }

    /** This hook is called when the entry is going to be deleted from the db.
     *
     * Default behavior is to call preSave().
     *
     * @return true in case of success.
     */
    protected function preDelete()
    {
        return $this->preSave();
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

    public function copy(PlDBTableEntry $other)
    {
        Platal::assert($this->table == $other->table,
                       "Trying to fill an entry of table {$this->table->table} with content of {$other->table->table}.");
        $this->changed = $other->changed;
        $this->fetched = $other->fetched;
        $this->data    = $other->data;
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

    public function iterate($sortField = null)
    {
        return $this->table->iterateOnEntry($this, $sortField);
    }

    public function iterateOnCondition($condition, $sortField = null)
    {
        return $this->table->iterateOnCondition($this, $condition, $sortField);
    }

    public function save($flags)
    {
        return self::saveBatch(array($this), $flags);
    }

    public function update($insertMissing = false)
    {
        $flags = PlDBTable::SAVE_UPDATE_EXISTING;
        if ($insertMissing) {
            $flags = PlDBTable::SAVE_INSERT_MISSING;
        }
        return $this->save($flags);
    }

    public function insert($allowUpdate = false)
    {
        $flags = PlDBTable::SAVE_INSERT_MISSING;
        if ($allowUpdate) {
            $flags |= PlDBTable::SAVE_UPDATE_EXISTING;
        }
        return $this->save($flags);
    }

    public function delete()
    {
        if (!$this->preDelete()) {
            return 0;
        }
        return $this->table->deleteEntry($this, true);
    }

    public function export()
    {
        return $this->table->exportEntry($this);
    }

    protected static function saveBatch($entries, $flags)
    {
        $table = null;
        foreach ($entries as $entry) {
            if (is_null($table)) {
                $table = $entry->table;
            } else {
                Platal::assert($table === $entry->table, "Cannot save batch of entries of different kinds");
            }
            if (!$entry->preSave()) {
                return false;
            }
        }
        $table->saveEntries($entries, $flags);
        foreach ($entries as $entry) {
            $entry->changed->clear();
            $entry->postSave();
        }
        return true;
    }

    public static function insertBatch($entries, $allowUpdate = false)
    {
        $flags = PlDBTable::SAVE_INSERT_MISSING;
        if ($allowUpdate) {
            $flags |= PlDBTable::SAVE_UPDATE_EXISTING;
        }
        return self::saveBatch($entries, $flags);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

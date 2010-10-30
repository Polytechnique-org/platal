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

class Survey extends PlDBTableEntry implements SurveyQuestionContainer
{
    private $fetchQuestions = true;
    public $questions = array();

    public function __construct()
    {
        parent::__construct('surveys');
        $this->registerFieldValidator('shortname', 'ShortNameFieldValidator');
        $this->registerFieldFormatter('voters', 'JSonFieldFormatter');
        $this->registerFieldFormatter('viewers', 'JSonFieldFormatter');
    }

    protected function postFetch()
    {
        if (!$this->fetchQuestions) {
            return true;
        }
        $selector = new SurveyQuestion($this);
        $selector->sid = $this->id;

        $stack = array();
        foreach ($selector as $question) {
            $question = $question->typedInstance();
            if (is_null($question->parent)) {
                $this->addQuestion($question);
            } else {
                $pos = count($stack) - 1;
                while ($stack[$pos]->qid != $question->parent) {
                    --$pos;
                    array_pop($stack);
                }
                Platal::assert(count($stack) > 0, "Invalid question structure");
                Platal::assert($stack[$pos] instanceof SurveyQuestionContainer, "Invalid question type");
                $stack[$pos]->addQuestion($question);
            }
            array_push($stack, $question);
        }
        return true;
    }

    protected function postSave()
    {
        $questions = array();
        $selector = new SurveyQuestion($this);
        $selector->sid = $this->id;
        $selector->delete();

        $this->reassignQuestionIds();
        foreach ($this->questions as $question) {
            $question->sid = $this->id;
            $question->insert();
        }
    }

    public function addQuestion(SurveyQuestion $question, $pos = null)
    {
        $question->parent = null;
        if (is_null($pos)) {
            $this->questions[] = $question;
        } else {
            array_splice($this->questions, $pos, 0, $question);
        }
    }

    public function newQuestion($type, $pos = null)
    {
        $question = SurveyQuestion::instanceForType($this, $type);
        $this->addQuestion($question, $pos);
        return $question;
    }

    public function reassignQuestionIds()
    {
        $id = 0;
        foreach ($this->questions as $question) {
            $question->qid = $id;
            if ($question instanceof SurveyQuestionContainer) {
                $id = $question->reassignQuestionIds();
            } else {
                $id++;
            }
        }
        return $id;
    }

    public function export()
    {
        $export = parent::export();
        $export['questions'] = array();
        foreach ($this->questions as $question) {
            $export['questions'][] = $question->export();
        }
        return $export;
    }

    public function canSee(User $user)
    {
        if ($this->uid == $user->id() || $user->hasFlag('admin')) {
            return true;
        }
        return false;
    }

    public static function get($name, $fetchQuestions = true)
    {
        if (is_array($name)) {
            $name = $name[0];
        }
        $survey = new Survey();
        $survey->fetchQuestions = $fetchQuestions;
        if (can_convert_to_integer($name)) {
            $survey->id = $name;
        } else {
            $survey->shortname = $name;
        }
        if (!$survey->fetch()) {
            return null;
        }
        return $survey;
    }

    public static function iterActive()
    {
        $survey = new Survey();
        $survey->fetchQuestions = false;
        return $survey->iterateOnCondition('begin <= CURDATE() AND end >= CURDATE()
                                            AND FIND_IN_SET(\'validated\', flags)');
    }
}

class ShortNameFieldValidator implements PlDBTableFieldValidator
{
    public function __construct(PlDBTableField $field, $value)
    {
        if (can_convert_to_integer($value) || !preg_match('/^[a-z0-9]+[-_\.a-z0-9]*[a-z0-9]+$/i', $value)) {
            throw new PlDBBadValueException($value, $field,
                                            'The shortname can only contain alphanumerical caracters, dashes, underscores and dots');
        }
    }
}

interface SurveyQuestionContainer
{
    public function addQuestion(SurveyQuestion $question, $pos = null);
    public function newQuestion($type, $pos = null);
    public function reassignQuestionIds();
}

class SurveyQuestion extends PlDBTableEntry
{
    protected $survey;
    protected $parentQuestion;

    public function __construct(Survey $survey)
    {
        parent::__construct('survey_questions');
        $this->registerFieldFormatter('parameters', 'JSonFieldFormatter');
        $this->survey = $survey;
    }

    public function typedInstance()
    {
        $instance = self::instanceForType($this->survey, $this->type);
        $instance->copy($this);
        return $instance;
    }

    public static function instanceForType(Survey $survey, $type)
    {
        require_once dirname(__FILE__) . '/' . $type . '.inc.php';
        $class = 'SurveyQuestion' . $type;
        return new $class($survey);
    }
}

class SurveyQuestionGroup extends SurveyQuestion implements SurveyQuestionContainer
{
    public $children = array();

    public function __construct(Survey $survey)
    {
        parent::__construct($survey);
    }

    public function addQuestion(SurveyQuestion $question, $pos = null)
    {
        $question->parentQuestion = $this;
        if (is_null($pos)) {
            $this->children[] = $question;
        } else {
            array_splice($this->children, $pos, 0, $question);
        }
    }

    public function newQuestion($type, $pos = null)
    {
        $question = SurveyQuestion::instanceForType($this->survey, $type);
        $this->addQuestion($question, $pos);
        return $question;
    }

    public function reassignQuestionIds()
    {
        $id = $this->qid + 1;
        foreach ($this->children as $question) {
            $question->qid = $id;
            if ($question instanceof SurveyQuestionContainer) {
                $id = $question->reassignQuestionIds();
            } else {
                $id++;
            }
        }
        return $id;
    }

    protected function postSave()
    {
        foreach ($this->children as $question) {
            $question->sid = $this->sid;
            $question->parent = $this->qid;
            $question->insert();
        }
    }

    public function export()
    {
        $export = parent::export();
        $export['children'] = array();
        foreach ($this->children as $child) {
            $export['children'][] = $child->export();
        }
        return $export;
    }
}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker enc=utf-8:
?>

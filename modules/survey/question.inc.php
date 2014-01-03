<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

    public function voteTemplate()
    {
        return 'survey/question.' . $this->type . '.tpl';
    }

    public function editTemplate()
    {
        return 'survey/edit.' . $this->type . '.tpl';
    }

    protected function buildAnswer(SurveyAnswer $answer, PlDict $answers)
    {
        Platal::assert(false, "This should not happen");
    }

    public function vote(SurveyVote $vote, PlDict $answers)
    {
        if ($this->flags->hasFlag('noanswer')) {
            if ($answers->has($this->qid)) {
                throw new Exception("Des réponses ont été données à une question n'en attendant pas");
            }
            return null;
        }
        $answer = $vote->getAnswer($this);
        if (is_null($answer)) {
            return null;
        }
        if (!$this->buildAnswer($answer, $answers)) {
            return $answer;
        }
        if ($this->flags->hasFlag('mandatory') && is_null($answer->answer)) {
            $answer->inError = 'Tu dois répondre à cette question';
        }
        return $answer;
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

    public function vote(SurveyVote $vote, PlDict $answers)
    {
        $a = parent::vote($vote, $answers);
        foreach ($this->children as $child) {
            $child->vote($vote, $answers);
        }
        return $a;
    }

    public function child($qid)
    {
        $prev = null;
        foreach ($this->children as $question) {
            if ($qid == $question->qid) {
                return $question;
            } else if ($qid < $question->qid) {
                Platal::assert($prev instanceof SurveyQuestionGroup);
                return $prev->child($qid);
            }
            $prev = $question;
        }
        Platal::assert($prev instanceof SurveyQuestionGroup);
        return $prev->child($qid);
    }
}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker enc=utf-8:
?>

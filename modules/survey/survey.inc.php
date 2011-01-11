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

require_once dirname(__FILE__) . '/question.inc.php';
require_once dirname(__FILE__) . '/answer.inc.php';

class Survey extends PlDBTableEntry implements SurveyQuestionContainer
{
    private $fetchQuestions = true;
    public $questions = array();
    public $viewerFilter = null;
    public $voterFilter = null;

    public function __construct()
    {
        parent::__construct('surveys');
        $this->registerFieldValidator('shortname', 'ShortNameFieldValidator');
        $this->registerFieldFormatter('voters', 'JSonFieldFormatter');
        $this->registerFieldFormatter('viewers', 'JSonFieldFormatter');
    }

    protected function postFetch()
    {
        if (!is_null($this->voters)) {
            $this->voterFilter = UserFilter::fromExportedConditions($this->voters);
        } else {
            $this->voterFilter = null;
        }
        if (!is_null($this->viewers)) {
            $this->viewerFilter = UserFilter::fromExportedConditions($this->viewers);
        } else {
            $this->viewerFilter = null;
        }
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

    protected function preSave()
    {
        if (!is_null($this->voterFilter)) {
            $this->voters = $this->voterFilter->exportConditions();
        } else {
            $this->voters = null;
        }
        if (!is_null($this->viewerFilter)) {
            $this->viewers = $this->viewerFilter->exportConditions();
        } else {
            $this->viewers = null;
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

    public function clearQuestions()
    {
        $this->fetchQuestions = true;
        $this->questions = array();
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

    public function questionForId($qid)
    {
        $prev = null;
        foreach ($this->questions as $question) {
            if ($qid == $question->qid) {
                return $question;
            } else if ($qid < $question->qid) {
                Platal::assert($prev instanceof SurveyQuestionGroup,
                               "Id gap must be caused by question groups");
                return $prev->child($qid);
            }
            $prev = $question;
        }
        Platal::assert($prev instanceof SurveyQuestionGroup,
                       "Id gap must be caused by question groups");
        return $prev->child($qid);
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
        $export['questions'] = $this->exportQuestions();
        return $export;
    }

    public function exportQuestions()
    {
        $export = array();
        foreach ($this->questions as $question) {
            $export[] = $question->export();
        }
        return $export;
    }

    public function exportQuestionsToJSON()
    {
        return json_encode($this->exportQuestions());
    }

    /* Return an indicator of the progression of the survey:
     *  negative values means 'the survey is not started'
     *  0 means 'the survey is in progress'
     *  positive values means 'the survey expired'
     */
    public function progression()
    {
        if (!$this->flags->hasFlag('validated')) {
            return -2;
        }
        $now = time();
        if ($this->begin->format('U') > $now) {
            return -1;
        }
        if ($this->end->format('U') <= $now) {
            return 1;
        }
        return 0;
    }

    public function canSee(User $user)
    {
        if ($this->canSeeResults($user) || $this->canVote($user)) {
            return true;
        }
        return false;
    }

    public function canSeeResults(User $user)
    {
        if ($user->id() == $this->uid || $user->hasFlag('admin')) {
            return true;
        }
        if (is_null($this->viewerFilter)) {
            return $this->viewerFilter->checkUser($user);
        }
        return false;
    }

    public function canVote(User $user)
    {
        $status = $this->progression();
        if ($status < 0) {
            return "Ce sondage n'est pas encore commencé";
        } else if ($status > 0) {
            return "Ce sondage est terminé";
        }
        if (!is_null($this->voterFilter) && !$this->voterFilter->checkUser($user)) {
            return "Ce sondage ne s'adresse pas à toi";
        }
        $vote = SurveyVote::getVote($this, $user, false);
        if (is_null($vote)) {
            return "Tu as déjà voté à ce sondage.";
        }
        return true;
    }

    public function vote(User $user, array $answers)
    {
        if (!$this->canVote($user)) {
            return array('survey' => "Tu n'es pas autorisé à voter à ce sondage.");
        }
        $vote = SurveyVote::getVote($this, $user);
        if (is_null($vote)) {
            return $vote;
        }
        $answers = new PlDict($answers);
        foreach ($this->questions as $question) {
            $question->vote($vote, $answers);
        }
        return $vote;
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

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker enc=utf-8:
?>

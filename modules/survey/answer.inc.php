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

class SurveyVote extends PlDBTableEntry
{
    protected $survey;
    protected $user;

    private $answers = array();
    private $fetchAnswers;

    public function __construct(Survey $survey, User $user)
    {
        parent::__construct('survey_votes');
        $this->survey = $survey;
        $this->user = $user;
        $this->sid = $survey->id;
    }

    protected function postSave()
    {
        Platal::assert(!is_null($this->vid), "Cannot process a vote without its identifier");
        XDB::execute("REPLACE INTO  survey_voters (sid, uid, vid)
                            VALUES  ({?}, {?}, {?})",
                     $this->survey->id, $this->user->id(),
                     $this->survey->flags->hasFlag('anonymous') ? null : $this->vid);

        /* Save answers */
        $selector = new SurveyAnswer($this);
        $selector->delete();

        $answers = array();
        foreach ($this->answers as $key=>$answer) {
            if (!is_null($answer)) {
                $answer->vid = $this->vid;
                $answers[] = $answer;
            }
        }
        PlDBTableEntry::insertBatch($answers);
        return true;
    }

    protected function postFetch()
    {
        $selector = new SurveyAnswer($this);
        foreach ($selector as $answer) {
            $question = $this->survey->questionForId($answer->qid);
            $this->answers[$answer->qid] = $answer;
        }
        return true;
    }

    public function inError()
    {
        foreach ($this->answers as $answer) {
            if ($answer->inError !== false) {
                return true;
            }
        }
        return false;
    }

    public function getAnswer(SurveyQuestion $question)
    {
        if (!isset($this->answers[$question->qid])) {
            $val = new SurveyAnswer($this);
            $val->qid = $question->qid;
            $this->answers[$question->qid] = $val;
        }
        return $this->answers[$question->qid];
    }

    public function export()
    {
        $export = array();
        foreach ($this->answers as $qid=>$answer) {
            $export[$qid] = $answer->export();
        }
        return $export;
    }

    public static function getVote(Survey $survey, User $user, $fetchAnswers = true)
    {
        $vid = XDB::query('SELECT  vid
                             FROM  survey_voters
                            WHERE  sid = {?} AND uid = {?}',
                          $survey->id, $user->id());
        if ($vid->numRows() == 0) {
            $vote = new SurveyVote($survey, $user);
            $vote->fetchAnswers = $fetchAnswers;
            return $vote;
        }
        $vid = $vid->fetchOneCell();
        if (is_null($vid)) {
            /* User already vote, but survey is anonymous and the vote
             * cannot be changed
             */
            return null;
        }
        $vote = new SurveyVote($survey, $user);
        $vote->vid = $vid;
        $vote->fetchAnswers = $fetchAnswers;
        $vote->fetch();
        return $vote;
    }
}

class SurveyAnswer extends PlDBTableEntry
{
    public $inError = false;
    public $vote;

    public function __construct(SurveyVote $vote)
    {
        parent::__construct('survey_vote_answers');
        $this->registerFieldFormatter('answer', 'JSonFieldFormatter');
        $this->vote = $vote;
        if (!is_null($vote->vid)) {
            $this->vid = $vote->vid;
        }
    }

    protected function preSave()
    {
        Platal::assert(!$this->inError, "Cannot save an invalid answer");
        $this->sid = $this->vote->sid;
        $this->vid = $this->vote->vid;
        return true;
    }

    public function export()
    {
        $export = array();
        if (!is_null($this->answer)) {
            $export['value'] = $this->answer->export();
        }
        if ($this->inError !== false) {
            $export['error'] = $this->inError;
        }
        return $export;
    }
}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker enc=utf-8:
?>

<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

// {{{ class Survey : root of any survey, contains all questions
class Survey
{
    // {{{ static properties and functions, regarding survey modes and question types
    const MODE_ALL    = 0;
    const MODE_XANON  = 1;
    const MODE_XIDENT = 2;
    private static $longModes = array(self::MODE_ALL    => "sondage ouvert à tout le monde, anonyme",
                                      self::MODE_XANON  => "sondage restreint aux polytechniciens, anonyme",
                                      self::MODE_XIDENT => "sondage restreint aux polytechniciens, non anonyme");
    private static $shortModes = array(self::MODE_ALL    => "tout le monde, anonyme",
                                       self::MODE_XANON  => "polytechniciens, anonyme",
                                       self::MODE_XIDENT => "polytechniciens, non anonyme");

    public static function getModes($long = true) {
        return ($long)? self::$longModes : self::$shortModes;
    }

    private static $types = array('text'          => 'Texte court',
                                  'textarea'      => 'Texte long',
                                  'num'           => 'Numérique',
                                  'radio'         => 'Choix multiples (une réponse)',
                                  'checkbox'      => 'Choix multiples (plusieurs réponses)',
                                  'radiotable'    => 'Questions multiples à choix multiples (une réponse)',
                                  'checkboxtable' => 'Questions multiples à choix mutliples (plusieurs réponses)');

    public static function getTypes()
    {
        return self::$types;
    }

    public static function isType($t)
    {
        return array_key_exists($t, self::$types);
    }
    // }}}

    // {{{ properties, constructor and basic methods
    private $id;
    private $title;
    private $description;
    private $end;
    private $mode;
    private $promos;
    private $valid;
    private $questions;
    private $creator;

    public function __construct($args, $id = -1, $valid = false, $questions = null)
    {
        $this->update($args);
        $this->id = $id;
        $this->valid = $valid;
        $this->questions = ($questions == null)? array() : $questions;
    }

    public function update($args)
    {
        $this->title       = $args['title'];
        $this->description = $args['description'];
        $this->end         = $args['end'];
        $this->mode        = (isset($args['mode']))? $args['mode'] : self::MODE_ALL;
        $this->creator     = $args['uid'];
        if ($this->mode == self::MODE_ALL) {
            $args['promos'] = '';
        }
        $args['promos'] = str_replace(' ', '', $args['promos']);
        $this->promos  = ($args['promos'] == '' || preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $args['promos']))? $args['promos'] : '#';
    }

    public function canSeeEarlyResults(User $user)
    {
        return $user->id() == $this->creator || $user->checkPerms('admin');
    }
    // }}}

    // {{{ functions to access general information
    public function isMode($mode)
    {
        return ($this->mode == $mode);
    }

    public function checkPromo($promo)
    {
        if ($this->promos == '') {
            return true;
        }
        $promos = explode(',', $this->promos);
        foreach ($promos as $p) {
            if ((preg_match('#^\d{4}$#', $p) && $p == $promo) ||
                (preg_match('#^\d{4}-$#', $p) && intval(substr($p, 0, 4)) <= $promo) ||
                (preg_match('#^-\d{4}$#', $p) && intval(substr($p, 1)) >= $promo) ||
                (preg_match('#^\d{4}-\d{4}$#', $p) &&
                    (intval(substr($p, 0, 4)) <= $promo && intval(substr($p, 5)) >= $promo ||
                     intval(substr($p, 0, 4)) >= $promo && intval(substr($p, 5)) <= $promo ))) {
                    return true;
            }
        }
        return false;
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function isEnded()
    {
        return (strtotime($this->end) - time() <= 0);
    }

    public function getTitle()
    {
        return $this->title;
    }
    // }}}

    // {{{ function toArray() : converts a question (or the whole survey) to array, with results if the survey is ended
    public function toArray($i = 'all')
    {
        if ($i != 'all' && $i != 'root') { // if a specific question is requested, then just returns this question converted to array
            $i = intval($i);
            if (array_key_exists($i, $this->questions)) {
                return $this->questions[$i]->toArray();
            } else {
                return null;
            }
        } else { // else returns the root converted to array in any case
            $a = array('title'       => $this->title,
                       'description' => $this->description,
                       'end'         => $this->end,
                       'mode'        => $this->mode,
                       'promos'      => $this->promos,
                       'valid'       => $this->valid,
                       'type'        => 'root');
            if ($this->id != -1) {
                $a['id'] = $this->id;
            }
            if ($this->isEnded()) { // if the survey is ended, then adds here the number of votes
                $sql = 'SELECT COUNT(id)
                          FROM survey_votes
                         WHERE survey_id={?};';
                $tot = XDB::query($sql, $this->id);
                $a['votes'] = $tot->fetchOneCell();
            }
            if ($i == 'all' && count($this->questions) > 0) { // if the whole survey is requested, then returns all the questions converted to array
                $qArr = array();
                for ($k = 0; $k < count($this->questions); $k++) {
                    $q = $this->questions[$k]->toArray();
                    $q['id'] = $k;
                    if ($this->isEnded()) { // if the survey is ended, then adds here the results of this question
                        $q['result'] = $this->questions[$k]->getResultArray($this->id, $k);
                    }
                    $qArr[$k] = $q;
                }
                $a['questions'] = $qArr;
            }
            return $a;
        }
    }
    // }}}

    // {{{ function toCSV() : builds a CSV file containing all the results of the survey
    public function toCSV($sep = ',', $enc = '"', $asep='|')
    {
        $nbq = count($this->questions);
        //require_once dirname(__FILE__) . '/../../classes/varstream.php';
        VarStream::init();
        global $csv_output;
        $csv_output = '';
        $csv = fopen('var://csv_output', 'w');
        $line = ($this->isMode(self::MODE_XIDENT))? array('id', 'Nom', 'Prenom', 'Promo') : array('id');
        $qids = array();
        for ($qid = 0; $qid < $nbq; $qid++) {
            $qids[$qid] = count($line); // stores the first id of a question (in case of questions with subquestions)
            array_splice($line, count($line), 0, $this->questions[$qid]->getCSVColumns()); // the first line contains the questions
        }
        $nbf = count($line);
        $users = array();
        if ($this->isMode(self::MODE_XIDENT)) { // if the mode is non anonymous
            $users = XDB::fetchAllAssoc('vid', 'SELECT  v.id AS vid, v.uid
                                                  FROM  survey_votes AS v
                                                 WHERE  v.survey_id = {?}
                                            ORDER BY  vid ASC',
                                                $this->id);
        }
        $sql = 'SELECT v.id AS vid, a.question_id AS qid, a.answer AS answer
                  FROM survey_votes AS v
            INNER JOIN survey_answers AS a ON a.vote_id=v.id
                 WHERE v.survey_id={?}
              ORDER BY vid ASC, qid ASC, answer ASC';
        $res = XDB::iterator($sql, $this->id); // retrieves all answers from database
        $vid = -1;
        $vid_ = 0;
        while (($cur = $res->next()) != null) {
            if ($vid != $cur['vid']) { // if the vote id changes, then starts a new line
                fputcsv($csv, $line, $sep, $enc); // stores the former line into $csv_output
                $vid = $cur['vid'];
                $line = array_fill(0, $nbf, ''); // creates an array full of empty string
                $line[0] = $vid_; // the first field is a 'clean' vote id (not the one stored in database)
                if ($this->isMode(self::MODE_XIDENT)) { // if the mode is non anonymous
                    if (array_key_exists($vid, $users)) { // and if the user data can be found
                        $user=PlUser::getWithUID($users[$vid]);
                        $line[1] = $user->lastName(); // adds the user data (in the first fields of the line)
                        $line[2] = $user->firstName();
                        $line[3] = $user->promo();
                    }
                }
                $vid_++;
            }
            $ans = $this->questions[$cur['qid']]->formatAnswer($cur['answer']); // formats the current answer
            if (!is_null($ans)) {
                if (is_array($ans)) {
                    $fid = $qids[$cur['qid']] + $ans['id']; // computes the field id
                    $a = $ans['answer'];
                } else {
                    $fid = $qids[$cur['qid']];
                    $a = $ans;
                }
                if ($line[$fid] != '') {  // if this field already contains something
                    $line[$fid] .= $asep; // then adds a separator before adding the new answer
                }
                $line[$fid] .= $a; // adds the current answer to the correct field
            }
        }
        fputcsv($csv, $line, $sep, $enc); // stores the last line into $csv_output
        return $csv_output;
    }
    // }}}

    // {{{ function factory($type, $args) : builds a question according to the given type
    public function factory($t, $args)
    {
        switch ($t) {
        case 'text':
            return new SurveyText($args);
        case 'textarea':
            return new SurveyTextarea($args);
        case 'num':
            return new SurveyNum($args);
        case 'radio':
            return new SurveyRadio($args);
        case 'checkbox':
            return new SurveyCheckbox($args);
        case 'radiotable':
            return new SurveyRadioTable($args);
        case 'checkboxtable':
            return new SurveyCheckboxTable($args);
        default:
            return null;
        }
    }
    // }}}

    // {{{ questions manipulation functions
    public function addQuestion($i, $c)
    {
        $i = intval($i);
        if ($this->valid || $i > count($this->questions)) {
            return false;
        } else {
            array_splice($this->questions, $i, 0, array($c));
            return true;
        }
    }

    public function delQuestion($i)
    {
        $i = intval($i);
        if ($this->valid || !array_key_exists($i, $this->questions)) {
            return false;
        } else {
            array_splice($this->questions, $i, 1);
            return true;
        }
    }

    public function editQuestion($i, $a)
    {
        if ($i == 'root') {
            $this->update($a);
        } else {
            $i = intval($i);
            if ($this->valid ||!array_key_exists($i, $this->questions)) {
                return false;
            } else {
                $this->questions[$i]->update($a);
            }
        }
        return true;
    }
    // }}}

    // {{{ function checkSyntax() : checks syntax of the questions (currently the root only) before storing the survey in database
    private static $errorMessages = array(
        "datepassed"  => "la date de fin de sondage est déjà dépassée : vous devez préciser une date future",
        "promoformat" => "les restrictions à certaines promotions sont mal formatées"
    );

    public function checkSyntax()
    {
        $rArr = array();
        // checks that the end date given is not already passed
        // (unless the survey has already been validated : an admin can have a validated survey expired)
        if (!$this->valid && $this->isEnded()) {
            $rArr[] = array('question' => 'root', 'error' => self::$errorMessages["datepassed"]);
        }
        if ($this->promos != '' && !preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $this->promos)) {
            $rArr[] = array('question' => 'root', 'error' => self::$errorMessages["promoformat"]);
        }
        return (empty($rArr))? null : $rArr;
    }
    // }}}

    // {{{ functions that manipulate surveys in database
    // {{{ static function retrieveList() : gets the list of available survey (current, old and not validated surveys)
    public static function retrieveList($type, $tpl = true)
    {
        switch ($type) {
        case 'c':
        case 'current':
            $where = 'end > NOW()';
            break;
        case 'o':
        case 'old':
            $where = 'end <= NOW()';
            break;
        default:
            return null;
        }
        if (!S::user()->checkPerms(PERMS_USER)) {
            $where .=  XDB::format(' AND mode = {?}', self::MODE_ALL);
        }
        $sql = 'SELECT id, title, uid, end, mode
                  FROM surveys
                 WHERE '.$where.'
              ORDER BY end DESC;';
        if ($tpl) {
            return XDB::iterator($sql);
        } else {
            return XDB::iterRow($sql);
        }
    }
    // }}}

    // {{{ static function retrieveSurvey() : gets a survey in database (and unserialize the survey object structure)
    public static function retrieveSurvey($sid)
    {
        $sql = 'SELECT questions, title, description, end, mode, promos, uid
                  FROM surveys
                 WHERE id={?}';
        $res = XDB::query($sql, $sid);
        $data = $res->fetchOneAssoc();
        if (is_null($data) || !is_array($data)) {
            return null;
        }
        $survey = new Survey($data, $sid, true, unserialize($data['questions']));
        return $survey;
    }
    // }}}

    // {{{ static function retrieveSurveyInfo() : gets information about a survey (title, description, end date, restrictions) but does not unserialize the survey object structure
    public static function retrieveSurveyInfo($sid)
    {
        $sql = 'SELECT title, description, end, mode, promos
                  FROM surveys
                 WHERE id={?}';
        $res = XDB::query($sql, $sid);
        return $res->fetchOneAssoc();
    }
    // }}}

    // {{{ static function retrieveSurveyReq() : gets a survey request to validate
    public static function retrieveSurveyReq($id)
    {
        $surveyreq = Validate::get_request_by_id($id);
        if ($surveyreq == null) {
            return null;
        }
        $data = array('title'       => $surveyreq->title,
                      'description' => $surveyreq->description,
                      'end'         => $surveyreq->end,
                      'mode'        => $surveyreq->mode,
                      'promos'      => $surveyreq->promos);
        $survey = new Survey($data, $id, false, $surveyreq->questions);
        return $survey;
    }
    // }}}

    // {{{ function proposeSurvey() : stores a proposition of survey in database (before validation)
    public function proposeSurvey()
    {
        $surveyreq = new SurveyReq($this->title, $this->description, $this->end, $this->mode, $this->promos, $this->questions, S::user());
        return $surveyreq->submit();
    }
    // }}}

    // {{{ function updateSurvey() : updates a survey in database (before validation)
    public function updateSurvey()
    {
        if ($this->valid) {
            $sql = 'UPDATE surveys
                       SET questions={?},
                           title={?},
                           description={?},
                           end={?},
                           mode={?},
                           promos={?}
                     WHERE id={?};';
            return XDB::execute($sql, serialize($this->questions), $this->title, $this->description, $this->end, $this->mode, $this->promos, $this->id);
        } else {
            $surveyreq = Validate::get_request_by_id($this->id);
            if ($surveyreq == null) {
                return false;
            }
            return $surveyreq->updateReq($this->title, $this->description, $this->end, $this->mode, $this->promos, $this->questions);
        }
    }
    // }}}

    // {{{ functions vote() and hasVoted() : handles vote to a survey
    public function vote($uid, $args)
    {
        XDB::execute('INSERT INTO  survey_votes
                              SET  survey_id = {?}, uid = {?}',
                     $this->id, ($uid == 0) ? null : $uid); // notes the user as having voted
        $vid = XDB::insertId();
        for ($i = 0; $i < count($this->questions); $i++) {
            $ans = $this->questions[$i]->checkAnswer($args[$i]);
            if (!is_null($ans) && is_array($ans)) {
                foreach ($ans as $a) {
                    XDB::execute('INSERT INTO survey_answers
                                          SET vote_id     = {?},
                                              question_id = {?},
                                              answer      = {?}', $vid, $i, $a);
                }
            }
        }
    }

    public function hasVoted($uid)
    {
        $res = XDB::query('SELECT  id
                             FROM  survey_votes
                            WHERE  survey_id = {?} AND uid = {?};', $this->id, $uid); // checks whether the user has already voted
        return ($res->numRows() != 0);
    }
    // }}}

    // {{{ static function deleteSurvey() : deletes a survey (and all its votes)
    public static function deleteSurvey($sid)
    {
        $sql = 'DELETE s.*, v.*, a.*
                  FROM surveys AS s
             LEFT JOIN survey_votes AS v
                    ON v.survey_id=s.id
             LEFT JOIN survey_answers AS a
                    ON a.vote_id=v.id
                 WHERE s.id={?};';
        return XDB::execute($sql, $sid);
    }
    // }}}

    // {{{ static function purgeVotes() : clears all votes concerning a survey (I'm not sure whether it's really useful)
    public static function purgeVotes($sid)
    {
        $sql = 'DELETE v.*, a.*
                  FROM survey_votes AS v
             LEFT JOIN survey_answers AS a
                    ON a.vote_id=v.id
                 WHERE v.survey_id={?};';
        return XDB::execute($sql, $sid);
    }
    // }}}

    // }}}
}
// }}}

// {{{ abstract class SurveyQuestion
abstract class SurveyQuestion
{
    // {{{ common properties, constructor, and basic methods
    private $question;
    private $comment;

    public function __construct($args)
    {
        $this->update($args);
    }

    public function update($a)
    {
        $this->question = $a['question'];
        $this->comment  = $a['comment'];
    }

    abstract protected function getQuestionType();
    // }}}

    // {{{ function toArray() : converts to array
    public function toArray()
    {
        return array('type' => $this->getQuestionType(), 'question' => $this->question, 'comment' => $this->comment);
    }
    // }}}

    // {{{ function checkSyntax() : checks question elements (before storing into database), not currently needed (with new structure)
    protected function checkSyntax()
    {
        return null;
    }
    // }}}

    // {{{ function checkAnswer : returns a correct answer (or a null value if error)
    public function checkAnswer($ans)
    {
        return null;
    }
    // }}}

    // {{{ functions regarding the results of a survey
    abstract public function getResultArray($sid, $qid);

    public function formatAnswer($ans)
    {
        return $ans;
    }

    public function getCSVColumns()
    {
        return $this->question;
    }
    // }}}
}
// }}}

// {{{ abstract class SurveySimple and its derived classes : "open" questions
// {{{ abstract class SurveySimple extends SurveyQuestion
abstract class SurveySimple extends SurveyQuestion
{
    public function checkAnswer($ans)
    {
        return array($ans);
    }

    public function getResultArray($sid, $qid)
    {
        $sql = 'SELECT answer
                  FROM survey_answers
                 WHERE vote_id IN (SELECT id FROM survey_votes WHERE survey_id={?})
                   AND question_id={?}
              ORDER BY RAND()
                 LIMIT 5;';
        $res = XDB::query($sql, $sid, $qid);
        return $res->fetchAllAssoc();
    }
}
// }}}

// {{{ class SurveyText extends SurveySimple : simple text field, allowing a few words
class SurveyText extends SurveySimple
{
    public function getQuestionType()
    {
        return "text";
    }
}
// }}}

// {{{ class SurveyTextarea extends SurveySimple : textarea field, allowing longer comments
class SurveyTextarea extends SurveySimple
{
    public function getQuestionType()
    {
        return "textarea";
    }
}
// }}}

// {{{ class SurveyNum extends SurveySimple : allows numerical answers
class SurveyNum extends SurveySimple
{
    public function checkAnswer($ans)
    {
        return array(intval($ans));
    }

    protected function getQuestionType()
    {
        return "num";
    }
}
// }}}
// }}}

// {{{ abstract class SurveyList and its derived classes : restricted questions that allows only a list of possible answers
// {{{ abstract class SurveyList extends SurveyQuestion
abstract class SurveyList extends SurveyQuestion
{
    protected $choices;

    public function update($args)
    {
        parent::update($args);
        $this->choices = array();
        foreach ($args['choices'] as $val) {
            if (trim($val) || trim($val) == '0') {
                $this->choices[] = $val;
            }
        }
    }

    public function toArray()
    {
        $rArr = parent::toArray();
        $rArr['choices'] = $this->choices;
        return $rArr;
    }

    public function getResultArray($sid, $qid)
    {
        $sql = 'SELECT answer, COUNT(id) AS count
                  FROM survey_answers
                 WHERE vote_id IN (SELECT id FROM survey_votes WHERE survey_id={?})
                   AND question_id={?}
              GROUP BY answer ASC';
        $res = XDB::query($sql, $sid, $qid);
        return $res->fetchAllAssoc();
    }

    public function formatAnswer($ans)
    {
        if (array_key_exists($ans, $this->choices)) {
            return $this->choices[$ans];
        } else {
            return null;
        }
    }
}
// }}}

// {{{ class SurveyRadio extends SurveyList : radio question, allows one answer among the list offered
class SurveyRadio extends SurveyList
{
    public function checkAnswer($ans)
    {
        $a = intval($ans);
        return (array_key_exists($a, $this->choices))? array($a) : null;
    }

    protected function getQuestionType()
    {
        return "radio";
    }
}
// }}}

// {{{ class SurveyCheckbox extends SurveyList : checkbox question, allows any number of answers among the list offered
class SurveyCheckbox extends SurveyList
{
    public function checkAnswer($ans)
    {
        $rep = array();
        foreach ($ans as $a) {
            $a = intval($a);
            if (array_key_exists($a, $this->choices)) {
                $rep[] = $a;
            }
        }
        return (count($rep) == 0)? null : $rep;
    }

    protected function getQuestionType()
    {
        return "checkbox";
    }
}
// }}}
// }}}

// {{{ abstract class SurveyTable and its derived classes : table question, each column represents a choice, each line represents a question
// {{{ abstract class SurveyTable extends SurveyList
abstract class SurveyTable extends SurveyList
{
    protected $subquestions;

    public function update($args)
    {
        parent::update($args);
        $this->subquestions = array();
        foreach ($args['subquestions'] as $val) {
            if (trim($val) || trim($val) == '0') {
                $this->subquestions[] = $val;
            }
        }
    }

    public function toArray()
    {
        $rArr = parent::toArray();
        $rArr['subquestions'] = $this->subquestions;
        return $rArr;
    }

    public function getResultArray($sid, $qid)
    {
        $sql = 'SELECT answer, COUNT(id) AS count
                  FROM survey_answers
                 WHERE vote_id IN (SELECT id FROM survey_votes WHERE survey_id={?})
                   AND question_id={?}
              GROUP BY answer ASC';
        $res = XDB::iterator($sql, $sid, $qid);
        $result = array();
        for ($i = 0; $i < count($this->subquestions); $i++) {
            $result[$i] = array_fill(0, count($this->choices), 0);
        }
        while ($r = $res->next()) {
            list($i, $j) = explode(':', $r['answer']);
            $result[$i][$j] = $r['count'];
        }
        return $result;
    }

    public function formatAnswer($ans)
    {
        list($q, $c) = explode(':', $ans);
        if (array_key_exists($q, $this->subquestions) && array_key_exists($c, $this->choices)) {
            return array('id' => $q, 'answer' => $this->choices[$c]);
        } else {
            return null;
        }
    }

    public function getCSVColumns()
    {
        $q = parent::getCSVColumns();
        if (empty($this->subquestions)) {
            return $q;
        }
        $a = array();
        for ($k = 0; $k < count($this->subquestions); $k++) {
            $a[$k] = $q.' : '.$this->subquestions[$k];
        }
        return $a;
    }
}
// }}}

// {{{ class SurveyRadioTable extends SurveyTable : SurveyTable with radio type choices
class SurveyRadioTable extends SurveyTable
{
    public function checkAnswer($ans)
    {
        $rep = array();
        foreach ($ans as $k => $a) {
            if (!array_key_exists($k, $this->subquestions)) {
                continue;
            }
            $a = intval($a);
            if (array_key_exists($a, $this->choices)) {
                $rep[] = $k . ':' . $a;
            }
        }
        return (count($rep) == 0)? null : $rep;
    }

    protected function getQuestionType()
    {
        return "radiotable";
    }

}
// }}}

// {{{ class SurveyCheckboxTable extends SurveyTable : SurveyTable with checkbox type choices
class SurveyCheckboxTable extends SurveyTable
{
    public function checkAnswer($ans)
    {
        $rep = array();
        foreach ($ans as $k => $aa) {
            if (!array_key_exists($k, $this->subquestions)) {
                continue;
            }
            foreach ($aa as $a) {
                $a = intval($a);
                if (array_key_exists($a, $this->choices)) {
                    $rep[] = $k . ':' . $a;
                }
            }
        }
        return (count($rep) == 0)? null : $rep;
    }

    protected function getQuestionType()
    {
        return "checkboxtable";
    }

}
// }}}
// }}}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker fenc=utf-8:
?>

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

// {{{ class Survey : root of any survey, contains all questions
class Survey
{
    // {{{ static properties and functions, regarding survey modes and question types
    const MODE_ALL    = 0;
    const MODE_XANON  = 1;
    const MODE_XIDENT = 2;
    private static $longModes = array(self::MODE_ALL    => "sondage ouvert &#224; tout le monde, anonyme",
                                      self::MODE_XANON  => "sondage restreint aux polytechniciens, anonyme",
                                      self::MODE_XIDENT => "sondage restreint aux polytechniciens, non anonyme");
    private static $shortModes = array(self::MODE_ALL    => "tout le monde, anonyme",
                                       self::MODE_XANON  => "polytechniciens, anonyme",
                                       self::MODE_XIDENT => "polytechniciens, non anonyme");

    public static function getModes($long = true) {
        return ($long)? self::$longModes : self::$shortModes;
    }

    private static $types = array('text'     => 'texte court',
                                  'textarea' => 'texte long',
                                  'num'      => 'num&#233;rique',
                                  'radio'    => 'radio',
                                  'checkbox' => 'checkbox',
                                  'personal' => 'informations personnelles');

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
        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $args['end'])) {
            $this->end = preg_replace('#^(\d{2})/(\d{2})/(\d{4})$#', '\3-\2-\1', $args['end']);
        } else {
            $this->end = (preg_match('#^\d{4}-\d{2}-\d{2}$#', $args['end']))? $args['end'] : '#';
        }
        $this->mode    = $args['mode'];
        if ($args['mode'] == 0) {
            $args['promos'] = '';
        }
        $this->promos  = ($args['promos'] == '' || preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $args['promos']))? $args['promos'] : '#';
    }
    // }}}

    // {{{ functions to access general information
    public function isMode($mode)
    {
        return ($this->mode == $mode);
    }

    public function checkPromo($promo)
    {
        $promos = explode('|', $this->promos);
        foreach ($promos as $p) {
            if ((preg_match('#^\d{4}$#', $p) && $p == $promo) ||
                (preg_match('#^\d{4}-$#', $p) && intval(substr($p, 0, 4)) <= $promo) ||
                (preg_match('#^-\d{4}$#', $p) && intval(substr($p, 1)) >= $promo) ||
                (preg_match('#^\d{4}-\d{4}$#', $p) && intval(substr($p, 0, 4)) <= $promo && intval(substr($p, 5)) >= $promo)) {
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

    // {{{ function toArray() : converts a question (or the whole survey) to array
    public function toArray($i = 'all')
    {
        if ($i != 'all' && $i != 'root') {
            $i = intval($i);
            if (array_key_exists($i, $this->questions)) {
                return $this->questions[$i]->toArray();
            } else {
                return null;
            }
        } else {
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
            if ($i == 'all' && count($this->questions) > 0) {
                $qArr = array();
                for ($k = 0; $k < count($this->questions); $k++) {
                    $q = $this->questions[$k]->toArray();
                    $q['id'] = $k;
                    $qArr[$k] = $q;
                }
                $a['questions'] = $qArr;
            }
            return $a;
        }
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
        case 'personal':
            return new SurveyPersonal($args);
        default:
            return null;
        }
    }
    // }}}

    // {{{ questions manipulation functions
    public function addQuestion($i, $c)
    {
        if ($this->valid || $i > count($this->questions)) {
            return false;
        } else {
            array_splice($this->questions, $i, 0, array($c));
            return true;
        }
    }

    public function delQuestion($i)
    {
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
        "dateformat"  => "la date de fin de sondage est mal formatt&#233;e : elle doit respecter la syntaxe dd/mm/aaaa",
        "datepassed"  => "la date de fin de sondage est d&#233;j&#224; d&#233;pass&#233;e : vous devez pr&#233;ciser une date future",
        "promoformat" => "les restrictions &#224; certaines promotions sont mal formatt&#233;es"
    );

    public function checkSyntax()
    {
        $rArr = array();
        if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $this->end)) {
            $rArr[] = array('question' => 'root', 'error' => self::$errorMessages["dateformat"]);
        } else {
            // checks that the end date given is not already passed
            // (unless the survey has already been validated : an admin can have a validated survey expired)
            if (!$this->valid && $this->isEnded()) {
                $rArr[] = array('question' => 'root', 'error' => self::$errorMessages["datepassed"]);
            }
        }
        if ($this->promos != '' && !preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $this->promos)) {
            $rArr[] = array('question' => 'root', 'error' => self::$errorMessages["promoformat"]);
        }
        return (empty($rArr))? null : $rArr;
    }
    // }}}

    // {{{ functions that manipulates surveys in database
    // {{{ static function retrieveList() : gets the list of available survey (current, old and not validated surveys)
    public static function retrieveList($type, $tpl = true)
    {
        switch ($type) {
        case 'w':
        case 'waiting' :
            $where = 'valid=0';
            break;
        case 'c':
        case 'current':
            $where = 'valid=1 AND end > NOW()';
            break;
        case 'o':
        case 'old':
            $where = 'valid=1 AND end <= NOW()';
            break;
        default:
            return null;
        }
        $sql = 'SELECT id, title, end, mode
                  FROM survey_surveys
                 WHERE '.$where.';';
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
        $sql = 'SELECT questions, title, description, end, mode, promos, valid
                  FROM survey_surveys
                 WHERE id={?}';
        $res = XDB::query($sql, $sid);
        $data = $res->fetchOneAssoc();
        if (is_null($data) || !is_array($data)) {
            return null;
        }
        $survey = new Survey($data, $sid, (boolean) $data['valid'], unserialize($data['questions']));
        return $survey;
    }
    // }}}

    // {{{ static function retrieveSurveyInfo() : gets information about a survey (title, description, end date, restrictions) but does not unserialize the survey object structure
    public static function retrieveSurveyInfo($sid)
    {
        $sql = 'SELECT title, description, end, mode, promos, valid
                  FROM survey_surveys
                 WHERE id={?}';
        $res = XDB::query($sql, $sid);
        return $res->fetchOneAssoc();
    }
    // }}}

    // {{{ function proposeSurvey() : stores a proposition of survey in database (before validation)
    public function proposeSurvey()
    {
        $sql = 'INSERT INTO survey_surveys
                        SET questions={?},
                            title={?},
                            description={?},
                            author_id={?},
                            end={?},
                            mode={?},
                            promos={?},
                            valid=0;';
        return XDB::execute($sql, serialize($this->questions), $this->title, $this->description, S::v('uid'), $this->end, $this->mode, $this->promos);
    }
    // }}}

    // {{{ function updateSurvey() : updates a survey in database (before validation)
    public function updateSurvey()
    {
        if ($this->id == -1) {
            return false;
        }
        $sql = 'UPDATE survey_surveys
                   SET questions={?},
                       title={?},
                       description={?},
                       end={?},
                       mode={?},
                       promos={?}
                 WHERE id={?};';
        return XDB::execute($sql, serialize($this->questions), $this->title, $this->description, $this->end, $this->mode, $this->promos, $this->id);
    }
    // }}}

    // {{{ static function validateSurvey() : validates a survey
    public static function validateSurvey($sid)
    {
        $sql = 'UPDATE survey_surveys
                   SET valid=1
                 WHERE id={?};';
        return XDB::execute($sql, $sid);
    }
    // }}}

    // {{{ functions vote() and hasVoted() : handles vote to a survey
    public function vote($uid, $args)
    {
        XDB::execute('INSERT INTO survey_votes
                              SET survey_id={?}, user_id={?};', $this->id, $uid); // notes the user as having voted
        $vid = XDB::insertId();
        for ($i = 0; $i < count($this->questions); $i++) {
            $ans = $this->questions[$i]->checkAnswer($args[$i]);
            if ($ans != "") {
                XDB::execute('INSERT INTO survey_answers
                                      SET vote_id     = {?},
                                          question_id = {?},
                                          answer      = {?}', $vid, $i, $ans);
            }
        }
    }

    public function hasVoted($uid)
    {
        $res = XDB::query('SELECT id
                             FROM survey_votes
                            WHERE survey_id={?} AND user_id={?};', $this->id, $uid); // checks whether the user has already voted
        return ($res->numRows() != 0);
    }
    // }}}

    // {{{ static function deleteSurvey() : deletes a survey (and all its votes)
    public static function deleteSurvey($sid)
    {
        $sql = 'DELETE s.*, v.*, a.*
                  FROM survey_surveys AS s
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

    // {{{ function checkAnswer : returns a correctly formatted answer (or nothing empty string if error)
    public function checkAnswer($ans)
    {
        return "";
    }
    // }}}

    // {{{ function resultArray() : statistics on the results of the survey
    //abstract protected function resultArray($sid, $where);
    // }}}
}
// }}}

// {{{ abstract class SurveySimple extends SurveyQuestion : "opened" questions
abstract class SurveySimple extends SurveyQuestion
{
    public function checkAnswer($ans)
    {
        return $ans;
    }
}

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
        return intval($ans);
    }

    protected function getQuestionType()
    {
        return "num";
    }
}
// }}}
// }}}

// {{{ abstract class SurveyList extends SurveyTreeable : restricted questions that allows only a list of possible answers
abstract class SurveyList extends SurveyQuestion
{
    protected $choices;

    public function update($args)
    {
        parent::update($args);
        $this->choices = explode('|', $args['options']);
    }

    public function toArray()
    {
        $rArr = parent::toArray();
        $rArr['choices'] = $this->choices;
        $rArr['options'] = implode('|', $this->choices);
        return $rArr;
    }

}

// {{{ class SurveyRadio extends SurveyList : radio question, allows one answer among the list offered
class SurveyRadio extends SurveyList
{
    public function checkAnswer($ans)
    {
        return (array_key_exists($ans, $this->choices)) ? $ans : "";
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
        $rep = "|";
        foreach ($ans as $a) {
            if (array_key_exists($a,$this->choices)) {
                $rep .= $a . "|";
            }
        }
        return $rep;
    }

    protected function getQuestionType()
    {
        return "checkbox";
    }
}
// }}}
// }}}

// {{{ class SurveyPersonal extends SurveyQuestion : allows easy and verified access to user's personal data (promotion, name...)
// actually this type of question should be suppressed (non anonymous surveys are possible with survey modes)
// and anyway it is not finished (checkAnswer implementation) : currently it does not store anything when a user votes
class SurveyPersonal extends SurveyQuestion
{
    private $perm;

    public function update($args)
    {
        $args['question'] = "Informations personnelles";
        parent::update($args);
        $this->perm['promo'] = isset($args['promo'])? 1 : 0;
        $this->perm['name'] = isset($args['name'])? 1 : 0;
    }

    public function checkAnswer($ans)
    {
        if (intval($ans) == 1) {
            // requete mysql qvb
            return "";
        } else {
            return "";
        }
    }

    protected function getQuestionType()
    {
        return "personal";
    }

    public function toArray()
    {
        $a = parent::toArray();
        $a['promo'] = $this->perm['promo'];
        $a['name']  = $this->perm['name'];
        return $a;
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

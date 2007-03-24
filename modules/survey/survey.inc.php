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

// {{{ class Survey : static database managing functions
class SurveyDB
{
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
        $sql = 'SELECT survey_id, title, end
                  FROM survey_questions
                 WHERE '.$where.';';
        if ($tpl) {
            return XDB::iterator($sql);
        } else {
            return XDB::iterRow($sql);
        }
    }
    // }}}

    // {{{ static function proposeSurvey() : stores a proposition of survey in database (before validation)
    public static function proposeSurvey($survey)
    {
        $sql = 'INSERT INTO survey_questions
                        SET questions={?},
                            title={?},
                            description={?},
                            author_id={?},
                            end={?},
                            promos={?},
                            valid=0;';
        $data = $survey->storeArray();
        return XDB::execute($sql, serialize($survey), $data['question'], $data['comment'], S::v('uid'), $data['end'], $data['promos']);
    }
    // }}}

    // {{{ static function updateSurvey() : updates a survey in database (before validation)
    public static function updateSurvey($survey, $sid)
    {
        $sql = 'UPDATE survey_questions
                   SET questions={?},
                       title={?},
                       description={?},
                       end={?},
                       promos={?}
                 WHERE survey_id={?};';
        $data = $survey->storeArray();
        return XDB::execute($sql, serialize($survey), $data['question'], $data['comment'], $data['end'], $data['promos'], $sid);
    }
    // }}}

    // {{{ static function retrieveSurvey() : gets a survey in database (and unserialize the survey object structure)
    public static function retrieveSurvey($sid)
    {
        $sql = 'SELECT questions, title, description, end, promos, valid
                  FROM survey_questions
                 WHERE survey_id={?}';
        $res = XDB::query($sql, $sid);
        $data = $res->fetchOneAssoc();
        if (is_null($data) || !is_array($data)) {
            return null;
        }
        $survey = unserialize($data['questions']);
        if (isset($data['end'])) {
            $data['end'] = preg_replace('#^(\d{4})-(\d{2})-(\d{2})$#', '\3/\2/\1', $data['end']);
        }
        $survey->update(array('question' => $data['title'], 'comment' => $data['description'], 'end' => $data['end'], 'promos' => $data['promos']));
        $survey->setValid($data['valid']);
        return $survey;
    }
    // }}}

    // {{{ static function retrieveSurveyInfo() : gets information about a survey (title, description, end date, restrictions) but does not unserialize the survey object structure
    public static function retrieveSurveyInfo($sid)
    {
        $sql = 'SELECT title, description, end, promos, valid
                  FROM survey_questions
                 WHERE survey_id={?}';
        $res = XDB::query($sql, $sid);
        return $res->fetchOneAssoc();
    }
    // }}}

    // {{{ static function validateSurvey() : validates a survey
    public static function validateSurvey($sid)
    {
        $sql = 'UPDATE survey_questions
                   SET valid=1
                 WHERE survey_id={?};';
        return XDB::execute($sql, $sid);
    }
    // }}}

    // {{{ static function deleteSurvey() : deletes a survey (and all its votes)
    public static function deleteSurvey($sid)
    {
        $sql1 = 'DELETE FROM survey_questions
                       WHERE survey_id={?};';
        $sql2 = 'DELETE FROM survey_answers
                       WHERE survey_id={?};';
        $sql3 = 'DELETE FROM survey_votes
                       WHERE survey_id={?};';
        return (XDB::execute($sql1, $sid) && XDB::execute($sql2, $sid) && XDB::execute($sql3, $sid));
    }
    // }}}
}
// }}}

// {{{ abstract class SurveyQuestion
abstract class SurveyQuestion
{
    // {{{ static properties and methods regarding question types
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

    // {{{ common properties, constructor, and basic methods
    private $survey_id;
    private $id;
    private $question;
    private $comment;

    protected function __construct($i, $args)
    {
        $this->id = $i;
        $this->update($args);
    }

    protected function update($a)
    {
        $this->question = $a['question'];
        $this->comment  = $a['comment'];
    }

    protected function getId()
    {
        return $this->id;
    }

    abstract protected function getQuestionType();
    // }}}

    // {{{ tree manipulation methods : not implemented here (but definition needed)
    protected function addChildNested($i, $c)
    {
        return false;
    }

    protected function addChildAfter($i, $c)
    {
        return false;
    }

    protected function delChild($i)
    {
        return false;
    }
    // }}}

    // {{{ function edit($i, $a) : searches and edits question $i
    protected function edit($i, $a)
    {
        if ($this->id == $i) {
            $this->update($a);
            return true;
        } else {
            return false;
        }
    }
    // }}}

    // {{{ functions toArray() and searchToArray($i) : (searches and) converts to array
    protected function toArray()
    {
        return $this->storeArray();
    }

    protected function searchToArray($i)
    {
        if ($this->id == $i) {
            return $this->storeArray();
        } else {
            return null;
        }
    }

    protected function storeArray()
    {
        return array('type' => $this->getQuestionType(), 'id' => $this->id,  'question' => $this->question, 'comment' => $this->comment);
    }
    // }}}

    // {{{ function checkSyntax() : checks question elements (before storing into database)
    protected function checkSyntax()
    {
        return null;
    }
    // }}}

    // {{{ function vote() : handles vote
    protected function checkAnswer($ans)
    {
        return "";
    }

    function vote($sid, $vid, $a)
    {
        $ans = $this->checkAnswer($a[$this->getId]);
        if ($ans != "") {
            XDB::execute(
                'INSERT INTO survey_answers
                         SET survey_id   = {?},
                             vote_id     = {?},
                             question_id = {?},
                             answer      = "{?}"', $sid, $vid, $id, $ans);
        }
    }
    // }}}
}
// }}}

// {{{ abstract class SurveyTreeable extends SurveyQuestion : questions that allow nested ones
abstract class SurveyTreeable extends SurveyQuestion
{
    // {{{ common properties, constructor
    private $children;

    protected function __construct($i, $args)
    {
        parent::__construct($i, $args);
        $this->children = array();
    }
    // }}}

    // {{{ tree manipulation functions : actual implementation
    protected function hasChild()
    {
        return !is_null($this->children) && is_array($this->children);
    }

    protected function addChildNested($i, $c)
    {
        if ($this->getId() == $i) {
            if ($this->hasChild()) {
                array_unshift($this->children, $c);
            } else {
                $this->children = array($c);
            }
            return true;
        } else {
            foreach ($this->children as $child) {
                if ($child->addChildNested($i, $c)) {
                    return true;
                }
            }
            return false;
        }
    }

    protected function addChildAfter($i, $c)
    {
        $found = false;
        for ($k = 0; $k < count($this->children); $k++) {
            if ($this->children[$k]->getId() == $i) {
                $found = true;
                break;
            } else {
                if ($this->children[$k]->addChildAfter($i, $c)) {
                    return true;
                }
            }
        }
        if ($found) {
            array_splice($this->children, $k+1, 0, array($c));
            return true;
        }
        return false;
    }

    protected function delChild($i)
    {
        $found = false;
        for ($k = 0; $k < count($this->children); $k++) {
            if ($this->children[$k]->getId() == $i) {
                $found = true;
                break;
            } else {
                if ($this->children[$k]->delChild($i)) {
                    return true;
                }
            }
        }
        if ($found) {
            array_splice($this->children, $k, 1);
            return true;
        }
        return false;
    }
    // }}}

    // {{{ function edit() with tree support
    protected function edit($i, $a)
    {
        if ($this->getId() == $i) {
            $this->update($a);
            return true;
        } else {
            foreach ($this->children as $child) {
                if ($child->edit($i, $a)) {
                    return true;
                }
            }
            return false;
        }
    }
    // }}}

    // {{{ functions toArray() and searchToArray() with tree support
    protected function toArray()
    {
        if ($this->hasChild()) {
            $cArr = array();
            foreach ($this->children as $child) {
                $cArr[] = $child->toArray();
            }
            $a = $this->storeArray();
            $a['children'] = $cArr;
            return $a;
        } else {
            return $this->storeArray();
        }
    }

    protected function searchToArray($i)
    {
        if ($this->getId() == $i) {
            return $this->storeArray();
        } else {
            foreach ($this->children as $child) {
                $a = $child->searchToArray($i);
                if (!is_null($a) && is_array($a)) {
                    return $a;
                }
            }
            return null;
        }
    }
    // }}}

    // {{{ function checkSyntax()
    protected function checkSyntax()
    {
        $rArr = array();
        foreach ($this->children as $child) {
            $a = $child->checkSyntax();
            if ($a != null) {
                $rArr[] = $a;
            }
        }
        return (empty($rArr))? null : $rArr;
    }
    // }}}

    // {{{ function vote()
    function vote($sid, $vid, $a)
    {
        parent::vote($sid, $vid, $a);
        if ($this->hasChild()) {
            foreach ($this->children as $c) {
                $c->vote($sid, $vid, $a);
            }
        }
    }
    // }}}
}
// }}}

// {{{ class SurveyRoot extends SurveyTreeable : root of any survey, actually the only entry point (no public methods outside this class)
class SurveyRoot extends SurveyTreeable
{
    // {{{ properties, constructor and basic methods
    private $last_id;
    private $beginning;
    private $end;
    private $promos;
    private $valid;

    public function __construct($args)
    {
        parent::__construct(0, $args);
        $this->last_id   = 0;
    }

    public function update($args)
    {
        parent::update($args);
        //$this->beginning = $args['beginning_year'] . "-" . $args['beginning_month'] . "-" . $args['beginning_day'];
        //$this->end       = $args['end_year']       . "-" . $args['end_year']        . "-" . $args['end_day'];
        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $args['end'])) {
            $this->end = preg_replace('#^(\d{2})/(\d{2})/(\d{4})$#', '\3-\2-\1', $args['end']);
        } else {
            $this->end = (preg_match('#^\d{4}-\d{2}-\d{2}$#', $args['end']))? $args['end'] : '#';
        }
        $this->promos  = ($args['promos'] == '' || preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $args['promos']))? $args['promos'] : '#';
    }

    private function getNextId()
    {
        $this->last_id++;
        return $this->last_id;
    }

    public function setValid($v)
    {
        $this->valid = (boolean) $v;
    }

    public function isValid()
    {
        return $this->valid;
    }

    protected function getQuestionType()
    {
        return "root";
    }
    // }}}

    // {{{ function factory($type, $args) : builds a question according to the given type
    public function factory($t, $args)
    {
        $i = $this->getNextId();
        switch ($t) {
        case 'text':
            return new SurveyText($i, $args);
        case 'textarea':
            return new SurveyTextarea($i, $args);
        case 'num':
            return new SurveyNum($i, $args);
        case 'radio':
            return new SurveyRadio($i, $args);
        case 'checkbox':
            return new SurveyCheckbox($i, $args);
        case 'personal':
            return new SurveyPersonal($i, $args);
        default:
            return null;
        }
    }
    // }}}

    // {{{ methods needing public access
    public function addChildNested($i, $c)
    {
        return !$this->isValid() && parent::addChildNested($i, $c);
    }

    public function addChildAfter($i, $c)
    {
        return !$this->isValid() && parent::addChildAfter($i, $c);
    }

    public function delChild($i)
    {
        return !$this->isValid() && parent::delChild($i);
    }

    public function edit($i, $a)
    {
        return (!$this->isValid() || $this->getId() == $i) && parent::edit($i, $a);
    }

    public function toArray()
    {
        return parent::toArray();
    }

    public function searchToArray($i)
    {
        return parent::searchToArray($i);
    }
    // }}}

    // {{{ function storeArray()
    public function storeArray()
    {
        $rArr = parent::storeArray();
        $rArr['beginning'] = $this->beginning;
        $rArr['end']       = $this->end;
        $rArr['promos']    = $this->promos;
        $rArr['valid']     = $this->valid;
        return $rArr;
    }
    // }}}

    // {{{ function checkSyntax()
    private static $errorMessages = array(
        "dateformat"  => "la date de fin de sondage est mal formatt&#233;e : elle doit respecter la syntaxe dd/mm/aaaa",
        "datepassed"  => "la date de fin de sondage est d&#233;j&#224; d&#233;pass&#233;e : vous devez pr&#233;ciser une date future",
        "promoformat" => "les restrictions &#224; certaines promotions sont mal formatt&#233;es"
    );

    public function checkSyntax()
    {
        $rArr = parent::checkSyntax();
        if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $this->end)) {
            $rArr[] = array('question' => $this->getId(), 'error' => self::$errorMessages["dateformat"]);
        } else {
            if (strtotime($this->end) - time() <= 0) {
                $rArr[] = array('question' => $this->getId(), 'error' => self::$errorMessages["datepassed"]);
            }
        }
        if ($this->promos != '' && !preg_match('#^(\d{4}-?|(\d{4})?-\d{4})(,(\d{4}-?|(\d{4})?-\d{4}))*$#', $this->promos)) {
            $rArr[] = array('question' => $this->getId(), 'error' => self::$errorMessages["promoformat"]);
        }
        return (empty($rArr))? null : $rArr;
    }
    // }}}
}
// }}}

// {{{ abstract class SurveySimple extends SurveyQuestion : "opened" questions
abstract class SurveySimple extends SurveyQuestion
{
    protected function checkAnswer($ans)
    {
        return $ans;
    }
}

// {{{ class SurveyText extends SurveySimple : simple text field, allowing a few words
class SurveyText extends SurveySimple
{
    protected function getQuestionType()
    {
        return "text";
    }
}
// }}}

// {{{ class SurveyTextarea extends SurveySimple : textarea field, allowing longer comments
class SurveyTextarea extends SurveySimple
{
    protected function getQuestionType()
    {
        return "textarea";
    }
}
// }}}

// {{{ class SurveyNum extends SurveySimple : allows numerical answers
class SurveyNum extends SurveySimple
{
    protected function checkAnswer($ans)
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
abstract class SurveyList extends SurveyTreeable
{
    private $choices;

    protected function update($args)
    {
        parent::update($args);
        $this->choices = explode('|', $args['options']);
    }

    protected function storeArray()
    {
        $rArr = parent::storeArray();
        $rArr['choices'] = $this->choices;
        $rArr['options'] = implode('|', $this->choices);
        return $rArr;
    }

}

// {{{ class SurveyRadio extends SurveyList : radio question, allows one answer among the list offered
class SurveyRadio extends SurveyList
{
    protected function checkAnswer($ans)
    {
        return (in_array($ans, $this->choices)) ? $ans : "";
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
    protected function checkAnswer($ans)
    {
        $rep = "";
        foreach ($this->choices as $key => $value) {
            if (array_key_exists($key,$v[$id]) && $v[$id][$key]) {
                $rep .= "|" . $key;
            }
        }
        $rep = (strlen($rep) >= 4) ? substr($rep, 4) : "";
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
class SurveyPersonal extends SurveyQuestion
{
    private $perm;

    protected function update($args)
    {
        $args['question'] = "Informations personnelles";
        parent::update($args);
        $this->perm['promo'] = isset($args['promo'])? 1 : 0;
        $this->perm['name'] = isset($args['name'])? 1 : 0;
    }

    protected function checkAnswer($ans)
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

    protected function storeArray()
    {
        $a = parent::storeArray();
        $a['promo'] = $this->perm['promo'];
        $a['name']  = $this->perm['name'];
        return $a;
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

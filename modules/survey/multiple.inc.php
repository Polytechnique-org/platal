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

class SurveyQuestionMultiple extends SurveyQuestion
{
    public function __construct(Survey $survey)
    {
        parent::__construct($survey);
        $this->type = "multiple";
    }

    protected function buildAnswer(SurveyAnswer $answer, PlDict $data)
    {
        $content = $data->v($this->qid);
        $value   = $content['answers'];
        if (empty($value)) {
            $answer->answer = null;
            return true;
        }
        if ($this->parameters['subtype'] == 'radio') {
            if (count($value) > 1) {
                throw new Exception("You cannot select more than one answer");
            }
        }
        $answers = array();
        $answers['answers'] = array();
        foreach ($value as $key=>$text) {
            if (can_convert_to_integer($key)) {
                $key = to_integer($key);
                if ($text != $this->parameters['answers'][$key]) {
                    throw new Exception("Answer text does not match");
                }
                $answers['answers'][] = $key;
            } else if ($key != 'other') {
                throw new Exception("Unsupported answer id $key");
            } else if (!$this->parameters['allow_other']) {
                throw new Exception("Got 'Other' answer while not supported");
            } else if (!isset($content['other'])) {
                $answers['other'] = '';
            } else {
                $answers['other'] = $content['other'];
            }
        }
        if (empty($value)) {
            $answer->answer = null;
            return false;
        } else {
            $answer->answer = $answers;
        }
        return true;
    }
}

?>

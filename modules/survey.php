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

class SurveyModule extends PLModule
{
    // {{{ function handlers() : registers the different handlers
    function handlers()
    {
        return array(
            'survey'              => $this->make_hook('index', AUTH_COOKIE),
            'survey/edit'         => $this->make_hook('edit', AUTH_COOKIE),
            'survey/ajax'         => $this->make_hook('ajax', AUTH_COOKIE),
            'survey/admin'        => $this->make_hook('admin', AUTH_MDP, 'admin'),
            'survey/admin/edit'   => $this->make_hook('adminEdit', AUTH_MDP, 'admin'),
            'survey/admin/valid'  => $this->make_hook('adminValidate', AUTH_MDP, 'admin'),
            'survey/admin/del'    => $this->make_hook('adminDelete', AUTH_MDP, 'admin'),
        );
    }
    // }}}

    // {{{ function handler_index() : lists all available surveys
    function handler_index(&$page, $action = null)
    {
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $page->changeTpl('survey/index.tpl');
        $page->assign('survey_current', SurveyDB::retrieveList('c'));
        $page->assign('survey_old', SurveyDB::retrieveList('o'));
    }
    // }}}

    // {{{ function handler_admin() : index of admin mode
    function handler_admin(&$page)
    {
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $page->changeTpl('survey/admin.tpl');
        $page->assign('survey_waiting', SurveyDB::retrieveList('w'));
        $page->assign('survey_current', SurveyDB::retrieveList('c'));
        $page->assign('survey_old', SurveyDB::retrieveList('o'));
    }
    // }}}

    // {{{ function handler_adminEdit() : edits a survey in admin mode
    function handler_adminEdit(&$page, $id)
    {
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $survey = SurveyDB::retrieveSurvey($id); // retrieves the survey in database
        S::kill('survey'); // cleans session (in case there would have been a problem before)
        S::kill('survey_id');
        if ($survey->isValid()) {
            return $this->show_error($page, "Il est impossible de modifier un sondage d&#233;j&#224; valid&#233;", 'admin');
        }
        $_SESSION['survey']    = serialize($survey);
        $_SESSION['survey_id'] = $id;
        $this->handler_edit($page, 'show'); // calls handler_edit, but in admin mode since 'survey_id' is in session
    }
    // }}}

    // {{{ function handler_adminValidate() : validates a survey (admin mode)
    function handler_adminValidate(&$page, $id = -1)
    {
        if (Post::has('survey_cancel')) { // if the admin cancels the validation, returns to the admin index
            return $this->handler_admin(&$page);
        }
        $id = Post::v('survey_id', $id);
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit &#234;tre pr&#233;cis&#233;.", 'admin');
        }
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $surveyInfo = SurveyDB::retrieveSurveyInfo($id); // retrieves information about the survey (does not retrieve and unserialize the object structure)
        if ($surveyInfo == null) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'admin');
        }
        if (Post::has('survey_submit')) { // needs a confirmation before validation
            if (SurveyDB::validateSurvey($id)) { // validates the survey (in the database)
                $this->show_success($page, "Le sondage \"".$surveyInfo['title']."\" a bien &#233;t&#233; valid&#233;, les votes sont maintenant ouverts.", 'admin');
            } else {
                $this->show_error($page, '', 'admin');
            }
        } else { // asks for a confirmation
            $this->show_confirm($page, "&#202;tes-vous certain de vouloir valider le sondage \"".$surveyInfo['title']."\" ? "
                                      ."Les votes seront imm&#233;diatement ouverts.", 'admin/valid', array('id' => $id));
        }
    }
    // }}}

    // {{{ function handler_adminDelete() : deletes a survey (admin mode)
    function handler_adminDelete(&$page, $id = -1)
    {
        if (Post::has('survey_cancel')) { // if the admin cancels the suppression, returns to the admin index
            return $this->handler_admin(&$page);
        }
        $id = Post::v('survey_id', $id);
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit &#234;tre pr&#233;cis&#233;.", 'admin');
        }
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $surveyInfo = SurveyDB::retrieveSurveyInfo($id); // retrieves information about the survey (does not retrieve and unserialize the object structure)
        if ($surveyInfo == null) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'admin');
        }
        if (Post::has('survey_submit')) { // needs a confirmation before suppression
            if (SurveyDB::deleteSurvey($id)) { // deletes survey in database
                $this->show_success($page, "Le sondage \"".$surveyInfo['title']."\" a bien &#233;t&#233; supprim&#233;, ainsi que tous les votes le concernant.", 'admin');
            } else {
                $this->show_error($page, '', 'admin');
            }
        } else { // asks for a confirmation
            $this->show_confirm($page, "&#202;tes-vous certain de vouloir supprimer le sondage \"".$surveyInfo['title']."\" ?", 'admin/delete', array('id' => $id));
        }
    }
    // }}}

    // {{{ function handler_edit() : edits a survey (in normal mode unless called by handler_adminEdit() )
    function handler_edit(&$page, $action = 'show', $qid = 0)
    {
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        $action = Post::v('survey_action', $action);
        $qid    = Post::v('survey_qid', $qid);
        if (Post::has('survey_cancel')) { // after cancelling changes, shows the survey
            if (S::has('survey')) {
                $action = 'show';
            } else {                      // unless no editing has been done at all (shows to the surveys index page)
                return $this->handler_index($page);
            }
        }
        if (S::has('survey_id')) { // if 'survey_id' is in session, it means we are modifying a survey in admin mode
            $page->assign('survey_adminmode', true);
        }
        if ($action == 'show' && !S::has('survey')) {
            $action = 'new';
        }
        if ($action == 'question') { // {{{ modifies an existing question
            if (Post::has('survey_submit')) { // if the form has been submitted, makes the modifications
                $survey = unserialize(S::v('survey'));
                $args   = Post::v('survey_question');
                if (!$survey->edit($qid, $args)) { // update the survey object structure
                    return $this->show_error($page, '', 'edit');
                }
                $this->show_survey($page, $survey);
                $_SESSION['survey'] = serialize($survey);
            } else { // if a form has not been submitted, shows modification form
                $survey = unserialize(S::v('survey'));
                $current = $survey->searchToArray($qid); // gets the current parameters of the question
                if ($current == null) {
                    return $this->show_error($page, '', 'edit');
                }
                $this->show_form($page, $action, $qid, $current['type'], $current);
            } // }}}
        } elseif ($action == 'new') { // {{{ create a new survey : actually store the root question
            if (Post::has('survey_submit')) { // if the form has been submitted, creates the survey
                S::kill('survey');
                S::kill('survey_id');
                $survey = new SurveyRoot(Post::v('survey_question')); // creates the object structure
                $this->show_survey($page, $survey);
                $_SESSION['survey'] = serialize($survey);
            } else {
                S::kill('survey');
                S::kill('survey_id');
                $this->show_form($page, $action, 0, 'root');
            } // }}}
        } elseif ($action == 'nested' || $action == 'after') { // {{{ adds a new question, nested in the current node, or on the same level after it
            if (Post::has('survey_submit')) { // if the form has been submitted, adds the question
                $survey = unserialize(S::v('survey'));
                $question = $survey->factory(Post::v('survey_type'), Post::v('survey_question')); // creates the question object, with a sort of 'factory' method
                if ($action == 'nested') {
                    if (!$survey->addChildNested($qid, $question)) {
                        return $this->show_error($page, '', 'edit');
                    }
                } else {
                    if (!$survey->addChildAfter($qid, $question)) {
                        return $this->show_error($page, '', 'edit');
                    }
                }
                $this->show_survey($page, $survey);
                $_SESSION['survey'] = serialize($survey);
            } else {
                $this->show_form($page, $action, $qid);
            } // }}}
        } elseif ($action == 'del') { // {{{ deletes a question
            if (Post::has('survey_submit')) { // if a confirmation has been sent, deletes the question
                $survey = unserialize(S::v('survey'));
                if (!$survey->delChild(Post::v('survey_qid'))) { // deletes the node in the survey object structure
                    return $this->show_error($page, '', 'edit');
                }
                $this->show_survey($page, $survey);
                $_SESSION['survey'] = serialize($survey);
            } else { // if user has not confirmed, shows a confirmation form
                $survey = unserialize(S::v('survey'));
                $current = $survey->searchToArray($qid); // needed to get the title of the question to delete (more user-friendly than an id)
                if ($current == null) {
                    return $this->show_error($page, '', 'edit');
                }
                $this->show_confirm($page, '&#202;tes-vous certain de vouloir supprimer la question intitul&#233; "'.$current['question'].'" ? '
                                          .'Attention, cela supprimera en m&#234;me temps toutes les questions qui d&#233;pendent de celle-ci.',
                                                'edit', array('action' => 'del', 'qid' => $qid));
            } // }}}
        } elseif ($action == 'show') { // {{{ simply shows the survey in its current state
            $this->show_survey($page, unserialize(S::v('survey'))); // }}}
        } elseif ($action == 'valid') { // {{{ validates the proposition, i.e stores the proposition in the database
                                        // but an admin will still need to validate the survey before it is activated
            if (Post::has('survey_submit')) { // needs a confirmation before storing the proposition
                $survey = unserialize(S::v('survey'));
                if (S::has('survey_id')) { // if 'survey_id' is in session, we are modifying an existing survey (in admin mode) instead of proposing a new one
                    if (SurveyDB::updateSurvey($survey, S::v('survey_id'))) { // updates the database according the new survey object structure
                        $this->show_success($page, "Les modifications sur le sondage ont bien &#233;t&#233; enregistr&#233;es.", 'admin');
                    } else {
                        $this->show_error($page, '', 'admin');
                    }
                } else { // if no 'survey_id' is in session, we are indeed proposing a new survey
                    if (SurveyDB::proposeSurvey($survey)) { // stores the survey object structure in database
                        $this->show_success($page, "Votre proposition de sondage a bien &#233;t&#233; enregistr&#233;e,
                                                    elle est en attent de validation par un administrateur du site.", '');
                    } else {
                        $this->show_error($page);
                    }
                }
                S::kill('survey'); // cleans session
                S::kill('survey_id');
            } else { // asks for a confirmation if it has not been sent
                $survey = unserialize(S::v('survey'));
                $errors = $survey->checkSyntax();
                if (!is_null($errors)) {
                    $this->show_error($page, "", 'edit', $errors);
                } else {
                    if (S::has('survey_id')) {
                        $this->show_confirm($page, "Veuillez confirmer l'enregistrement des modifications apport&#233;es &#224; ce sondage", 'edit', array('action' => 'valid'));
                    } else {
                        $this->show_confirm($page, "Veuillez confirmer l'envoi de cette proposition de sondage.", 'edit', array('action' => 'valid'));
                    }
                }
            } // }}}
        } elseif ($action == 'cancel') { // {{{ cancels the creation/modification of a survey
            if (Post::has('survey_submit')) { // needs a confirmation
                S::kill('survey'); // cleans session
                if (S::has('survey_id')) {              // only possible when modifying a survey in admin mode, still this should be considered again,
                    S::kill('survey_id');               // maybe some name with "admin" in it, "survey_adminid" or anything that might not be confusing.
                    return $this->handler_admin($page); // in this case, shows the admin index
                } else {
                    return $this->handler_index($page); // else shows the 'normal' index
                }
            } else { // asks for a confirmation if it has not been sent
                $this->show_confirm(&$page, "&#202;tes-vous certain de vouloir annuler totalement l'&#233;dition de ce sondage ? Attention, "
                                           ."toutes les donn&#233;es &#233;dit&#233;es jusque l&#226; seront d&#233;finitivement perdues.",
                                                'edit', array('action' => $action));
            }
        } // }}}
    }
    // }}}
 
    // {{{ function handler_ajax() : some ajax in editing a new question (for now, there may be a little more later)
    function handler_ajax(&$page, $type)
    {
        require_once dirname(__FILE__).'/survey/survey.inc.php';
        header('Content-Type: text/html; charset="UTF-8"');
        if (SurveyQuestion::isType($type)) { // when type has been chosen, the form is updated to fit exactly the type of question chosen
            $page->changeTpl('survey/edit_new.tpl', NO_SKIN);
            $page->assign('survey_types', SurveyQuestion::getTypes());
            $page->assign('survey_type', $type);
        }
    }
    // }}}

    // {{{ function show_survey() : calls the template to display a survey, for editing, voting, or consulting the results
    function show_survey(&$page, $survey)
    {
        $page->changeTpl('survey/show_survey.tpl');
        $page->assign('survey_mode', 'edit'); // for now, only editing has been completely implemented
        $page->assign('survey', $survey->toArray());
    }
    // }}}

    // {{{ function show_form() : calls the template to display the editing form
    function show_form(&$page, $action, $qid, $type = 'new', $current = null)
    {
        $page->changeTpl('survey/edit_survey.tpl');
        $page->assign('survey_action', $action);
        $page->assign('survey_qid', $qid);
        $page->assign('survey_formaction', './survey/edit');
        $page->assign('survey_type', $type);
        if (!is_null($current) && is_array($current)) {
            $page->assign('survey_current', $current);
        } elseif ($type == 'new') {
            $page->addJsLink('ajax.js');
            $page->assign('survey_types', SurveyQuestion::getTypes());
        }
    }
    // }}}
    
    // {{{ function show_confirm() : calls the template to display a confirm form
    function show_confirm(&$page, $message, $formaction, $formhidden)
    {
        $page->changeTpl('survey/confirm.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_formaction', './survey/'.$formaction);
        $page->assign('survey_formhidden', $formhidden);
    }
    // }}}

    // {{{ function show_error() : calls the template to display an error message
    function show_error(&$page, $message, $link = "", $errArray = null)
    {
        $page->changeTpl('survey/error.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_link', './survey/'.$link); // 'return' link to let the user leave the page
        if (!is_null($errArray)) {
            $page->assign('survey_errors', $errArray);
        }
    }
    // }}}

    // {{{ function show_success() : calls the template to display a success message
    function show_success(&$page, $message = "", $link = "")
    {
        $page->changeTpl('survey/success.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_link', './survey/'.$link); // 'return' link to let the user leave the page
    }
    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

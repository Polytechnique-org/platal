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

class SurveyModule extends PLModule
{
    // {{{ function handlers() : registers the different handlers
    function handlers()
    {
        return array(
            'survey'             => $this->make_hook('index',         AUTH_PUBLIC),
            'survey/vote'        => $this->make_hook('vote',          AUTH_PUBLIC),
            'survey/result'      => $this->make_hook('result',        AUTH_PUBLIC),
            'survey/edit'        => $this->make_hook('edit',          AUTH_COOKIE, 'user'),
            'survey/ajax'        => $this->make_hook('ajax',          AUTH_COOKIE, 'user'),
            'survey/admin'       => $this->make_hook('admin',         AUTH_PASSWD, 'admin'),
            'survey/admin/edit'  => $this->make_hook('adminEdit',     AUTH_PASSWD, 'admin'),
            'survey/admin/valid' => $this->make_hook('adminValidate', AUTH_PASSWD, 'admin'),
            'survey/admin/del'   => $this->make_hook('adminDelete',   AUTH_PASSWD, 'admin'),
        );
    }
    // }}}

    // {{{ function handler_index() : lists all available surveys
    function handler_index($page, $action = null)
    {
        $this->load('survey.inc.php');
        $page->changeTpl('survey/index.tpl');
        $page->assign('survey_current', Survey::retrieveList('c'));
        $page->assign('survey_old', Survey::retrieveList('o'));
        $page->assign('survey_modes', Survey::getModes(false));
    }
    // }}}

    // {{{ function handler_vote() : handles the vote to a survey
    function handler_vote($page, $id = -1)
    {
        if (Post::has('survey_cancel')) { // if the user cancels, returns to index
            return $this->handler_index($page);
        }
        $id = intval($id);
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit être précisé.", 'survey');
        }
        $this->load('survey.inc.php');
        $survey = Survey::retrieveSurvey($id); // retrieves the survey object structure
        if ($survey == null || !$survey->isValid()) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'survey');
        } elseif ($survey->isEnded()) {
            return $this->show_error($page, "Le sondage ".$survey->getTitle()." est terminé.", 'survey');
        }
        if (!$this->check_surveyPerms($page, $survey)) {
            return PL_DO_AUTH;
        }
        if (Post::has('survey_submit')) { // checks if the survey has already been filled in
            // admins can see the survey but not vote
            if (!$this->check_surveyPerms($page, $survey, false, false)) {
                return PL_DO_AUTH;
            }
            $uid = 0;
            if (!$survey->isMode(Survey::MODE_ALL)) { // if survey is restriced to alumni
                $uid = S::v('uid');
                if ($survey->hasVoted($uid)) { // checks whether the user has already voted
                    return $this->show_error($page, "Tu as déjà voté à ce sondage.", 'survey');
                }
            }
            $survey->vote($uid, Post::v('survey'.$id)); // performs vote
            $this->show_success($page, "Ta réponse a bien été prise en compte. Merci d'avoir participé à ce sondage.", 'survey');
        } else { // offers to fill in the survey
            if ($survey->isMode(Survey::MODE_ALL) || !$survey->hasVoted(S::v('uid'))) {
                $page->assign('survey_votemode', true);
            } else {
                $page->assign('survey_warning', "Tu as déjà voté à ce sondage.");
            }
            //$page->assign('survey_id', $id);
            $this->show_survey($page, $survey);
        }
    }
    // }}}

    // {{{ function handler_result() : show the results of the votes to a survey
    function handler_result($page, $id = -1, $show = 'all')
    {
        $id = intval($id);
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit être précisé.", 'survey');
        }
        $this->load('survey.inc.php');
        $survey = Survey::retrieveSurvey($id); // retrieves the survey object structure
        if ($survey == null || !$survey->isValid()) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'survey');
        } elseif (!$survey->isEnded() && !$survey->canSeeEarlyResults(S::user())) {
            return $this->show_error($page, "Le sondage ".$survey->getTitle()." n'est pas encore terminé.", 'survey');
        }
        if (!$survey->canSeeEarlyResults(S::user()) && !$this->check_surveyPerms($page, $survey)) {
            return PL_DO_AUTH;
        }
        if ($show == 'csv') {
            pl_content_headers("text/csv");
            header('Content-Disposition: attachment; filename="'.addslashes($survey->getTitle()).'.csv"');
            echo $survey->toCSV();
            exit;
        } else {
            $page->assign('survey_resultmode', true);
            $this->show_survey($page, $survey);
        }
    }
    // }}}

    // {{{ function handler_admin() : index of admin mode
    function handler_admin($page, $id = -1)
    {
        $this->load('survey.inc.php');
        $this->clear_session();
        if ($id == -1) {
            $page->changeTpl('survey/admin.tpl');
            $page->assign('survey_waiting', Survey::retrieveList('w'));
            $page->assign('survey_current', Survey::retrieveList('c'));
            $page->assign('survey_old', Survey::retrieveList('o'));
            $page->assign('survey_modes', Survey::getModes(false));
        } else {
            $id = intval($id);
            $survey = Survey::retrieveSurvey($id); // retrieves all survey object structure
            if ($survey == null) {
                $this->show_error($page, "Sondage ".$id." introuvable.", 'survey/admin');
            }
            $page->assign('survey_adminmode', true);
            $this->show_survey($page, $survey);
        }
    }
    // }}}

    // {{{ function handler_adminEdit() : edits a survey in admin mode
    function handler_adminEdit($page, $id = -1, $req = -1)
    {
        if ($id == -1 || ($id == 'req' && $req == -1)) {
            return $this->show_error($page, "Un identifiant de sondage doit être précisé.", 'survey/admin');
        }
        $this->load('survey.inc.php');
        $this->clear_session(); // cleans session (in case there would have been a problem before)
        if ($id == 'req') {
            $survey = Survey::retrieveSurveyReq($req);
            if ($survey == null) {
                return $this->show_error($page, "Sondage introuvable.", 'survey/admin');
            }
            $this->store_session($survey, $req, true);
        } else {
            $id = intval($id);
            $survey = Survey::retrieveSurvey($id); // retrieves the survey in database
            if ($survey == null) {
                return $this->show_error($page, "Sondage ".$id." introuvable.", 'survey/admin');
            }
            $this->store_session($survey, $id);
        }
        $this->handler_edit($page, 'show'); // calls handler_edit, but in admin mode since 'survey_id' is in session
    }
    // }}}

    // {{{ function handler_adminValidate() : validates a survey (admin mode)
    function handler_adminValidate($page, $id = -1)
    {
        $id = Post::i('survey_id', $id);
        if (Post::has('survey_cancel')) { // if the admin cancels the validation, returns to the admin index
            $this->clear_session();
            return $this->handler_admin($page, $id);
        }
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit être précisé.", 'survey/admin');
        }
        $id = intval($id);
        $this->load('survey.inc.php');
        $surveyInfo = Survey::retrieveSurveyInfo($id); // retrieves information about the survey (does not retrieve and unserialize the object structure)
        if ($surveyInfo == null) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'survey/admin');
        }
        if (Post::has('survey_submit')) { // needs a confirmation before validation
            if (Survey::validateSurvey($id)) { // validates the survey (in the database)
                $this->show_success($page, "Le sondage \"".$surveyInfo['title']."\" a bien été validé, les votes sont maintenant ouverts.", 'survey/admin');
            } else {
                $this->show_error($page, '', 'survey/admin');
            }
        } else { // asks for a confirmation
            $this->show_confirm($page, "Êtes-vous certain de vouloir valider le sondage \"".$surveyInfo['title']."\" ? "
                                      ."Les votes seront immédiatement ouverts.", 'admin/valid', array('id' => $id));
        }
    }
    // }}}

    // {{{ function handler_adminDelete() : deletes a survey (admin mode)
    function handler_adminDelete($page, $id = -1)
    {
        $id = Post::i('survey_id', $id);
        if (Post::has('survey_cancel')) { // if the admin cancels the suppression, returns to the admin index
            return $this->handler_admin($page, $id);
        }
        if ($id == -1) {
            return $this->show_error($page, "Un identifiant de sondage doit être précisé.", 'survey/admin');
        }
        $id = intval($id);
        $this->load('survey.inc.php');
        $surveyInfo = Survey::retrieveSurveyInfo($id); // retrieves information about the survey (does not retrieve and unserialize the object structure)
        if ($surveyInfo == null) {
            return $this->show_error($page, "Sondage ".$id." introuvable.", 'survey/admin');
        }
        if (Post::has('survey_submit')) { // needs a confirmation before suppression
            if (Survey::deleteSurvey($id)) { // deletes survey in database
                $this->show_success($page, "Le sondage \"".$surveyInfo['title']."\" a bien été supprimé, ainsi que tous les votes le concernant.", 'survey/admin');
            } else {
                $this->show_error($page, '', 'survey/admin');
            }
        } else { // asks for a confirmation
            $this->show_confirm($page, "Êtes-vous certain de vouloir supprimer le sondage \"".$surveyInfo['title']."\" ?", 'admin/del', array('id' => $id));
        }
    }
    // }}}

    // {{{ function handler_edit() : edits a survey (in normal mode unless called by handler_adminEdit() )
    function handler_edit($page, $action = 'show', $qid = 'root')
    {
        $this->load('survey.inc.php');
        $action = Post::v('survey_action', $action);
        $qid    = Post::v('survey_qid', $qid);
        if (Post::has('survey_cancel')) { // after cancelling changes, shows the survey
            if (S::has('survey')) {
                $action = 'show';
            } else {                      // unless no editing has been done at all (shows to the surveys index page)
                return $this->handler_index($page);
            }
        }
        $page->assign('survey_editmode', true);
        if (S::has('survey_id')) { // if 'survey_id' is in session, it means we are modifying a survey in admin mode
            $page->assign('survey_updatemode', true);
        }
        if ($action == 'show' && !S::has('survey')) {
            $action = 'new';
        }
        if ($action == 'question') { // {{{ modifies an existing question
            if (Post::has('survey_submit')) { // if the form has been submitted, makes the modifications
                $survey = unserialize(S::v('survey'));
                $args   = Post::v('survey_question');
                if (!$survey->editQuestion($qid, $args)) { // update the survey object structure
                    return $this->show_error($page, '', 'survey/edit');
                }
                $this->show_survey($page, $survey);
                $this->store_session($survey);
            } else { // if a form has not been submitted, shows modification form
                $survey = unserialize(S::v('survey'));
                $current = $survey->toArray($qid); // gets the current parameters of the question
                if ($current == null) {
                    return $this->show_error($page, '', 'survey/edit');
                }
                $this->show_form($page, $action, $qid, $current['type'], $current);
            } // }}}
        } elseif ($action == 'new') { // {{{ create a new survey : actually store the root question
            if (Post::has('survey_submit')) { // if the form has been submitted, creates the survey
                $this->clear_session();
                $survey = new Survey(Post::v('survey_question')); // creates the object structure
                $this->show_survey($page, $survey);
                $this->store_session($survey);
            } else {
                $this->clear_session();
                $this->show_form($page, $action, 'root', 'newsurvey');
            } // }}}
        } elseif ($action == 'add') { // {{{ adds a new question
            if (Post::has('survey_submit')) { // if the form has been submitted, adds the question
                $survey = unserialize(S::v('survey'));
                if (!$survey->addQuestion($qid, $survey->factory(Post::v('survey_type'), Post::v('survey_question')))) {
                    return $this->show_error($page, '', 'survey/edit');
                }
                $this->show_survey($page, $survey);
                $this->store_session($survey);
            } else {
                $this->show_form($page, $action, $qid);
            } // }}}
        } elseif ($action == 'del') { // {{{ deletes a question
            if (Post::has('survey_submit')) { // if a confirmation has been sent, deletes the question
                $survey = unserialize(S::v('survey'));
                if (!$survey->delQuestion(Post::v('survey_qid'))) { // deletes the node in the survey object structure
                    return $this->show_error($page, '', 'survey/edit');
                }
                $this->show_survey($page, $survey);
                $this->store_session($survey);
            } else { // if user has not confirmed, shows a confirmation form
                $survey = unserialize(S::v('survey'));
                $current = $survey->toArray($qid); // needed to get the title of the question to delete (more user-friendly than an id)
                if ($current == null) {
                    return $this->show_error($page, '', 'survey/edit');
                }
                $this->show_confirm($page, 'Êtes-vous certain de vouloir supprimer la question intitulé "'.$current['question'].'" ? '
                                          .'Attention, cela supprimera en même temps toutes les questions qui dépendent de celle-ci.',
                                                'edit', array('action' => 'del', 'qid' => $qid));
            } // }}}
        } elseif ($action == 'show') { // {{{ simply shows the survey in its current state
            $this->show_survey($page, unserialize(S::v('survey'))); // }}}
        } elseif ($action == 'valid') { // {{{ validates the proposition, i.e stores the proposition in the database
                                        // but an admin will still need to validate the survey before it is activated
            if (Post::has('survey_submit')) { // needs a confirmation before storing the proposition
                $survey = unserialize(S::v('survey'));
                if (S::has('survey_id')) { // if 'survey_id' is in session, we are modifying an existing survey (in admin mode) instead of proposing a new one
                    $link = (S::has('survey_validate'))? 'admin/validate' : 'survey/admin';
                    if ($survey->updateSurvey()) { // updates the database according the new survey object structure
                        $this->show_success($page, "Les modifications sur le sondage ont bien été enregistrées.", $link);
                    } else {
                        $this->show_error($page, '', $link);
                    }
                } else { // if no 'survey_id' is in session, we are indeed proposing a new survey
                    if ($survey->proposeSurvey()) { // stores the survey object structure in database
                        $this->show_success($page, "Votre proposition de sondage a bien été enregistrée,
                                                    elle est en attente de validation par un administrateur du site.", 'survey');
                    } else {
                        $this->show_error($page, '', 'survey');
                    }
                }
                $this->clear_session();
            } else { // asks for a confirmation if it has not been sent
                $survey = unserialize(S::v('survey'));
                $errors = $survey->checkSyntax();
                if (!is_null($errors)) {
                    $this->show_error($page, "", 'survey/edit', $errors);
                } else {
                    if (S::has('survey_id')) {
                        $this->show_confirm($page, "Veuillez confirmer l'enregistrement des modifications apportées à ce sondage.", 'edit', array('action' => 'valid'));
                    } else {
                        $this->show_confirm($page, "Veuillez confirmer l'envoi de cette proposition de sondage.", 'edit', array('action' => 'valid'));
                    }
                }
            } // }}}
        } elseif ($action == 'cancel') { // {{{ cancels the creation/modification of a survey
            if (Post::has('survey_submit')) { // needs a confirmation
                if (S::has('survey_id')) {  // only possible when modifying a survey in admin mode
                    if (S::has('survey_validate')) { // if a link has been supplied, uses it
                        $this->clear_session();
                        return $this->show_success($page, "Les modifications effectuées ont été annulées", 'admin/validate');
                    } else { // else shows the admin index
                        $this->clear_session();
                        return $this->handler_admin($page);
                    }
                } else {
                    $this->clear_session();
                    return $this->handler_index($page); // else shows the 'normal' index
                }
            } else { // asks for a confirmation if it has not been sent
                $this->show_confirm($page, "Êtes-vous certain de vouloir annuler totalement l'édition de ce sondage ? Attention, "
                                           ."toutes les données éditées jusque là seront définitivement perdues.",
                                                'edit', array('action' => $action));
            }
        } // }}}
    }
    // }}}

    // {{{ function handler_ajax() : some ajax in editing a new question (for now, there may be a little more later)
    function handler_ajax($page, $type)
    {
        $this->load('survey.inc.php');
        pl_content_headers("text/html");
        if (Survey::isType($type)) { // when type has been chosen, the form is updated to fit exactly the type of question chosen
            $page->changeTpl('survey/edit_new.tpl', NO_SKIN);
            $page->assign('survey_types', Survey::getTypes());
            $page->assign('survey_type', $type);
        }
    }
    // }}}

    // {{{ function clear_session() : clears the data stored in session
    function clear_session()
    {
        S::kill('survey');
        S::kill('survey_id');
        S::kill('survey_validate');
    }
    // }}}

    // {{{ function store_session() : serializes and stores survey (and survey_id) in session
    function store_session($survey, $survey_id = -1, $survey_validate = false)
    {
        $_SESSION['survey'] = serialize($survey);
        if ($survey_id != -1) {
            $_SESSION['survey_id'] = $survey_id;
        }
        if ($survey_validate) {
            $_SESSION['survey_validate'] = true;
        }
    }
    // }}}

    // {{{ function check_surveyPerms() : checks the particular surveys access permissions
    function check_surveyPerms($page, $survey, $silent = false, $admin_allowed = true)
    {
        $this->load('survey.inc.php');
        if ($survey->isMode(Survey::MODE_ALL)) { // if the survey is not reserved to alumni
            return true;
        }
        if (!S::logged()) {
            return false;
        }
        $profile = S::user()->profile();
        if (!$profile) {
            return false;
        }
        // checks promotion
        $allowed = false;
        foreach ($profile->yearspromo() as $p) {
            if ($survey->checkPromo($p)) {
                $allowed = true;
                break;
            }
        }
        if ($allowed) {
            return true;
        }
        if (S::admin() && $admin_allowed) {
            if (!$silent) {
                $page->trigWarning('Tu as accès à ce sondage car tu es administrateur du site.');
            }
            return true;
        }
        if (!$silent) {
            $page->kill("Tu n'as pas accès à ce sondage car il est réservé à d'autres promotions.");
        }
        return false;
    }
    // }}}

    // {{{ function show_survey() : calls the template to display a survey, for editing, voting, or consulting the results
    function show_survey($page, $survey)
    {
        $page->changeTpl('survey/show_root.tpl');
        $page->assign('survey', $survey->toArray());
        $page->assign('survey_modes', Survey::getModes());
    }
    // }}}

    // {{{ function show_form() : calls the template to display the editing form
    function show_form($page, $action, $qid, $type = 'new', $current = null)
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
            $page->assign('survey_types', Survey::getTypes());
        }
        if ($type == 'root' || $type == 'newsurvey') {
            $page->assign('survey_modes', Survey::getModes());
        }
    }
    // }}}

    // {{{ function show_confirm() : calls the template to display a confirm form
    function show_confirm($page, $message, $formaction, $formhidden = null)
    {
        $page->changeTpl('survey/confirm.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_formaction', './survey/'.$formaction);
        $page->assign('survey_formhidden', $formhidden);
    }
    // }}}

    // {{{ function show_error() : calls the template to display an error message
    function show_error($page, $message, $link = "", $errArray = null)
    {
        $page->changeTpl('survey/error.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_link', $link); // 'return' link to let the user leave the page
        if (!is_null($errArray)) {
            $page->assign('survey_errors', $errArray);
        }

    }
    // }}}

    // {{{ function show_success() : calls the template to display a success message
    function show_success($page, $message = "", $link = "")
    {
        $page->changeTpl('survey/success.tpl');
        $page->assign('survey_message', $message);
        $page->assign('survey_link', $link); // 'return' link to let the user leave the page
    }
    // }}}
}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker fenc=utf-8:
?>

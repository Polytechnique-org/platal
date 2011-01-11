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

class SurveyModule extends PLModule
{
    function handlers()
    {
        return array(
            'survey'              => $this->make_hook('index',         AUTH_COOKIE),
            'survey/vote'         => $this->make_hook('vote',          AUTH_COOKIE),
            'survey/edit'         => $this->make_hook('edit',          AUTH_COOKIE),
            /*
            'survey/result'       => $this->make_hook('result',        AUTH_COOKIE),
            'survey/ajax'         => $this->make_hook('ajax',          AUTH_COOKIE),
            'survey/admin'        => $this->make_hook('admin',         AUTH_MDP, 'admin'),
            'survey/admin/edit'   => $this->make_hook('adminEdit',     AUTH_MDP, 'admin'),
            'survey/admin/valid'  => $this->make_hook('adminValidate', AUTH_MDP, 'admin'),
            'survey/admin/del'    => $this->make_hook('adminDelete',   AUTH_MDP, 'admin'),
      */  );
    }

    private function setup_page(PlPage $page)
    {
        $this->load('survey.inc.php');
        $page->addJsLink('jquery.ui.core.js');
        $page->addJsLink('jquery.ui.widget.js');
        $page->addJsLink('jquery.ui.datepicker.js');
        $page->addJsLink('jquery.ui.datepicker-fr.js');
        $page->addJsLink('jquery.tmpl.js');
        $page->addJsLink('survey.js');
    }

    function handler_index(&$page, $action = null)
    {
        $this->setup_page($page);

        $page->changeTpl('survey/index.tpl');
        $page->assign('active', Survey::iterActive());
    }

    function handler_vote(PlPage $page, $name)
    {
        $this->setup_page($page);
        $page->changeTpl('survey/vote.tpl');

        $survey = Survey::get($name);
        if (is_null($survey)) {
            return PL_NOT_FOUND;
        }
        if (!$survey->canSee(S::user())) {
            return PL_FORBIDDEN;
        }
        if (Post::has('vote')) {
            $answers = Post::v('qid');
            $vote = $survey->vote(S::user(), $answers);
            if (is_null($vote)) {
                $page->kill("Tu n'as pas le droit de voter à ce sondage.");
            } else if ($vote->inError()) {
                $page->trigError("Certaines réponses sont invalides et doivent être corrigées");
            } else {
                $vote->insert(true);
                $page->trigSuccess("Ton vote a été enregistré");
            }
        }
        $page->assign('survey', $survey);
    }

    function handler_edit(PlPage $page, $name = null)
    {
        $this->setup_page($page);
        $page->changeTpl('survey/edit.tpl');

        if (!is_null($name)) {
            $survey = Survey::get($name);
        } else {
            $survey = new Survey();
            $survey->id = null;
            $survey->uid = S::user()->id();
        }
        if (Post::has('valid')) {
            $survey->title = Post::t('title');
            $survey->shortname = Post::t('shortname');
            $survey->description = Post::t('description');
            $survey->begin     = Post::t('begin');
            $survey->end       = Post::t('end');
            $survey->flags     = 'validated';
            $survey->flags->addFlag('anonymous', Post::b('anonymous'));

            $q_edit = Post::v('q_edit');
            $qs = array();
            foreach ($q_edit as $qid => $q_desc) {
                if (isset($q_desc['parent'])) {
                    $parent = $qs[$q_desc['parent']];
                } else {
                    $parent = $survey;
                }
                $question = $parent->newQuestion($q_desc['type']);
                $question->label = $q_desc['label'];
                unset($q_desc['type']);
                unset($q_desc['parent']);
                unset($q_desc['label']);
                $question->parameters = $q_desc;
                $qs[$qid] = $question;
            }
            $survey->insert('true');
        }
        $page->assign('survey', $survey);
    }
}

// vim:set et sw=4 sts=4 ts=4 foldmethod=marker enc=utf-8:
?>

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

class ProfileSkill implements ProfileSetting
{
    private $table;
    private $skill_field;
    private $text_field;

    public function __construct($table, $skill, $text)
    {
        $this->table = $table;
        $this->skill_field = $skill;
        $this->text_field = $text;
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterRow("SELECT  s.id, s.{$this->text_field}, i.level
                                   FROM  {$this->table}_def AS s
                             INNER JOIN  {$this->table}_ins AS i ON(s.id = i.{$this->skill_field})
                                  WHERE  i.uid = {?}",
                                S::i('uid'));
            while (list($sid, $text, $level) = $res->next()) {
                $value[$sid] = array('text' => $text, 'level' => $level);
            }
        }
        if (!is_array($value)) {
            $value = array();
        } else {
            foreach ($value as $id=>&$skill) {
                if (!isset($skill['text']) || empty($skill['text'])) {
                    $res = XDB::query("SELECT  {$this->text_field}
                                         FROM  {$this->table}_def
                                        WHERE  id = {?}", $id);
                    $skill['text'] = $res->fetchOneCell();
                }
            }
        }
        ksort($value);
        $success = true;
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM  {$this->table}_ins
                            WHERE  uid = {?}",
                     S::i('uid'));
        if (!count($value)) {
            return;
        }
        foreach ($value as $id=>&$skill) {
            XDB::execute("INSERT INTO  {$this->table}_ins (uid, {$this->skill_field}, level)
                               VALUES  ({?}, {?}, {?})",
                         S::i('uid'), $id, $skill['level']);
        }
    }
}

class ProfileSkills extends ProfilePage
{
    protected $pg_template = 'profile/skill.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['competences'] = new ProfileSkill('competences', 'cid', 'text_fr');
        $this->settings['langues'] = new ProfileSkill('langues', 'lid', 'langue_fr');
    }

    public function prepare(PlatalPage &$page)
    {
        parent::prepare($page);
        $page->assign('comp_list', XDB::iterator("SELECT  id, text_fr, FIND_IN_SET('titre',flags) AS title
                                                    FROM  competences_def"));
        $page->assign('comp_level', array('initié' => 'initié',
                                          'bonne connaissance' => 'bonne connaissance',
                                          'expert' => 'expert'));
        $page->assign('lang_list', XDB::iterator("SELECT  id, langue_fr
                                                    FROM  langues_def"));
        $page->assign('lang_level', array(1 => 'connaissance basique',
                                          2 => 'maîtrise des bases',
                                          3 => 'maîtrise limitée',
                                          4 => 'maîtrise générale',
                                          5 => 'bonne maîtrise',
                                          6 => 'maîtrise complète'));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * VersionnedResource configuration form
 *
 * @package    mod_versionnedresource
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_versionnedresource_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor();

        $mform->addElement('text', 'docurl', get_string('docurl', 'versionnedresource'), array('size' => '80', 'maxlength' => 255));
        $mform->setType('docurl', PARAM_URL);

        $mform->addElement('text', 'giturl', get_string('giturl', 'versionnedresource'), array('size' => '80', 'maxlength' => 255));
        $mform->setType('giturl', PARAM_URL);

        //-------------------------------------------------------
        $mform->addElement('header', 'versionhdr', get_string('versiondata', 'versionnedresource'));

        $mform->addElement('textarea', 'branches', get_string('branches', 'versionnedresource'), array('cols' => '80', 'rows' => 10));

        $mform->addElement('textarea', 'maturities', get_string('maturities', 'versionnedresource'), array('cols' => '80', 'rows' => 10));

        $mform->addElement('textarea', 'extracss', get_string('extracss', 'versionnedresource'), array('cols' => '80', 'rows' => 10));

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        return $errors;
    }
}

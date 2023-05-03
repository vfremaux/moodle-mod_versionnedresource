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
 * upgrade processes for this module.
 *
 * @package     mod_versionresource
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

class version_form extends moodleform {

    protected $fileoptions;

    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $maxbytes = $COURSE->maxbytes; // TODO: add some setting.
        $context = context_course::instance($COURSE->id);
        $this->fileoptions = array('subdirs' => false, 'maxfiles' => 1, 'maxbytes' => $maxbytes);

        $mform->addElement('hidden', 'vid');
        $mform->setType('vid', PARAM_INT);

        $mform->addElement('hidden', 'versionnedresourceid');
        $mform->setType('versionnedresourceid', PARAM_INT);

        if (!$this->_customdata['vid'] || has_capability('mod/versionnedresource:updateartifact', $context)) {
            $mform->addElement('filepicker', 'versionfile', get_string('artifact', 'versionnedresource'), $this->fileoptions);
        } else {
            $label = get_string('artifact', 'versionnedresource');
            $desc = get_string('artifactlocked', 'versionnedresource');
            $mform->addElement('static', 'versionfileadvice', $label, $desc);
        }

        if (empty($this->_customdata['branches'])) {
            $mform->addElement('text', 'branch', get_string('branch', 'versionnedresource'));
            $mform->setType('branch', PARAM_TEXT);
        } else {
            $options = $this->_customdata['branches'];
            $mform->addElement('select', 'branch', get_string('branch', 'versionnedresource'), $options);
        }

        $mform->addElement('text', 'version', get_string('version', 'versionnedresource'));
        $mform->setType('version', PARAM_TEXT);

        if (empty($this->_customdata['maturities'])) {
            $mform->addElement('text', 'maturity', get_string('maturity', 'versionnedresource'));
            $mform->setType('maturity', PARAM_TEXT);
        } else {
            $options = $this->_customdata['maturities'];
            $mform->addElement('select', 'maturity', get_string('maturity', 'versionnedresource'), $options);
        }

        $mform->addElement('checkbox', 'visible', get_string('visible', 'versionnedresource'));
        $mform->setDefault('visible', 1);

        $mform->addElement('textarea', 'changes', get_string('changes', 'versionnedresource'), array('cols' => '80', 'rows' => 10));

        if (!empty($this->_customdata['instance']->hasdoc)) {
            $mform->addElement('text', 'docurl', get_string('docurl', 'versionnedresource'));
            $mform->setType('docurl', PARAM_URL);
        }

        if (!empty($this->_customdata['instance']->hascvs)) {
            $mform->addElement('text', 'giturl', get_string('giturl', 'versionnedresource'));
            $mform->setType('giturl', PARAM_URL);
        }

        $label = get_string('minorrelease', 'versionnedresource');
        $desc = get_string('minorrelease_desc', 'versionnedresource');
        $mform->addElement('checkbox', 'minorrelease', $label, $desc);

        $this->add_action_buttons();
    }

}


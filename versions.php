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
 * @package     mod_versionnedresource
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/versionnedresource/locallib.php');
require_once($CFG->dirroot.'/mod/versionnedresource/forms/version_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$vr = optional_param('vr', 0, PARAM_INT);  // VersionnedResource instance ID.
$vid = optional_param('vid', 0, PARAM_INT);

if ($vr) {
    if (!$vresource = $DB->get_record('versionnedresource', array('id' => $vr))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('versionnedresource', $vresource->id, $vresource->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('versionnedresource', $id)) {
        print_error('invalidcoursemodule');
    }
    $vresource = $DB->get_record('versionnedresource', array('id' => $cm->instance), '*', MUST_EXIST);
}
$course = $DB->get_record('course', array('id' => $vresource->course));

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/versionnedresource:manageversions', $context);

$instance = new versionned_resource($vresource);

$url = new moodle_url('/mod/versionnedresource/versions.php', array('vr' => $vr));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('editversion', 'versionnedresource'));
$PAGE->set_heading($course->fullname);

$params = array('instance' => $instance);
if (!empty($vresource->branches)) {
    $items = explode("\n", $vresource->branches);
    $params['branches'] = array_combine($items, $items);
}

if (!empty($vresource->maturities)) {
    $items = explode("\n", $vresource->maturities);
    $params['maturities'] = array_combine($items, $items);
}

$params['vid'] = $vid;

$form = new version_form($url, $params);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/versionnedresource/view.php', array('id' => $cm->id)));
}

if ($data = $form->get_data()) {

    // Why do we loose those ?
    $data->branch = optional_param('branch', '', PARAM_TEXT);
    $data->maturity = optional_param('maturity', '', PARAM_TEXT);

    if (!$data->vid) {
        $vid = $instance->add_version($data);

        $draftid = file_get_submitted_draft_itemid('versionfile');
        file_save_draft_area_files($draftid, $context->id, 'mod_versionnedresource',
            'artifact', $vid, array('subdirs' => true));
    } else {
        $vid = $instance->update_version($data);
    }


    $params = array('id' => $cm->id);
    redirect(new moodle_url('/mod/versionnedresource/view.php', $params));
}

echo $OUTPUT->header();

if ($vid) {
    echo $OUTPUT->heading(get_string('editversion', 'versionnedresource'));
} else {
    echo $OUTPUT->heading(get_string('addversion', 'versionnedresource'));
}

if ($vid) {
    $data = $DB->get_record('versionnedresource_version', array('id' => $vid));
    $data->vid = $data->id;
    $data->id = $cm->id;
    $form->set_data($data);
}
$form->display();

echo $OUTPUT->footer();
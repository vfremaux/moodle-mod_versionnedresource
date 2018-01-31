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

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$vr = optional_param('vr', 0, PARAM_INT);  // VersionnedResource instance ID.
$action = optional_param('what', '', PARAM_ALPHA);

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

$instance = new versionned_resource($vresource);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/versionnedresource:view', $context);

if ($action) {
    include_once($CFG->dirroot.'/mod/versionnedresource/view.controller.php');
    $controller = new \mod_versionnedresource\view_controller($instance);
    $controller->receive($action);
    $controller->process($action);
}

$url = new moodle_url('/mod/versionnedresource/view.php', array('id' => $cm->id));

$renderer = $PAGE->get_renderer('versionnedresource');
$renderer->set_instance($instance);
$renderer->set_context($context);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_heading(get_string('pluginname', 'versionnedresource'));

echo $OUTPUT->header();

$versions = $instance->get_versions();

echo $renderer->header($vresource);
echo $renderer->versions($versions, $context);

if (has_capability('mod/versionnedresource:manageversions', $context)) {

    if ($instance->extracss) {
        echo '<style>'.$instance->extracss.'</style>';
    }

    echo $renderer->add_version();
}

echo $OUTPUT->footer();




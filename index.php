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
 * List of all versionnedresources in course
 *
 * @package    mod_versionnedresource
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // Course id.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_resource\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strresource     = get_string('modulename', 'versionnedresource');
$strresources    = get_string('modulenameplural', 'versionnedresource');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/versionnedresource/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strresources);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strresources);
echo $OUTPUT->header();
echo $OUTPUT->heading($strresources);

if (!$resources = get_all_instances_in_course('versionnedresource', $course)) {
    $returnurl = new moodle_url('/course/view.php', array('id' => $course->id));
    echo $OUTPUT->notification(get_string('thereareno', 'moodle', $strresources), $returnurl);
    echo $OUTPUT->footer();
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($resources as $resource) {
    $cm = $modinfo->cms[$resource->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($resource->section !== $currentsection) {
            if ($resource->section) {
                $printsection = get_section_name($course, $resource->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $resource->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($resource->timemodified)."</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // Each resource file has an icon in 2.0.
        $attrs = array('class' => 'activityicon');
        $icon = $OUTPUT->pix_icon($cm->icon, get_string('modulename', $cm->modname), 'mod_'.$cm->modname, $attrs);
    }

    $class = $resource->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
    $viewurl = new moodle_url('/mod/versionnedresource/view.php', array('id' => $cm->id));
    $table->data[] = array (
        $printsection,
        '<a $class '.$extra.' href="'.$viewurl.'">'.$icon.format_string($resource->name).'</a>',
        format_module_intro('versionnedresource', $resource, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();

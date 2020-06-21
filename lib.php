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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/versionnedresource/locallib.php');

/**
 *
 */
function mod_versionnedresource_supports_feature($feature) {
    return versionned_resource::supports_feature($feature);
}

/**
 * List of features supported in Page module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function versionnedresource_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * Add versionnedresource instance.
 * @param stdClass $data
 * @param mod_page_mod_form $mform
 * @return int new page instance id
 */
function versionnedresource_add_instance($data, $mform = null) {
    global $CFG, $DB;

    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $displayoptions = array();

    $data->id = $DB->insert_record('versionnedresource', $data);

    return $data->id;
}

/**
 * Update page instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function versionnedresource_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('versionnedresource', $data);

    return true;
}

/**
 * Delete versionnedresource instance.
 * @param int $id
 * @return bool true
 */
function versionnedresource_delete_instance($id) {
    global $DB, $COURSE;

    if (!$vr = $DB->get_record('versionnedresource', array('id' => $id))) {
        return false;
    }

    // Note: all context files are deleted automatically.

    $DB->delete_records('versionnedresource_version', array('versionnedresourceid' => $vr->id));
    $DB->delete_records('versionnedresource_user_subs', array('versionnedresourceid' => $vr->id));
    $DB->delete_records('versionnedresource', array('id' => $vr->id));

    return true;
}

/**
 * Serves the resource files.
 *
 * @package  mod_versionnedresource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function versionnedresource_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/versionnedresource:download', $context)) {
        return false;
    }

    if ($filearea !== 'artifact') {
        // Intro is handled automatically in pluginfile.php.
        return false;
    }

    $itemid = array_shift($args);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);

    $fullpath = "/$context->id/mod_versionnedresource/$filearea/$itemid/$relativepath";
    if ((!$file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Increment download counts.
    $resourceversion = $DB->get_record('versionnedresource_version', array('id' => $itemid), '*', MUST_EXIST);
    $resourceversion->downloads++;
    $DB->update_record('versionnedresource_version', $resourceversion);

    // Finally send the file.
    send_stored_file($file, null, $filter, $forcedownload);
}

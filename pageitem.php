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
 * This script implements a pageitem content builder for feeding
 * a page_module actvity wrapper.
 *
 * @package   mod_versionnedresource
 * @copyright 2014 Valery Fremaux (valery.Fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/versionnedresource/locallib.php');

/**
 * Implements a hook for the page_module block to add
 * the link allowing live refreshing of the content. this method is
 * specifically used for page formatted courses.
 * @param object $block a page_module block surrounding the customlabel resource.
 */
function versionnedresource_set_instance(&$block) {
    global $USER, $CFG, $COURSE, $DB;

    // Transfer content from title to content.
    $block->title = '';

    // Fake unpacks object's load.
    $vresource = $DB->get_record('versionnedresource', array('id' => $block->cm->instance));

    $context = context_module::instance($block->cm->id);

    $completioninfo = new completion_info($COURSE);
    $modinfo = get_fast_modinfo($COURSE);
    $mod = $modinfo->cms[$block->cm->id];
    $sectionreturn = $block->cm->section;
    $formatrenderer = $PAGE->get_renderer('format_page');
    $str .= $formatrenderer->print_cm($COURSE, $mod);

    $instance = new versionned_resource($vresource);
    $versions = $instance->get_versions();
    $renderer = $PAGE->get_renderer('versionnedresourcee');

    $str .= $renderer->header($vresource);
    $str .= $renderer->versions($versions, $context);

    // Do NOT format text here!
    $block->content->text = $str;

    return true;
}


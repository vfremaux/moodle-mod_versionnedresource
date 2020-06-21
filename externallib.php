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

require_once($CFG->dirroot.'/lib/externallib.php');
require_once($CFG->dirroot.'/mod/versionnedresource/locallib.php');

class mod_versionnedresource_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_version_info_parameters() {
        $desc = 'Source for versionresource identifier, can be \'id\' or \'idnumber\'';
        return new external_function_parameters(
            array(
                'vridsource'  => new external_value(PARAM_ALPHA, $desc),
                'vrid'  => new external_value(PARAM_TEXT, 'Version resource id')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param string $vridsource
     * @param string $vrid
     * @return array of course objects and warnings
     * @throws moodle_exception
     */
    public static function get_version_info($vridsource, $vrid) {
        global $CFG, $DB;

        $warnings = array();

        $parameters = array(
            'vridsource'  => $vridsource,
            'vrid'  => $vrid,
        );
        $params = self::validate_parameters(self::get_version_info_parameters(), $parameters);

        // Explicit mapping avoids injection.
        switch ($vridsource) {
            case 'idnumber':
                $vrid = self::get_vr_from_idnumber($vrid);
                break;
            default:
                break;
        }

        $vr = $DB->get_record('versionnedresource', array('id' => $vrid));
        $params = array('versionnedresourceid' => $vr->id, 'visible' => 1);
        $versions = $DB->get_records('versionnedresource_version', $params, 'branch DESC, version ASC');

        $exposed = array();
        if (!empty($versions)) {
            foreach ($versions as $v) {
                unset($v->versionresourceid);
                unset($v->visible);
                unset($v->file); // File may not be kept in future.
                $v->datemodified = date('Y-m-d', $v->timemodified);
                $exposed[] = $v;
            }
        }

        return array(
            'name' => $vr->name,
            'description' => $vr->intro,
            'versions' => $exposed
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_version_info_returns() {

        return new external_single_structure(
            array(
                'name' => new external_value(PARAM_TEXT, 'Resource local name'),
                'description' => new external_value(PARAM_TEXT, 'Resource local description'),
                'versions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Version Record id'),
                            'branch' => new external_value(PARAM_TEXT, 'Branch'),
                            'version' => new external_value(PARAM_TEXT, 'Version'),
                            'maturity' => new external_value(PARAM_INT, 'Maturity indicator'),
                            'changes' => new external_value(PARAM_TEXT, 'Change log'),
                            'giturl' => new external_value(PARAM_URL, 'Url to git repository'),
                            'docurl' => new external_value(PARAM_URL, 'Url to documentation'),
                            'timemodified' => new external_value(PARAM_INT, 'Modification stamp'),
                            'datemodified' => new external_value(PARAM_TEXT, 'Modification date'),
                            'downloads' => new external_value(PARAM_INT, 'Downloads count'),
                        )
                    )
                )
            )
        );
    }

    // Get version file url ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_version_file_url_parameters() {
        return new external_function_parameters(
            array(
                'versionid' => new external_value(PARAM_TEXT, 'Version id')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param int $versionid
     * @return array of course objects and warnings
     * @throws moodle_exception
     */
    public static function get_version_file_url($versionid) {
        global $DB, $CFG;

        $parameters = array(
            'versionid'  => $versionid,
        );
        $params = self::validate_parameters(self::get_version_file_url_parameters(), $parameters);

        if (!$version = $DB->get_record('versionnedresouce_version', array('id' => $versionid))) {
            throw new moodle_exception('missingversion');
        }

        $cm = get_coursemodule_from_instance('versionnedresource', $version->versionnedresourceid);
        $context = context_module::instance($cm->id);
        $urlbase = "$CFG->httpswwwroot/webservice/pluginfile.php";
        $context = context_user::instance($USER->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_versionnedresource', 'artifact', $version->id, 'itemid, filepath', false);
        if ($files) {
            // Should be only one.
            $file = array_pop($files);

            $filepath = $file->get_filepath();
            $filename = $file->get_filename();
            $path = "/{$context->id}/mod_versionnedresource/artifact/{$version->id}";
            return self::make_file_url($urlbase, $path.$filepath.$filename, true);
        }

        throw new moodle_exception('missingfile');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_version_file_url_returns() {
        return new external_value(PARAM_URL, 'An url to the file');
    }

    // Get last branch file url ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_last_branch_file_url_parameters() {
        return new external_function_parameters(
            array(
                'vridsource' => new external_value(PARAM_ALPHA, 'source for the id, can be either \'id\' or \'idnumber\''),
                'vrid' => new external_value(PARAM_TEXT, 'Resource id'),
                'branch' => new external_value(PARAM_TEXT, 'Branch tag')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param string $vridsource
     * @param string $vrid
     * @return array of course objects and warnings
     * @throws moodle_exception
     */
    public static function get_last_branch_file_url($vridsource, $vrid, $branch) {
        global $DB;

        $parameters = array(
            'vridsource'  => $vridsource,
            'vrid'  => $vrid,
            'branch'  => $branch,
        );
        $params = self::validate_parameters(self::get_last_branch_file_url_parameters(), $parameters);

        // Explicit mapping avoids injection.
        switch ($vridsource) {
            case 'idnumber':
                $vrid = self::get_vr_from_idnumber($vrid);
                break;
            default:
                break;
        }

        $vr = $DB->get_record('versionnedresource', array('id' => $vrid));

        // Search candidate version and get first one

        $params = array('versionnedresourceid' => $vr->id, 'branch' => $branch);
        $candidates = $DB->get_records('versionnedresource_version', $params, 'version DESC', '*', 0, 1);
        if ($candidates) {
            $candidate = array_pop($candidates);

            return self::get_version_file_url($candidate->id);
        }

        throw new moodle_exception('nocandidate');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_last_branch_file_url_returns() {
        return new external_value(PARAM_URL, 'An url to the file');
    }

    // Get last branch file url ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function commit_version_parameters() {
        $deschide = 'Hide control, can be \'no\', \'branch\' or \'module\'';
        return new external_function_parameters(
            array(
                'vridsource' => new external_value(PARAM_ALPHA, 'source for the id, can be either \'id\' or \'idnumber\''),
                'vrid' => new external_value(PARAM_TEXT, 'Resource id'),
                'draftitemid' => new external_value(PARAM_INT, 'Waiting draftitem'),
                'jsoninfo' => new external_value(PARAM_TEXT, 'Json serialized info for the version record'),
                'hideprevious' => new external_value(PARAM_TEXT, $deschide, VALUE_DEFAULT, 'no', true)
            )
        );
    }

    /**
     * Commits the version that has ben previously uploaded using the webservice/upload.php facility.
     *
     * @param string $vridsource the source field for the resource identifier.
     * @param string $vrid the versionnedresource id
     * @param int $draftitemid the temporary draft id of the uploaded file. This has been given by the upload return.
     *
     * @return external_description
     */
    public static function commit_version($vridsource, $vrid, $draftitemid, $jsoninfo, $hideprevious = 'no') {
        global $CFG;

        $parameters = array(
            'vridsource' => $vridsource,
            'vrid' => $vrid,
            'draftitemid' => $draftitemid,
            'jsoninfo' => $jsoninfo,
            'hideprevious' => $hideprevious
        );
        $params = self::validate_parameters(self::commit_version_parameters(), $parameters);

        // Explicit mapping avoids injection.
        switch ($vridsource) {
            case 'idnumber':
                $vrs = self::get_vrs_from_idnumber($vrid);
                break;
            default:
                break;
        }

        if (versionned_resource::supports_feature('api/commit')) {
            include_once($CFG->dirroot.'/mod/versionnedresource/pro/lib.php');
            foreach ($vrs as $vr) {
                $vid = mod_versionnedresource_commit($vr->instance, $draftitemid, $jsoninfo, $hideprevious);
            }
            // Return last version number.
            return $vid;
        } else {
            throw new moodle_exception('unsupportedversion');
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function commit_version_returns() {
        return new external_value(PARAM_INT, 'Version id');
    }

    public static function get_vrs_from_idnumber($idnumber) {
        global $DB;

        $cms = $DB->get_records('course_modules', array('idnumber' => $idnumber));

        if (!$cms) {
            throw new moodle_exception('nocoursemodulematch');
        }

        return $cms;
    }
}

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

class versionned_resource {

    protected $record;

    public function __construct($record) {
        $this->record = $record;
    }

    public function __get($key) {
        if (isset($this->record->$key)) {
            return $this->record->$key;
        } else {
            throw new coding_exception('This attribute does no exist in versionnedresource');
        }
    }

    public function add_version($version) {
        global $DB;

        $version->versionnedresourceid = $this->record->id;
        $version->timemodified = time();

        $params = array('versionnedresourceid' => $version->versionnedresourceid);
        $lastversion = $DB->get_field('versionnedresource_version', 'MAX(internalversion)', $params);
        $version->internalversion = $lastversion + 1;
        return $DB->insert_record('versionnedresource_version', $version);
    }

    public function update_version($version) {
        global $DB;

        $version->versionnedresourceid = $this->record->id;
        $version->timemodified = time();
        $version->id = $version->vid;
        unset($version->vid);

        return $DB->update_record('versionnedresource_version', $version);
    }

    public function remove_version($versionid) {
        global $DB;

        $DB->delete_records('versionnedresource_version', array('id' => $versionid));

        $fs = get_file_storage();

        $cm = get_coursemodule_from_instance('versionnedresource', $this->record->id);
        $context = context_module::instance($cm->id);
        $fs->delete_area_files($context->id, 'mod_versionnedresource', 'artifact', $versionid);
    }

    public function hide_version($versionid) {
        global $DB;

        $DB->set_field('versionnedresource_version', 'visible', 0, array('id' => $versionid));
    }

    public function show_version($versionid) {
        global $DB;

        $DB->set_field('versionnedresource_version', 'visible', 1, array('id' => $versionid));
    }

    /**
     * Get resources versions
     * @param bool $last if true, will only return one result per branch.
     */
    public function get_versions($last = false, $showhidden = true) {
        global $DB, $USER;

        $params = array('versionnedresourceid' => $this->record->id);

        if (!$showhidden) {
            $params['visible'] = 1;
        }

        // Add eventual personal filter.
        if ($preferedbranch = $DB->get_field('user_preferences', 'value', array('name' => 'versionned_resource_branch', 'userid' => $USER->id))) {
            $params['branch'] = $preferedbranch;
        }

        $allversions = $DB->get_records('versionnedresource_version', $params, 'branch DESC, internalversion DESC');

        if (!$last) {
            return $allversions;
        }

        // Keep only one version per branch (highest).
        $branches = array();
        $last = array();
        foreach ($allversions as $version) {
            if (!in_array($version->branch, $branches)) {
                $branches[] = $version->branch;
                $last[$version->id] = $version;
            }
        }
        return $last;
    }

    /**
     * Tells wether a feature is supported or not. Gives back the
     * implementation path where to fetch resources.
     * @param string $feature a feature key to be tested.
     */
    static public function supports_feature($feature = '', $getsupported = false) {
        global $CFG;
        static $supports;

        if (!during_initial_install()) {
            $config = get_config('versionnedresource');
        }

        if (!isset($supports)) {
            $supports = array(
                'pro' => array(
                    'api' => array('getinfo', 'getfile', 'getlastbranchfile', 'commit'),
                ),
                'community' => array(
                    'api' => array('getinfo', 'getfile', 'getlastbranchfile'),
                ),
            );
            $prefer = array();
        }

        if ($getsupported) {
            return $supports;
        }

        // Check existance of the 'pro' dir in plugin.
        if (is_dir(__DIR__.'/pro')) {
            if ($feature == 'emulate/community') {
                return 'pro';
            }
            if (empty($config->emulatecommunity)) {
                $versionkey = 'pro';
            } else {
                $versionkey = 'community';
            }
        } else {
            $versionkey = 'community';
        }

        if (empty($feature)) {
            // Just return version.
            return $versionkey;
        }

        list($feat, $subfeat) = explode('/', $feature);

        if (!array_key_exists($feat, $supports[$versionkey])) {
            return false;
        }

        if (!in_array($subfeat, $supports[$versionkey][$feat])) {
            return false;
        }

        // Special condition for pdf dependencies.
        if (($feature == 'format/pdf') && !is_dir($CFG->dirroot.'/local/vflibs')) {
            return false;
        }

        if (in_array($feat, $supports['community'])) {
            if (in_array($subfeat, $supports['community'][$feat])) {
                // If community exists, default path points community code.
                if (isset($prefer[$feat][$subfeat])) {
                    // Configuration tells which location to prefer if explicit.
                    $versionkey = $prefer[$feat][$subfeat];
                } else {
                    $versionkey = 'community';
                }
            }
        }

        return $versionkey;
    }
}


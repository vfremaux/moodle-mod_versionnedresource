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
 * @package mod_versionnedresource
 * @copyright 2010 onwards Valery Fremaux (valery.freamux@club-internet.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one versionnedresource activity
 */
class restore_versionnedresource_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $versionnedresource = new restore_path_element('versionnedresource', '/activity/versionnedresource');
        $paths[] = $versionnedresource;
        $versions = new restore_path_element('versionnedresource_version', '/activity/versionnedresource/versions/version');
        $paths[] = $versions;

        if ($userinfo) {
            $subscribes = new restore_path_element('versionnedresource_subscribe', '/activity/versionnedresource/subscribes/subscribe');
            $paths[] = $subscribes;
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_versionnedresource($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the label record
        $newitemid = $DB->insert_record('versionnedresource', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_versionnedresource_version($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newid = $DB->insert_record('versionnedresource_version', $data);
        $this->set_mapping('versionnedresource_version', $oldid, $newid, false); // Has no related files.
    }

    protected function process_versionnedresource_subscribe($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newid = $DB->insert_record('versionnedresource_user_subs', $data);
        $this->set_mapping('versionnedresource_user_subs', $oldid, $newid, false); // Has no related files.
    }

}

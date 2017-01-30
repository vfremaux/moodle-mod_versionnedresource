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
 * Define all the backup steps that will be used by the backup_versionnedresource_activity_task
 *
 * @package    mod
 * @subpackage versionnedresource
 * @copyright  2010 onwards Valery Fremaux {valery.fremaux@club-internet.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete label structure for backup, with file and id annotations
 */
class backup_versionnedresource_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $versionnedresource = new backup_nested_element('versionnedresource', array('id'), array(
            'name', 'idnumber', 'intro', 'introformat', 'giturl', 'docurl',
            'branches', 'maturities', 'extraurl', 'timemodified'));

        $versions = new backup_nested_element('versions');

        $version = new backup_nested_element('version', array('id'), array(
            'versionnedresourceid', 'branch', 'internalversion', 'version', 'maturity', 'visible', 'changes',
            'giturl', 'docurl', 'downloads', 'timemodified'));

        $subscribes = new backup_nested_element('subscribes');

        $subscribe = new backup_nested_element('subscribe', array('id'), array(
            'versionnedresourceid', 'userid', 'timemodified'));

        // Build the tree.
        $versionnedresource->add_child($versions);
        $versions->add_child($version);

        $versionnedresource->add_child($subscribes);
        $subscribes->add_child($subscribe);

        // Define sources.
        $versionnedresource->set_source_table('versionnedresource', array('id' => backup::VAR_ACTIVITYID));
        $version->set_source_table('versionnedresource_version', array('versionnedresourceid' => backup::VAR_PARENTID));

        if ($userinfo) {
            $subscribe->set_source_table('versionnedresource_user_subs', array('versionnedresourceid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $subscribe->annotate_ids('user', 'userid');

        // Define file annotations.
        $versionnedresource->annotate_files('mod_versionnedresource', 'intro', null); // This file area hasn't itemid.
        $version->annotate_files('mod_versionnedresource', 'artifact', 'id');

        // Return the root element (versionnedresource), wrapped into standard activity structure.
        return $this->prepare_activity_structure($versionnedresource);
    }
}

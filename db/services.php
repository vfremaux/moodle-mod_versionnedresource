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
 * Forum external functions and service definitions.
 *
 * @package    mod_versionnedresource
 * @copyright  2012 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'mod_versionnedresource_get_version_info' => array(
        'classname' => 'mod_versionnedresource_external',
        'methodname' => 'get_version_info',
        'classpath' => 'mod/versionnedresource/externallib.php',
        'description' => 'Get info about all available versions',
        'type' => 'read',
        'capabilities' => 'mod/versionnedresource:download'
    ),

    'mod_versionnedresource_get_version_file_url' => array(
        'classname' => 'mod_versionnedresource_external',
        'methodname' => 'get_version_file_url',
        'classpath' => 'mod/versionnedresource/externallib.php',
        'description' => 'Gets the archive or file of a resource for a given versionid',
        'type' => 'read',
        'capabilities' => 'mod/versionnedresource:download'
    ),

    'mod_versionnedresource_get_last_branch_file_url' => array(
        'classname' => 'mod_versionnedresource_external',
        'methodname' => 'get_last_branch_file_url',
        'classpath' => 'mod/versionnedresource/externallib.php',
        'description' => 'Gets highest version archive or file of a resource for a given branch',
        'type' => 'read',
        'capabilities' => 'mod/versionnedresource:download'
    ),

    'mod_versionnedresource_commit_version' => array(
        'classname' => 'mod_versionnedresource_external',
        'methodname' => 'commit_version',
        'classpath' => 'mod/versionnedresource/externallib.php',
        'description' => 'Add a new version',
        'type' => 'write',
        'capabilities' => 'mod/versionnedresource:manageversions'
    ),

);

$services = array(
    'Moodle Versionned Resource API'  => array(
        'functions' => array (
            'mod_versionnedresource_get_version_info',
            'mod_versionnedresource_get_last_branch_file_url',
            'mod_versionnedresource_get_version_file_url',
            'mod_versionnedresource_commit_version',
        ),
        'enabled' => 0,
        'restrictedusers' => 0,
        'shortname' => 'mod_versionnedresource',
        'downloadfiles' => 1,
        'uploadfiles' => 1
    ),
);

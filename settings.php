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
defined('MOODLE_INTERNAL') || die();

$key = 'versionnedresource/defaultusedoc';
$label = get_string('configdefaultusedoc', 'versionnedresource');
$desc = get_string('configdefaultusedoc_desc', 'versionnedresource');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));

$key = 'versionnedresource/defaultusevcs';
$label = get_string('configdefaultusevcs', 'versionnedresource');
$desc = get_string('configdefaultusevcs_desc', 'versionnedresource');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));

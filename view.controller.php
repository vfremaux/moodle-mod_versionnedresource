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
 * Controller for catalogs.
 *
 * @package     mod_versionnedresource
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_versionnedresource;

defined('MOODLE_INTERNAL') || die();

// Note that other use cases are handled by the edit_catalogue.php script.

class view_controller {

    protected $instance;

    protected $data;

    protected $received;

    protected $mform;

    public function __construct($instance) {
        $this->instance = $instance;
    }

    public function receive($cmd, $data = array(), $mform = null) {

        if (!empty($data)) {
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'show':
            case 'hide':
            case 'delete':
                $this->data->versionid = required_param('vid', PARAM_INT);
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        if ($cmd == 'delete') {
            $this->instance->remove_version($this->data->versionid);
        }

        if ($cmd == 'hide') {
            $this->instance->hide_version($this->data->versionid);
        }

        if ($cmd == 'show') {
            $this->instance->show_version($this->data->versionid);
        }
    }

    public static function info() {
        return array('delete' => array(
                        'versionid' => 'Numeric ID for update',
                     ),
                     'hide' => array(
                        'versionid' => 'Numeric ID for update',
                     ),
                     'show' => array(
                        'versionid' => 'Numeric ID for update',
                     ),
                     );
    }
}
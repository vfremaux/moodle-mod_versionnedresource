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

class mod_versionnedresource_renderer extends plugin_renderer_base {

    protected $instance;

    protected $context;

    public function set_instance($instance) {
        $this->instance = $instance;
    }

    public function set_context($context) {
        $this->context = $context;
    }

    public function header($instance) {
        $str = '';

        $str .= '<h2>'.$instance->name.'</h2>';
        $str .= '<div class="versionned-description">'.format_text($instance->intro, $instance->introformat).'</div>';

        return $str;
    }

    public function versions($versions, $context) {

        $str = '';

        if (!empty($versions)) {
            $str .= '<div class="versions-list current">';
            $str .= '<h3>'.get_string('currentversions', 'versionnedresource').'</h3>';

            foreach ($versions as $v) {
                if ($v->visible || has_capability('mod/versionnedresource:viewhidden', $context)) {
                    $str .= $this->version($v, $context);
                }
            }
            $str .= '</div>';
        } else {
            $str .= '<div class="versions-list current">';
            $str .= $this->output->notification(get_string('noversionsavailable', 'versionnedresource'));
            $str .= '</div>';
        }

        return $str;
    }

    public function version(&$v, &$context) {
        global $DB;
        static $fs;

        if (!isset($fs)) {
            $fs = get_file_storage();
        }

        $str = '';

        $visibleclass = ($v->visible) ? 'version-visible' : 'version-hidden';

        $str .= '<div class="versions-item clearfix '.$visibleclass.'" id="version-'.$v->id.'">';
        $str .= '<div class="row-fluid">';
        $str .= '<div class="details span6">';
        $str .= '<div class="heading">';
        $str .= '<h4><span class="version">V'.$v->internalversion.' ('.$v->version.')</span></h4>';
        $str .= '<div class="version-branch">'.$v->branch.'</div>';
        $str .= '<div class="version-maturity">'.$v->maturity.'</div>';
        $str .= '</div>';
        $str .= '<div class="version-created"><small class="muted">Release date: '.userdate($v->timemodified).'</small></div>';
        if (!empty($v->changes)) {
            $str .= '<div class="version-changes">';
            $str .= '<div class="label">'.get_string('changes', 'versionnedresource').'</div>';
            $str .= '<div class="value">'.$v->changes.'</div>';
            $str .= '</div>';
        }
        $str .= '</div>';
        $str .= '<div class="actions span6">';
        $str .= '<div class="downloadcell">';

        $select = '
            component = :component AND
            filearea = :filearea AND
            itemid = :itemid AND
            filename IS NOT NULL AND
            filename != \'.\'
        ';
        $params = array('component' => 'mod_versionnedresource',
                        'filearea' => 'artifact',
                        'itemid' => $v->id);
        $file = $DB->get_record_select('files', $select, $params, 'id,id');

        if ($file) {
            $file = $fs->get_file_by_id($file->id);
            $contextid = $file->get_contextid();
            $str .= '<div class="plugin-get-buttons">';
            $versionnedurl = moodle_url::make_pluginfile_url($contextid, $file->get_component(), $file->get_filearea(),
                                                             $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                                                             $forcedownload = true);
            $str .= '<a class="download btn latest" href="'.$versionnedurl.'">'.get_string('download', 'versionnedresource').'</a>';

            $str .= '</div>';
        }

        $str .= '<div class="plugin-get-buttons">';
        if (has_capability('mod/versionnedresource:manageversions', $context)) {
            $params = array('vr' => $this->instance->id, 'vid' => $v->id, 'what' => 'delete', 'sesskey' => sesskey());
            $removeurl = new moodle_url('/mod/versionnedresource/view.php', $params);
            $deleteicon = $this->output->pix_icon('t/delete', get_string('deleteversion', 'versionnedresource'));
            $str .= '<a href="'.$removeurl.'" title="'.get_string('deleteversion', 'versionnedresource').'">'.$deleteicon.'</a>';

            $params = array('vr' => $this->instance->id, 'vid' => $v->id, 'what' => 'edit', 'sesskey' => sesskey());
            $editurl = new moodle_url('/mod/versionnedresource/versions.php', $params);
            $editicon = $this->output->pix_icon('t/edit', get_string('editversion', 'versionnedresource'));
            $str .= '&nbsp;<a href="'.$editurl.'" title="'.get_string('editversion', 'versionnedresource').'">'.$editicon.'</a>';

            if ($v->visible) {
                $params = array('vr' => $this->instance->id, 'vid' => $v->id, 'what' => 'hide', 'sesskey' => sesskey());
                $editurl = new moodle_url('/mod/versionnedresource/view.php', $params);
                $icon = $this->output->pix_icon('t/hide', get_string('hideversion', 'versionnedresource'));
                $str .= '&nbsp;<a href="'.$editurl.'" title="'.get_string('hideversion', 'versionnedresource').'">'.$icon.'</a>';
            } else {
                $params = array('vr' => $this->instance->id, 'vid' => $v->id, 'what' => 'show', 'sesskey' => sesskey());
                $editurl = new moodle_url('/mod/versionnedresource/view.php', $params);
                $icon = $this->output->pix_icon('t/show', get_string('showversion', 'versionnedresource'));
                $str .= '&nbsp;<a href="'.$editurl.'" title="'.get_string('showversion', 'versionnedresource').'">'.$icon.'</a>';
            }
        }
        $str .= '</div>';
        $str .= '</div>';

        if (!empty($v->docurl)) {
            $str .= '<div class="version-actions">';
            $learnmorestr = get_string('learnmore', 'versionnedresource');
            $str .= '<a class="btn btn-link action-view" href="'.$v->docurl.'">'.$learnmorestr.'</a>';
            $str .= '</div>';
        }
        if (!empty($v->giturl)) {
            $str .= '<div class="version-actions">';
            $gitrepostr = get_string('gitrepo', 'versionnedresource');
            $str .= '<a class="btn btn-link action-view" href="'.$v->giturl.'">'.$gitrepostr.'</a>';
            $str .= '</div>';
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';

        return $str;
    }

    public function add_version() {
        $str = '';

        $params = array('vr' => $this->instance->id, 'what' => 'add', 'sesskey' => sesskey());
        $removeurl = new moodle_url('/mod/versionnedresource/versions.php', $params);
        $str .= '<a class="btn " href="'.$removeurl.'">'.get_string('addversion', 'versionnedresource').'</a>';

        return $str;
    }

    public function token_button() {
        $str = '';

        $str .= $this->output->heading(get_string('apitoken', 'versionnedresource'));

        $str .= '<div class="version-api">';
        $str .= '<div class="version-api-doc cell">';
        $str .= get_string('gettoken_desc', 'versionnedresource');
        $str .= '</div>';
        $str .= '<div class="version-api-access cell">';

        $params = array('vr' => $this->instance->id, 'what' => 'get', 'sesskey' => sesskey());
        $editurl = new moodle_url('/mod/versionnedresource/pro/get_api_token.php', $params);
        $str .= '<a class="btn" href="'.$editurl.'">'.get_string('gettoken', 'versionnedresource').'</a>';

        $str .= '</div>';
    }
}
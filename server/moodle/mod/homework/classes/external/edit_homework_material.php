<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * homework/classes/external/save_homework_literature.php
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_homework\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_external\external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * Class for editing homework materials.
 */
class edit_homework_material extends external_api {
    /**
     * Returns parameters id, inputfield, link, startpage, endpage, starttime, endtime, homeworkid and fileid
     *
     * @return external_function_parameters Define the parameters expected by this function.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'homework id value'),
            'inputfield' => new external_value(PARAM_TEXT, 'Input field value'),
            'homeworkid' => new external_value(PARAM_INT, 'homeworkId field value'),
            'link' => new external_value(PARAM_TEXT, 'link field value', VALUE_OPTIONAL),
            'startpage' => new external_value(PARAM_INT, 'startPage field value', VALUE_OPTIONAL),
            'endpage' => new external_value(PARAM_INT, 'endPage field value', VALUE_OPTIONAL),
            'starttime' => new external_value(PARAM_INT, 'startTime field value', VALUE_OPTIONAL),
            'endtime' => new external_value(PARAM_INT, 'endTime field value', VALUE_OPTIONAL),
            'fileid' => new external_value(PARAM_INT, 'Uploaded file ID', VALUE_OPTIONAL),
        ]);
    }

    /**
     * The main function to handle the request.
     *
     * @param $id
     * @param $inputfield
     * @param $homeworkid
     * @param $link
     * @param $startpage
     * @param $endpage
     * @param $starttime
     * @param $endtime
     * @param $fileid
     * @return string[]
     * @throws \dml_exception
     */
    public static function execute(
        $id,
        $inputfield,
        $homeworkid,
        $link = null,
        $startpage = null,
        $endpage = null,
        $starttime = null,
        $endtime = null,
        $fileid = null
    ) {
        global $DB, $USER;

        $record = new \stdClass();

        $record->id = $id;
        $record->homework_id = $homeworkid;
        $record->description = $inputfield;

        $record->timecreated = time();
        $record->timemodified = time();
        $record->usermodified = $USER->id;

        $record->introformat = 0;

        $record->link = $link;

        $record->startpage = $startpage;
        $record->endpage = $endpage;

        $record->starttime = $starttime;
        $record->endtime = $endtime;

        $record->file_id = $fileid;

        try {
            $DB->update_record('homework_materials', $record);
        } catch (\dml_exception $e) {
            debugging("Error editing record in homework_materials: " . $e->getMessage(), DEBUG_DEVELOPER);
            return ['status' => 'error', 'message' => 'Failed to edit homework materials record'];
        }

        // Return a success response.
        return ['status' => 'success', 'message' => 'Data edited successfully'];
    }

    /**
     * Returns status and message as single structure
     *
     * @return external_single_structure Define the return values.
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the request'),
            'message' => new external_value(PARAM_TEXT, 'Message with details about the request status'),
        ]);
    }
}

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

namespace block_homework\external;

defined('MOODLE_INTERNAL') || die();
global $CFG;

use core_external\external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use external_single_structure;

/**
 * The class describing the external function, its parameters, and also its return value
 * @copyright group 1
 * @package block_homework
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_homeworktime extends external_api {
    /**
     * Use the official Moodle execute_parameters syntax to set up the parameters as a user id and 3 arrays of ID and time.
     * @return external_function_parameters Returns the parameters with the correct strucutre.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'user' => new external_value(PARAM_INT, 'user id'),
            'timeCompleted' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'material id'),
                'time' => new external_value(PARAM_INT, 'time'),
            ])),
        ]);
    }

    /**
     * The external function to save the time taken for homework data
     * @param $user ID of currently logged in user.
     * @param $timecompletedliterature Array of objects containing an ID and a time.
     * @param $timecompletedlinks Array of objects containing an ID and a time.
     * @param $timecompletedvideos Array of objects containing an ID and a time.
     * @return string[] Returns a success message if successful
     * @throws \dml_exception On error, throws a dml exception as per Moodle standards
     */
    public static function execute($user, $timecompleted) {
        global $DB;
        // Handle the input field value here.
        // For example, save to a database.
        // For each completed literature material, add the time taken and ID to a new completion.
        foreach ($timecompleted as $currtimecompleted) {
            $record = new \stdClass();

            $record->usermodified = $user;
            $record->timecreated = time();
            $record->timemodified = time();

            $record->material_id = $currtimecompleted['id'];
            $record->timetaken = $currtimecompleted['time'];

            $DB->insert_record('completions', $record);
        }

        // Return a success response.
        return ['status' => 'success', 'message' => 'Data saved successfully'];
    }

    /**
     * The method describing the return value
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the request'),
            'message' => new external_value(PARAM_TEXT, 'Message with details about the request status'),
        ]);
    }
}

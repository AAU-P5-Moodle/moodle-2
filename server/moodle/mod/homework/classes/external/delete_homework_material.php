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
 * homework/classes/external/delete_homework_literature.php
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_homework\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core\exception\coding_exception;
use core\exception\invalid_parameter_exception;
use core\exception\moodle_exception;
use core\exception\require_login_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use Matrix\Exception;

/**
 * Class for editing homework materials.
 */
class delete_homework_material extends external_api {
    /**
     * Returns parameters id and fileid
     *
     * @return external_function_parameters Define the parameters expected by this function.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'homework id value'),
            'fileid' => new external_value(PARAM_INT, 'Uploaded file ID', VALUE_OPTIONAL),
        ]);
    }

    /**
     * The main function to handle the request.
     *
     * @param $id
     * @param $fileid
     * @return string[]
     * @throws \dml_exception
     */
    public static function execute($id, $fileid = null): array {
        global $DB, $USER;

        try {
            // Delete the record from homework_materials.
            $DB->delete_records('homework_materials', ['id' => $id]);

            // Check if fileid is not null and delete from files.
            if (!empty($fileid)) {
                \mod_homework\external\delete_file::execute($id, $fileid);
            }
        } catch (\Exception $e) {
            debugging("Error deleting record in homework_materials: " . $e->getMessage(), DEBUG_DEVELOPER);
            return ['status' => 'error', 'message' => 'Failed to delete homework materials record'];
        }

        // Return a success response.
        return ['status' => 'success', 'message' => 'Data deleted successfully'];
    }

    /**
     * Returns status and message as single structure
     *
     * @return external_single_structure Define the return values.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the request'),
            'message' => new external_value(PARAM_TEXT, 'Message with details about the request status'),
        ]);
    }
}

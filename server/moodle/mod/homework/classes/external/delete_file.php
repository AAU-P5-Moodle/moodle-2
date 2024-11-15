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
 * homework/classes/external/delete_file.php
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_homework\external;

use core\exception\moodle_exception;
use external_api;
use external_function_parameters;
use external_value;
use context_system;

/**
 * Class to handle file deletion for mod_homework.
 */
class delete_file extends external_api {
    /**
     * Define the parameters for delete_file.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'The ID of the homework to update'),
                'fileid' => new external_value(PARAM_INT, 'The ID of the file to delete'),
            ]
        );
    }

    /**
     * Deletes the specified file.
     *
     * @param int $id The ID of the homework to update.
     * @param int $fileid The ID of the file to delete.
     * @return bool True if the file was successfully deleted, false otherwise.
     */
    public static function execute($id, $fileid) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'id' => $id,
            'file_id' => $fileid,
        ]);

        // Ensure the user is logged in and validate context.
        require_login();
        $context = context_system::instance();
        self::validate_context($context);

        // Include the library function to delete the file.
        require_once(__DIR__ . '/../../lib.php');
        $success = mod_homework_delete_file(
            $params['id'],
            $params['file_id']
        );

        if (!$success) {
            throw new moodle_exception('deleteerror', 'mod_homework', '', null, 'File deletion failed.');
        }

        return true;
    }

    /**
     * Define the return type for delete_file.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_BOOL, 'True if file was deleted successfully');
    }
}

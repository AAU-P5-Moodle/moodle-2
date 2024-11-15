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
 * The external function for requesting the modal for plugin.
 * @copyright group 1
 * @package block_homework
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_infohomework_modal extends external_api {
    /**
     * Returns the parameters for the execute function.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'homework_id' => new external_value(PARAM_INT, 'The ID of the homework item'),
            'data' => new external_multiple_structure(new external_single_structure([
                'description' => new external_value(PARAM_TEXT, 'Description of the homework'),
                'endpage' => new external_value(PARAM_INT, 'End page number'),
                'homework_id' => new external_value(PARAM_INT, 'The homework ID'),
                'id' => new external_value(PARAM_INT, 'Unique ID'),
                'introformat' => new external_value(PARAM_INT, 'Format of the introduction'),
                'startpage' => new external_value(PARAM_INT, 'Start page number'),
                'timecreated' => new external_value(PARAM_INT, 'Timestamp when created'),
                'timemodified' => new external_value(PARAM_INT, 'Timestamp when last modified'),
                'usermodified' => new external_value(PARAM_INT, 'User when last modified'),
                'link' => new external_value(PARAM_TEXT, 'Link to material'),
                'file_id' => new external_value(PARAM_INT, 'id of file'),
            ])),
        ]);
    }

    /**
     * Generates the custom HTML for the homework chooser modal.
     * @param int $homework_id The ID of the homework item
     * @return string[] - The HTML to be shown client-side
     */
    public static function execute($homeworkid, $data) {
        global $DB;

        // Assuming you have the Mustache engine set up.
        $mustache = new \Mustache_Engine();
        $nohomework = "";
        if (!$data) {
            $nohomework = "All completed";
        }
        // Prepare data for the template.
        $content = [
            'nohomework' => $nohomework,
            'materials' => $data,
        ];

        // Render the template.
        $html = $mustache->render(file_get_contents(__DIR__ . "/../../templates/timeinfotemplate.mustache"), $content);

        return ['html' => $html];
    }

    /**
     * Returns the structure of the function's response.
     * @return external_single_structure - Definition of the function's return type and description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'HTML for the homework chooser modal'),
        ]);
    }
}

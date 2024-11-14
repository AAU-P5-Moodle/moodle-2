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
 * homework/classes/external/get_homework_chooser.php
 * A class defining an external API function
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace mod_homework\external;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/externallib.php");

use core_external\external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 *
 */
class get_homework_chooser extends external_api {
    /**
     *
     * @return external_function_parameters Is a definition of the functions parameter type and a description of it.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course Module ID'),
        ]);
    }

    /**
     * The logic making the custom html for modal client-side
     * @param $cmid - The current modules id
     * @return string[] - The html to be shown client-side
     */
    public static function execute($cmid) {
        // Custom HTML for the homework chooser modal.
        $html = '
            <div id="homework-chooser-modal">
                <form>
                    <label for="inputField">Input Field:</label><br>
                    <textarea type="text" id="inputField" name="inputField"></textarea><br><br>
                    <label>Choose one:</label><br>
                    <input checked type="radio" id="option1" name="option" value="option1">
                    <label for="option1">Literature</label><br>
                    <input type="radio" id="option2" name="option" value="option2">
                    <label for="option2">Link</label><br>
                    <input type="radio" id="option3" name="option" value="option3">
                    <label for="option3">Video</label><br>
                    <input type="radio" id="option3" name="option" value="option3">
                    <label for="option4">Existing Resource</label><br><br>
                    <div id="page-range-input">
                        <label for="startPage">Page Range:</label><br>
                        <input type="number" id="startPage" name="startPage" min="1" placeholder="Start Page" style="width: 50px;">
                        <span>-</span>
                        <label for="endPage"></label>
                        <input type="number" id="endPage" name="endPage" min="1" placeholder="End Page" style="width: 50px;">
                    </div>
                    <div id="video-time-input" style="display:none">
                        <label for="startTime">Time Range (seconds):</label><br>
                        <input type="number" id="startTime" name="startTime" min="1" placeholder="Start Time" style="width: 50px;">
                        <span>-</span>
                        <label for="endTime"></label>
                        <input type="number" id="endTime" name="endTime" min="1" placeholder="End Time" style="width: 50px;">
                    </div>
                    <div id="linkDiv" style="display:none">
                        <label for="link">Link:</label><br>
                        <input name="link" id="link" type="url" placeholder="Enter URL">
                    </div>
                    <div id="dropzone-pdf-container">
                    </div>
                    <div id="dropzone-video-container" style="display:none;">
                    <div id="ExiResDiv" style="display:none">
                        <label for="existingresource">Existing Resource:</label><br>
                        <select name="existingresource" id="existingresource">
                            '.collection_files_controller::get_choices().'
                    </div>
                </form>
            </div>
        ';
        return ['html' => $html];
    }

    /**
     *
     * @return external_single_structure - Is a definition of the functions return type and a description of it
     */
    public static function execute_returns() {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'HTML for the homework chooser modal'),
        ]);
    }
}

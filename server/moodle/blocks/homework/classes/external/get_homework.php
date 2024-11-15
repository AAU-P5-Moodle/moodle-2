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
 * @package   block_homework
 * @copyright 2024, cs-24-sw-5-13 <cs-24-sw-5-13@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_homework\external;
defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_external\external_api;
use coding_exception;
use dml_exception;
use external_function_parameters;
use external_value;
use external_single_structure;
use JsonException;

/**
 *
 */
class get_homework extends external_api {
    /**
     *
     * @return external_function_parameters Is a definition of the functions parameter type and a description of it.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'sort' => new external_value(PARAM_TEXT, 'Sorting parameter'),
        ]);
    }

    /**
     * The logic making the custom html for modal client-side
     * @param $sort - The current modules id
     * @return array - The html to be shown client-side
     * @throws JsonException|dml_exception
     */
    public static function execute($sort) {
        global $DB, $USER;
        $usercourses = enrol_get_users_courses($USER->id, true);
        $homeworkarray = [];
        foreach ($usercourses as $course) {
            $homeworkrecords = $DB->get_records('homework', ['course_id' => $course->id]);
            foreach ($homeworkrecords as $homework) {
                $homeworkarray[] = [
                    'id' => $homework->id,
                    'name' => $homework->name,
                    'intro' => $homework->intro,
                    'duedate' => $homework->duedate,
                    'course' => $course->fullname,
                ];
            }
        }
        if ($sort === 'due') {
            $homeworkarray = self::sortDueDate($homeworkarray);
        }
        /*else if($sort === 'time'){
            $homeworkArray = self::sortTime($homeworkArray);
        }
        Implement when time task is done
        */

        return ["homework" => json_encode($homeworkarray, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR];
    }

    /**
     *
     * @param array $homeworkarray
     * @return array
     */
    public static function sortduedate(array $homeworkarray): array {
        usort($homeworkarray, function ($a, $b) {
            return $a['duedate'] - $b['duedate'];
        });
        return $homeworkarray;
    }

    /**
     *
     * @param array $homeworkarray
     * @return array
     */
    public static function sorttime(array $homeworkarray): array {
        usort($homeworkarray, function ($a, $b) {
            return $a['time'] - $b['time'];
        });
        return $homeworkarray;
    }

    /**
     *
     * @return string - The name of the function
     */
    public static function get_homework_returns() {
        return 'homework';
    }

    /**
     *
     * @return external_single_structure - Is a definition of the functions return type and a description of it
     */
    public static function execute_returns() {
        return new external_single_structure([
            'homework' => new external_value(PARAM_TEXT, 'Data array of courses'),
        ]);
    }
}

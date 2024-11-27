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

use coding_exception;
use core_external\external_api;
use dml_exception;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use JsonException;
use Mustache_Engine;

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
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'homeworkID' => new external_value(PARAM_INT, 'The ID of the homework item'),
            'readingspeed' => new external_value(PARAM_FLOAT, "The user's reading speed"),
        ]);
    }

    /**
     * Generates the custom HTML for the homework chooser modal.
     *
     * @param int $homeworkid
     * @return string[] - The HTML to be shown client-side
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function execute(int $homeworkid, float $readingspeed): array {
        global $DB, $USER;
        $homework = $DB->get_record('homework', ['id' => $homeworkid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $homework->course_id]);
        $materials = $DB->get_records('homework_materials', ['homework_id' => $homework->id]);
        $completedmaterials = $DB->get_records('completions', ['usermodified' => $USER->id]);
        $literaturearray = [];
        $linksarray = [];
        $videosarray = [];
        foreach ($materials as $material) {
            $completed = false;
            foreach ($completedmaterials as $completedmaterial) {
                if ($completedmaterial->material_id === $material->id) {
                    $completed = true;
                    break;
                }
            }
            if ($completed) {
                continue;
            }
            if ($material->startpage !== null && $material->endpage !== null) {
                if ($material->file_id !== null) {
                    $material->fileurl = self::get_file_link_by_id($material->file_id);
                }
                $material->expectedTime = ceil(($material->endpage - $material->startpage) * $readingspeed);
                $literaturearray[] = $material;
            } else if ($material->link !== null) {
                $linksarray[] = $material;
            } else if ($material->starttime !== null && $material->endtime !== null) {
                if ($material->file_id !== null) {
                    $material->fileurl = self::get_file_link_by_id($material->file_id);
                }
                $videosarray[] = $material;
            }
            if ($material->starttime != null && $material->endtime != null) {
                $material->expectedTime = ceil(($material->endtime - $material->starttime)/60);
            }
        }

        return self::get_info($homework, $course, $literaturearray, $linksarray, $videosarray);
    }

    /**
     * Returns the structure of the function's response.
     * @return external_single_structure - Definition of the function's return type and description
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'HTML for the homework chooser modal'),
            'title' => new external_value(PARAM_TEXT, 'Title of the homework'),
            'course' => new external_value(PARAM_TEXT, 'Course name'),
            'duedate' => new external_value(PARAM_TEXT, 'Due date of the homework'),
            'courseurl' => new external_value(PARAM_TEXT, 'The URL for the course'),
            'homeworkurl' => new external_value(PARAM_TEXT, 'The URl for the homework'),
        ]);
    }

    /**
     *
     */
    public static function get_info($homework, $course, $literaturearray, $linksarray, $videosarray): array {
        // Assuming you have the Mustache engine set up.
        $mustache = new Mustache_Engine();
        $nohomework = "";

        if (count($literaturearray) === 0 && count($linksarray) === 0 && count($videosarray) === 0) {
            $nohomework = "All completed";
        }
        // Prepare data for the template.
        $content = [
            'nohomework' => $nohomework,
            'homeworkdescription' => strip_tags($homework->intro),
            'literature' => $literaturearray,
            'links' => $linksarray,
            'videos' => $videosarray,
        ];

        // Render the template.
        $html = $mustache->render(file_get_contents(__DIR__ . "/../../templates/timeinfotemplate.mustache"), $content);

        $duedate = date('H:i d-m-Y', $homework->duedate);
        $courseurl = "/course/view.php?id=" . $course->id;
        $homeworkurl = "/mod/homework/view.php?id=" . $homework->id;

        return ['html' => $html, 'title' => $homework->name, 'course' => $course->fullname,
            'duedate' => $duedate, 'courseurl' => $courseurl, 'homeworkurl' => $homeworkurl];
    }

    /**
     * Get a direct link to a file by its file ID.
     *
     * @param int $fileid The ID of the file in Moodle's file storage.
     * @return string|null The URL to the file or null if the file is not found.
     * @throws dml_exception|coding_exception
     */
    public static function get_file_link_by_id(int $fileid): null|string {
        global $DB;

        // Retrieve the file record from the database.
        $file = $DB->get_record('files', ['id' => $fileid]);

        // Check if the file exists and is valid.
        if (!$file || $file->filename === '.' || $file->filename === '') {
            return null;
        }

        // Generate the file URL.
        $context = \context::instance_by_id($file->contextid);
        $fs = get_file_storage();
        $storedfile = $fs->get_file(
            $file->contextid,
            $file->component,
            $file->filearea,
            $file->itemid,
            $file->filepath,
            $file->filename
        );

        if ($storedfile) {
            // Moodle's file plugin serves the files through pluginfile.php.
            $fileurl = \moodle_url::make_pluginfile_url(
                $storedfile->get_contextid(),
                $storedfile->get_component(),
                $storedfile->get_filearea(),
                $storedfile->get_itemid(),
                $storedfile->get_filepath(),
                $storedfile->get_filename()
            );
            return $fileurl->out();
        }

        return null;
    }
}

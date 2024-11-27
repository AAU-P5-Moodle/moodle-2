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
 * Code for viewing each homework module for details (not done)
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $OUTPUT, $PAGE, $DB, $CFG;

use block_homework\external\get_infohomework_modal;
use mod_homework\view_page;

$id = required_param('id', PARAM_INT);// Course module ID.
[$course, $cm] = get_course_and_cm_from_cmid($id, 'homework');
$instance = $DB->get_record('homework', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);
require_login($course, true, $cm);

try {
    $PAGE->set_url('/mod/homework/view.php', ['id' => $id]);
} catch (coding_exception $e) {
    debugging($e->getMessage(), DEBUG_DEVELOPER);
}
try {
    $PAGE->set_title(get_string('modulename', 'homework'));
} catch (coding_exception $e) {
    debugging($e->getMessage(), DEBUG_DEVELOPER);
}
$PAGE->set_heading(get_string('modulename', 'homework'));

// Adding secondary navigation links.
if ($PAGE->has_secondary_navigation()) {
    // Create a new navigation node for 'Submissions'.
    $submissionsnode = navigation_node::create(
        get_string('viewsubmissions', 'mod_homework'), // The label.
        new moodle_url('/mod/homework/submissions.php', ['id' => $cm->id]), // URL for the link.
        navigation_node::TYPE_CUSTOM, // Type of node (custom link).
        null, // Icon or image (null by default).
        'submissionsnav' // Unique key for the navigation node.
    );

    // Add the submissions node to the secondary navigation.
    $PAGE->secondarynav->add_node($submissionsnode);
    if (has_capability('mod/homework:edit', $context)) {
        // Example: Add another node, e.g., 'Edit Homework'.
        try {
            $editnode = navigation_node::create(
                get_string('edit', 'moodle'),
                new moodle_url('/mod/homework/edit.php', ['id' => $cm->id]),
                navigation_node::TYPE_CUSTOM,
                null,
                'editnav'
            );
            $PAGE->secondarynav->add_node($editnode);
        } catch (coding_exception | \core\exception\moodle_exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}

// Output the header - REQUIRED.
echo $OUTPUT->header();

$viewobj = new view_page();
$viewobj->canedit = true;



$homeworkmaterials = $DB->get_records_sql(
    "SELECT hm.*, f.filename
        FROM {homework_materials} hm
        LEFT JOIN {files} f ON hm.file_id = f.id
        WHERE hm.homework_id = :homework_id",
    ['homework_id' => $cm->instance]
);
?>
<?php
/**
 * Loop through each item in the homework material and display it.
 *
 * @var object $material Literature item with description, startpage, and endpage properties.
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
echo '<div class="view-homework-container">';
foreach ($homeworkmaterials as $material) : ?>
    <div class="material"">
        <?php if ($material->startpage != null):
            echo '<i class="fa-solid fa-book"></i>';
        elseif ($material->link != null):
            echo '<i class="fa-solid fa-link"></i>';
        elseif ($material->starttime != null):
            echo '<i class="fa-solid fa-play"></i>';
        elseif ($material->file_id != null):
            echo '<i class="fa-solid fa-file"></i>';
        endif; ?>
        <div class="material-container">
        <p><?php echo htmlspecialchars($material->description) ?></p>
        <?php if ($material->startpage != null) : ?>
            <p><?php echo "Pages: " . htmlspecialchars($material->startpage) . " - " . htmlspecialchars($material->endpage) ?></p>
        <?php if ($material->link != null) :
        // Checks to see if a link starts with "http" if not, then add it to the string,
        // this makes sure its is completely new site that is opened.
        $link = !str_starts_with($material->link, 'http') ? "https://" . $material->link : $material->link;
        ?><p><?php echo 'Link: <a href="' . $link . '" target="_blank">Click here</a>';?></p>
        <?php endif; ?>
        </div>
            <?php
        else :
            if ($material->link != null) :
                    // Checks to see if a link starts with "http" if not, then add it to the string,
                    // this makes sure its is completely new site that is opened.
                    $link = !str_starts_with($material->link, 'http') ? "https://" . $material->link : $material->link;
                ?>
            <p><?php echo 'Link: <a href="' . $link . '" target="_blank">Click here</a>';?></p>
            </div>
            <?php endif; ?>
        <?php endif;
        if ($material->starttime != null) :
            ?>
        <p><?php
        // Create strings showing the times as HH:MM:SS or MM:SS if no hours.
        $starttime = converttime($material->starttime);
        $endtime = converttime($material->endtime);
        echo "Watch: " . htmlspecialchars($starttime) . " - " . htmlspecialchars($endtime) ?></p>
                <?php
                if ($material->file_id != null) :
                    $fs = get_file_storage();
                    $url = null;
                    $haspermission = has_capability('mod/homework:managefiles', $context);

                    if ($haspermission) {
                        // Debugging: Check file details.
                        $filerecord = $DB->get_record('files', ['id' => $material->file_id]);

                        if ($filerecord) {
                            // Fetch file using get_file_storage.
                            $video = $fs->get_file(
                                $filerecord->contextid,
                                $filerecord->component,
                                $filerecord->filearea,
                                $filerecord->itemid,
                                $filerecord->filepath,
                                $filerecord->filename
                            );

                            if ($video && !$video->is_directory()) {
                                // Generate a URL for the file.
                                $url = moodle_url::make_pluginfile_url(
                                    $video->get_contextid(),
                                    $video->get_component(),
                                    $video->get_filearea(),
                                    $video->get_itemid(),
                                    $video->get_filepath(),
                                    $video->get_filename(),
                                    false
                                );
                                // Output video player.
                                if ($url) {
                                            echo '<video controls width="640" height="360">';
                                            echo '<source src="' . $url->out() . '" type="video/mp4">';
                                            echo 'Your browser does not support the video tag.';
                                            echo '</video>';
                                } else {
                                    echo 'Error: URL not generated.<br>';
                                }
                            } else {
                                echo 'Error: Video file not found or is a directory.<br>';
                            }
                        } else {
                            echo 'File not found in DB.<br>';
                        }
                    } else {
                        echo 'You do not have permission to manage files in this homework activity.<br>';
                    }
                    ?>
                    </div>
                <?php endif; ?>
        <?php else :
            if ($material->file_id != null) :
                    $fs = get_file_storage();
                    $url = null;
                    $haspermission = has_capability('mod/homework:managefiles', $context);

                if ($haspermission) {
                    // Debugging: Check file details.
                    $filerecord = $DB->get_record('files', ['id' => $material->file_id]);

                    if ($filerecord) {
                        // Fetch file using get_file_storage.
                        $file = $fs->get_file(
                            $filerecord->contextid,
                            $filerecord->component,
                            $filerecord->filearea,
                            $filerecord->itemid,
                            $filerecord->filepath,
                            $filerecord->filename
                        );

                        if ($file && !$file->is_directory()) {
                            // Generate a URL for the file.
                            $url = moodle_url::make_pluginfile_url(
                                $file->get_contextid(),
                                $file->get_component(),
                                $file->get_filearea(),
                                $file->get_itemid(),
                                $file->get_filepath(),
                                $file->get_filename(),
                                false
                            );
                            // Output the hyperlink for the user.
                            if ($url) {
                                        echo '<a href="' . $url . '" download>Click here to download the file </a>';
                            } else {
                                echo 'Error: URL not generated.<br>';
                            }
                        } else {
                            echo 'Error: File not found or is a directory.<br>';
                        }
                    } else {
                        echo 'File not found in DB.<br>';
                    }
                } else {
                    echo 'You do not have permission to manage files in this homework activity.<br>';
                }
                ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php
echo '</div>';?>

<?php
 /**
  *
  * @package   mod_homework
  * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
  * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

if ($viewobj->canedit && !$viewobj->hashomework && has_capability('mod/homework:edit', $context)) {
    // Add the button for opening the homework chooser modal.
    echo html_writer::tag('button', get_string('openhomeworkchooser', 'mod_homework'), [
        'type' => 'button',
        'id' => 'open-homework-chooser',
        'class' => 'btn btn-primary',
    ]);

    echo '<br>';
    echo '<br>';

    echo html_writer::tag('button', get_string('openeventlinker', 'mod_homework'), [
        'type' => 'button',
        'id' => 'open-event-linker',
        'class' => 'btn btn-primary',
    ]);

    // Include the AMD modules.
    $PAGE->requires->js_call_amd('mod_homework/homeworkchooser', 'init', [$cm->id,
        get_string('homeworkchooser', 'mod_homework'), $instance->id]);
    $PAGE->requires->js_call_amd('mod_homework/eventlinker', 'init', [$cm->id, $instance->id]);
}

// Output the footer - REQUIRED.
echo $OUTPUT->footer();

/**
 * Converts a number of seconds into a time in HH:MM:SS format, or MM:SS format if no hours
 * @param int $seconds number of seconds
 * @return string The time in HH:MM:SS or MM:SS format
 */
function converttime($seconds) {
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $seconds = $seconds % 60;
    $time =
        ($hours != 0 ? ($hours < 10 ? "0" . $hours : $hours) . ":" : "")
        . ($minutes < 10 ? "0" . $minutes : $minutes) . ":"
        . ($seconds < 10 ? "0" . $seconds : $seconds);
    return $time;
}

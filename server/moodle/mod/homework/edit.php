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
 * File for page of the editor
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');
global $OUTPUT, $PAGE, $DB, $CFG;

$id = required_param('id', PARAM_INT); // Course module ID.
[$course, $cm] = get_course_and_cm_from_cmid($id, 'homework');
$instance = $DB->get_record('homework', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);
require_login($course, true, $cm);

$PAGE->set_url('/mod/homework/edit.php', ['id' => $id]);
$PAGE->set_title(get_string('modulename', 'homework'));
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

    // Example: Add another node, e.g., 'Edit Homework'.
    $editnode = navigation_node::create(
        get_string('edit', 'moodle'),
        new moodle_url('/mod/homework/edit.php', ['id' => $cm->id]),
        navigation_node::TYPE_CUSTOM,
        null,
        'editnav'
    );
    $PAGE->secondarynav->add_node($editnode);
}

// Output the header - REQUIRED.
echo $OUTPUT->header();

echo html_writer::tag('div', 'This is the homework edit page', ['class' => 'content']);

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
 * Loop through each item in the homework literature and display it.
 *
 * @var object $literature Literature item with description, startpage, and endpage properties.
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
foreach ($homeworkmaterials as $materials) :

    // Generate the preview URL for the file if it exists
    if ($materials->file_id !== null) {
        // Retrieve additional metadata for generating the URL
        $file = $DB->get_record('files', ['id' => $materials->file_id]);
        if ($file) {
            // Generate the preview URL using Moodle's pluginfile.php
            $preview_url = moodle_url::make_pluginfile_url(
                $file->contextid,
                'mod_homework',
                'content',
                $file->itemid,
                '/',
                $file->filename
            );
        }
        echo $file->filename;
    }
    ?>

    <div class="material" style="border: 1px solid #ccc;padding: 16px;border-radius: 8px;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);background-color: #f9f9f9;">
        <p><?= htmlspecialchars($materials->description) ?></p>
        <?php if ($materials->startpage !== null && $materials->endpage !== null): ?>
            <p><?= "Page: " . htmlspecialchars($materials->startpage) . " - " . htmlspecialchars($materials->endpage) ?></p>
        <?php endif; ?>
        <?php if ($materials->link !== null): ?>
            <p><?= "Link: " ?><a href="<?= htmlspecialchars($materials->link) ?>"><?= htmlspecialchars($materials->link) ?></a></p>
        <?php endif; ?>
        <?php if ($materials->starttime !== null && $materials->endtime !== null): ?>
            <p><?= "Time (seconds): " . htmlspecialchars($materials->starttime) . " - " . htmlspecialchars($materials->endtime) ?></p>
        <?php endif; ?>

        <?php if ($materials->file_id !== null && isset($preview_url)): ?>
            <?php if (strtolower(pathinfo($file->filename, PATHINFO_EXTENSION)) === 'mp4'): ?>
                <!-- Display the video inline if it's an mp4 file -->
                <video controls width="100%">
                    <source src="<?= $preview_url ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <!-- Provide a link for non-video files -->
                <p><?= "File: " ?><a href="<?= $preview_url ?>" target="_blank"><?= htmlspecialchars($file->filename) ?></a> (Preview)</p>
            <?php endif; ?>
        <?php endif; ?>

        <?= html_writer::tag('button', get_string('openhomeworkchooser', 'mod_homework'), [
            'type' => 'button',
            'id' => 'open-homework-chooser-' . $materials->id,
            'class' => 'btn btn-primary'
        ]); ?>
    </div>
<?php endforeach; ?>
<?php
/**
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo html_writer::start_tag('div', ['class' => 'mod-quiz-edit-content']);

$homeworkmaterialids = array_map(function($material) {
    return [
        'id' => $material->id,
        'description' => $material->description,
        'startpage' => $material->startpage,
        'endpage' => $material->endpage,
        'link' => $material->link,
        'starttime' => $material->starttime,
        'endtime' => $material->endtime,
        'file_id' => $material->file_id,
        'filename' => $material->filename
    ];
}, $homeworkmaterials);

$PAGE->requires->js_call_amd('mod_homework/homeworkchooseredit', 'init', [$cm->id,
    get_string('homeworkchooser', 'mod_homework'), $instance->id, $homeworkmaterialids]);

// Output the footer - REQUIRED.
echo $OUTPUT->footer();

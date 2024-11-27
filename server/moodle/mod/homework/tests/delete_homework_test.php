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
 * PHPUnit test case for mod_homework's save homework functionality.
 *
 * @package   mod_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_homework;

use advanced_testcase;
use dml_exception;

/**
 *
 */
final class delete_homework_test extends advanced_testcase {
    /**
     * Setup routine before running each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true); // Reset the Moodle environment after each test.
    }

    /**
     * Test saving literature homework with page range.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_literature
     */
    public function test_edit_homework_literature(): void {
        global $DB, $CFG, $USER;

        // Create a test user.
        $user = self::getDataGenerator()->create_user();

        // Log in as the test user.
        self::setUser($user);

        // Call the external class method.
        $inputfield = 'Test Literature';
        $link = null;
        $startpage = 1;
        $endpage = 10;
        $starttime = null;
        $endtime = null;
        $homeworkid = 1;
        $fileid = null;

        $result = \mod_homework\external\save_homework_material::execute(
            $inputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the status is 'success'.
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Data saved successfully', $result['message']);

        // Verify that the data was saved in the database.
        $record = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $inputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($startpage, $record->startpage);
        $this->assertEquals($endpage, $record->endpage);
        $this->assertEquals($homeworkid, $record->homework_id);

        $recordid = $record->id;

        $deleteresult = \mod_homework\external\delete_homework_material::execute($recordid, $fileid);

        // Assert that the status is 'success' for deletion.
        $this->assertEquals('success', $deleteresult['status']);
        $this->assertEquals('Data deleted successfully', $deleteresult['message']);

        // Verify that the record no longer exists in the database.
        $deletedrecord = $DB->get_record('homework_materials', ['id' => $recordid]);
        $this->assertFalse($deletedrecord);
    }

    /**
     * Test saving a homework with a link.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_link
     */
    public function test_edit_homework_link(): void {
        global $DB;

        // Call the external class method.
        $inputfield = 'Test Literature';
        $link = 'https://www.test.com';
        $startpage = 1;
        $endpage = 10;
        $starttime = null;
        $endtime = null;
        $homeworkid = 1;
        $fileid = null;

        $result = \mod_homework\external\save_homework_material::execute(
            $inputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the status is 'success'.
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Data saved successfully', $result['message']);

        // Verify that the data was saved in the database.
        $record = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $inputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($link, $record->link);
        $this->assertEquals($homeworkid, $record->homework_id);

        $recordid = $record->id;

        $deleteresult = \mod_homework\external\delete_homework_material::execute($recordid, $fileid);

        // Assert that the status is 'success' for deletion.
        $this->assertEquals('success', $deleteresult['status']);
        $this->assertEquals('Data deleted successfully', $deleteresult['message']);

        // Verify that the record no longer exists in the database.
        $deletedrecord = $DB->get_record('homework_materials', ['id' => $recordid]);
        $this->assertFalse($deletedrecord);
    }

    /**
     * Test saving a homework with a video.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_video
     */
    public function test_edit_homework_video(): void {
        global $DB;

        // Call the external class method.
        $inputfield = 'Test Video';
        $link = null;
        $startpage = null;
        $endpage = null;
        $starttime = 0;
        $endtime = 60;
        $homeworkid = 1;
        $fileid = null;

        $result = \mod_homework\external\save_homework_material::execute(
            $inputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the status is 'success'.
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Data saved successfully', $result['message']);

        // Verify that the data was saved in the database.
        $record = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $inputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($starttime, $record->starttime);
        $this->assertEquals($endtime, $record->endtime);
        $this->assertEquals($homeworkid, $record->homework_id);

        $recordid = $record->id;

        $deleteresult = \mod_homework\external\delete_homework_material::execute($recordid, $fileid);

        // Assert that the status is 'success' for deletion.
        $this->assertEquals('success', $deleteresult['status']);
        $this->assertEquals('Data deleted successfully', $deleteresult['message']);

        // Verify that the record no longer exists in the database.
        $deletedrecord = $DB->get_record('homework_materials', ['id' => $recordid]);
        $this->assertFalse($deletedrecord);
    }


    /**
     * Test deleting a homework with a video.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_video
     */
    public function test_delete_homework_video(): void {
        global $DB;

        // Call the external class method.
        $inputfield = 'Test Video';
        $link = null;
        $startpage = null;
        $endpage = null;
        $starttime = 0;
        $endtime = 60;
        $homeworkid = 1;
        $fileid = null;

        $record = new \stdClass();
        $record->inputfield = $inputfield;
        $record->homeworkid = $homeworkid;
        $record->startpage = $startpage;
        $record->endpage = $endpage;
        $record->starttime = $starttime;
        $record->endtime = $endtime;
        $record->fileid = $fileid;
        $record->link = $link;

        $deleteresult = \mod_homework\external\delete_homework_material::execute($record->homeworkid, $record->fileid);
        // Assert that the status is 'success' for deletion.
        $this->assertEquals('success', $deleteresult['status']);
        $this->assertEquals('Data deleted successfully', $deleteresult['message']);

        // Verify that the record no longer exists in the database.
        $deletedrecord = $DB->get_record('homework_materials', ['id' => $record->fileid]);
        $this->assertFalse($deletedrecord);
    }

    /**
     * Tests if an exception is thrown when deleting a file that does not exist
     * @runInSeparateProcess
     * @throws moodle_exception
     * @throws dml_exception
     * @covers :: \mod_homework\external\delete_file
     */
    public function test_delete_non_existing_homeworkfile(): void {
        // Call the external class method.
        $inputfield = 'Test Video';
        $link = null;
        $startpage = null;
        $endpage = null;
        $starttime = 0;
        $endtime = 60;
        $homeworkid = 1;
        $fileid = null;

        $result = \mod_homework\external\save_homework_material::execute(
            $inputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        $invalidfileid = 999; // Non-existent file ID.

        $this->expectException(\moodle_exception::class);

        // Attempt to delete with an invalid file ID.
        \mod_homework\external\delete_file::execute($homeworkid, $invalidfileid);
    }

    public function test_delete_non_existing_homework(): void {
        // Call the external class method.
        $inputfield = 'Test Video';
        $link = null;
        $startpage = null;
        $endpage = null;
        $starttime = 0;
        $endtime = 60;
        $homeworkid = 1;
        $fileid = null;

        $result = \mod_homework\external\save_homework_material::execute(
            $inputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        $invalidfileid = 99; // Non-existent file ID.
        // Attempt to delete with an invalid file ID.
        $result = \mod_homework\external\delete_homework_material::execute($homeworkid, $invalidfileid);
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to delete homework materials record', $result['message']);
    }
}

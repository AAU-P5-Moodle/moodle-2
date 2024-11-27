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
final class save_homework_test extends advanced_testcase {
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
     * @covers :: \mod_homework\external\save_homework_material
     */
    public function test_save_homework_literature(): void {
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
    }

    /**
     * Test saving a homework with a link.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_material
     */
    public function test_save_homework_link(): void {
        global $DB, $CFG, $USER;

        // Create a test user.
        $user = self::getDataGenerator()->create_user();

        // Log in as the test user.
        self::setUser($user);

        // Call the external class method.
        $inputfield = 'Test Link';
        $link = 'https://www.test.com';
        $startpage = null;
        $endpage = null;
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
    }

    /**
     * Test saving a homework with a video.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_material
     */
    public function test_save_homework_video(): void {
        global $DB, $CFG, $USER;

        // Create a test user.
        $user = self::getDataGenerator()->create_user();

        // Log in as the test user.
        self::setUser($user);

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
    }

    /**
     * Test saving a homework file.
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\save_homework_file
     */
    public function test_file_upload(): void {
        global $CFG, $USER;

        // Create a test user.
        $user = self::getDataGenerator()->create_user();

        // Log in as the test user.
        self::setUser($user);

        // Mock Moodle's file storage system.
        $mockfilestorage = $this->createMock(\file_storage::class);
        $mockfile = $this->createMock(\stored_file::class);

        // Mock context and methods.
        $context = $this->createMock(\context_user::class);
        $context->method('instance')->willReturn($context);

        // Mock file options.
        $_FILES['file'] = [
            'name' => 'testfile.txt',
            'tmp_name' => __DIR__ . '/assets/testfile.txt',
        ];

        // Mock file existence check and file creation.
        $mockfilestorage->method('file_exists')->willReturn(false);
        $mockfilestorage->method('create_file_from_pathname')->willReturn($mockfile);

        // Include the script and capture output.
        ob_start();
        include(__DIR__ . '/../upload_file.php');
        $output = ob_get_clean();

        // Assert that the output contains a success message.
        $this->assertStringContainsString('"status":"success","message":"File uploaded successfully"', $output);
    }

    /**
     * Test editing a homework without required parameter.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_material
     */
    public function test_save_homework_missing_required_parameter(): void {
        global $DB;

        // Call the external class method.
        $inputfield = null;
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
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to save homework materials record', $result['message']);
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);
    }

    /**
     * Test editing a homework without required parameter.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\save_homework_material
     */
    public function test_save_homework_wrong_attribute_type(): void {
        global $DB;

        // Call the external class method.
        $inputfield = 'Homework';
        $link = 'https://www.test.com';
        $startpage = 1;
        $endpage = "Number as a string";
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
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to save homework materials record', $result['message']);
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);
    }
}

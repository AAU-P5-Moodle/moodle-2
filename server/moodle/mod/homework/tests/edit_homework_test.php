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
final class edit_homework_test extends advanced_testcase {
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

        // Updated data for editing the homework material.
        $newinputfield = 'Updated Literature';
        $newstartpage = 2;
        $newendpage = 15;

        // Call the edit method to update the existing record.
        $editresult = \mod_homework\external\edit_homework_material::execute(
            $recordid,
            $newinputfield,
            $homeworkid,
            $link,
            $newstartpage,
            $newendpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the edit was successful.
        $this->assertEquals('success', $editresult['status']);
        $this->assertEquals('Data edited successfully', $editresult['message']);

        // Verify that the updated data was saved in the database.
        // Verify that the data was saved in the database.
        $updatedrecord = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $newinputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($newinputfield, $updatedrecord->description);
        $this->assertEquals($newstartpage, $updatedrecord->startpage);
        $this->assertEquals($newendpage, $updatedrecord->endpage);
        $this->assertEquals($homeworkid, $updatedrecord->homework_id);
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

        // Updated data for editing the homework material.
        $newinputfield = 'Updated Link';
        $newlink = 'https://www.youtube.com';

        // Call the edit method to update the existing record.
        $editresult = \mod_homework\external\edit_homework_material::execute(
            $recordid,
            $newinputfield,
            $homeworkid,
            $newlink,
            $startpage,
            $endpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the edit was successful.
        $this->assertEquals('success', $editresult['status']);
        $this->assertEquals('Data edited successfully', $editresult['message']);

        // Verify that the updated data was saved in the database.
        // Verify that the data was saved in the database.
        $updatedrecord = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $newinputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($newinputfield, $updatedrecord->description);
        $this->assertEquals($newlink, $updatedrecord->link);
        $this->assertEquals($homeworkid, $updatedrecord->homework_id);
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

        // Updated data for editing the homework material.
        $newinputfield = 'Updated Video';
        $newstarttime = 1;
        $newendtime = 61;

        // Call the edit method to update the existing record.
        $editresult = \mod_homework\external\edit_homework_material::execute(
            $recordid,
            $newinputfield,
            $homeworkid,
            $link,
            $startpage,
            $endpage,
            $newstarttime,
            $newendtime,
            $fileid
        );

        // Assert that the edit was successful.
        $this->assertEquals('success', $editresult['status']);
        $this->assertEquals('Data edited successfully', $editresult['message']);

        // Verify that the updated data was saved in the database.
        // Verify that the data was saved in the database.
        $updatedrecord = $DB->get_record_select(
            'homework_materials',
            $DB->sql_compare_text('description') . ' = :description',
            ['description' => $newinputfield],
            '*',
            MUST_EXIST
        );

        $this->assertEquals($newinputfield, $updatedrecord->description);
        $this->assertEquals($newstarttime, $updatedrecord->starttime);
        $this->assertEquals($newendtime, $updatedrecord->endtime);
        $this->assertEquals($homeworkid, $updatedrecord->homework_id);
    }


    /**
     * Test editing a homework with wrong data type.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\edit_homework_material
     */
    public function test_edit_homework_wrong_attribute_type(): void {
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

        // Updated data for editing the homework material.
        $newendpage = "ok";
        $newlink = 'https://www.youtube.com';
        // Call the edit method to update the existing record.
        $editresult = \mod_homework\external\edit_homework_material::execute(
            $recordid,
            $inputfield,
            $homeworkid,
            $newlink,
            $startpage,
            $newendpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the edit was successful.
        $this->assertEquals('error', $editresult['status']);
        $this->assertEquals('Failed to edit homework materials record', $editresult['message']);
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);
    }

    /**
     * Test editing a homework without required parameter.
     *
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \mod_homework\external\edit_homework_material
     */
    public function test_edit_homework_missing_required_parameter(): void {
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

        // Updated data for editing the homework material.
        $newendpage = "ok";
        $newlink = 'https://www.youtube.com';
        $newinputfield = null;

        // Call the edit method to update the existing record.
        $editresult = \mod_homework\external\edit_homework_material::execute(
            $recordid,
            $newinputfield,
            $homeworkid,
            $newlink,
            $startpage,
            $newendpage,
            $starttime,
            $endtime,
            $fileid
        );

        // Assert that the edit was successful.
        $this->assertEquals('error', $editresult['status']);
        $this->assertEquals('Failed to edit homework materials record', $editresult['message']);
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);
    }
}

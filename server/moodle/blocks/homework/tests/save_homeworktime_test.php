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

namespace block_homework;

use advanced_testcase;
use block_homework\external\save_homeworktime;
use dml_exception;

/**
 * Test for the external function saving time taken for homework
 * @copyright group 1
 * @package block_homework
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class save_homeworktime_test extends advanced_testcase {
    /**
     * Test the save_homeworktime execute function
     * @throws dml_exception
     * @covers :: \block_homework\external\save_homeworktime
     * @runInSeparateProcess
     */
    public function test_save_timeindb(): void {
        global $DB;

        // Reset the database after each test.
        $this->resetAfterTest();

        // Prepare test data.
        $user = $this->getDataGenerator()->create_user();
        $userid = $user->id;

        $timecompletedliterature = [
            ['id' => 1, 'time' => 30],
            ['id' => 2, 'time' => 45],
        ];

        // Execute the external function.
        $resultliterature = save_homeworktime::execute($userid, $timecompletedliterature);

        // Check that the return structure and values are as expected.
        $this->assertIsArray($resultliterature, 'Result should be an array');
        $this->assertArrayHasKey('status', $resultliterature);
        $this->assertArrayHasKey('message', $resultliterature);
        $this->assertEquals('success', $resultliterature['status']);
        $this->assertEquals('Data saved successfully', $resultliterature['message']);

        $timecompletedlinks = [
            ['id' => 3, 'time' => 20],
            ['id' => 4, 'time' => 35],
        ];

        // Execute the external function.
        $resultlinks = save_homeworktime::execute($userid, $timecompletedlinks);

        // Check that the return structure and values are as expected.
        $this->assertIsArray($resultlinks, 'Result should be an array');
        $this->assertArrayHasKey('status', $resultlinks);
        $this->assertArrayHasKey('message', $resultlinks);
        $this->assertEquals('success', $resultlinks['status']);
        $this->assertEquals('Data saved successfully', $resultlinks['message']);

        $timecompletedvideos = [
            ['id' => 5, 'time' => 50],
            ['id' => 6, 'time' => 25],
        ];

        // Execute the external function.
        $resultvideos = save_homeworktime::execute($userid, $timecompletedvideos);

        // Check that the return structure and values are as expected.
        $this->assertIsArray($resultvideos, 'Result should be an array');
        $this->assertArrayHasKey('status', $resultvideos);
        $this->assertArrayHasKey('message', $resultvideos);
        $this->assertEquals('success', $resultvideos['status']);
        $this->assertEquals('Data saved successfully', $resultvideos['message']);

        // Verify data has been saved in the database for literature.
        foreach ($timecompletedliterature as $item) {
            $this->assertRecordExists('completions', [
                'timecreated' => time(),
                'timemodified' => time(),
                'usermodified' => (int) $userid,
                'material_id' => $item['id'],
                'timetaken' => $item['time'],
            ]);
        }

        // Verify data has been saved in the database for links.
        foreach ($timecompletedlinks as $item) {
            $this->assertRecordExists('completions', [
                'timecreated' => time(),
                'timemodified' => time(),
                'usermodified' => (int) $userid,
                'material_id' => $item['id'],
                'timetaken' => $item['time'],
            ]);
        }

        // Verify data has been saved in the database for videos.
        foreach ($timecompletedvideos as $item) {
            $this->assertRecordExists('completions', [
                'timecreated' => time(),
                'timemodified' => time(),
                'usermodified' => (int) $userid,
                'material_id' => $item['id'],
                'timetaken' => $item['time'],
            ]);
        }
    }

    /**
     *
     * @return void
     * @runInSeparateProcess
     * @throws dml_exception
     * @covers :: \block_homework\classes\external\save_homeworktime
     */
    public function test_save_timeout_error(): void {
        global $DB;
        $timecompletedvideos = [
            ['id' => null, 'time' => null],
            ['id' => null, 'time' => null],
        ];
        // Reset the database after each test.
        $this->resetAfterTest();

        // Prepare test data.
        $user = $this->getDataGenerator()->create_user();
        $userid = $user->id;

        $this->expectException(\dml_write_exception::class);

        save_homeworktime::execute($userid, $timecompletedvideos);
    }

    /**
     * Helper function to check if a record exists in the database.
     *
     * @param string $table The database table name.
     * @param array $conditions Array of conditions to match.
     * @throws dml_exception
     */
    private function assertrecordexists(string $table, array $conditions): void {
        global $DB;
        $exists = $DB->record_exists($table, $conditions);
        $this->assertTrue($exists, 'Record does not exist in ' . $table . ' table for conditions: ' . json_encode($conditions));
    }
}

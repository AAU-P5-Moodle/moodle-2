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
// File: mod/myplugin/tests/sample_test.php.

namespace block_homework;

use block_homework;
use block_homework\external\filter_homework;
use stdClass;
use function DI\get;


/**
 * Test for block homework
 * @copyright group 11
 * @package block_homework
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class filter_homework_test extends \advanced_testcase {
    /**
     * The test method itself
     * @return void
     * @covers :: block_homework
     * @runInSeparateProcess
     */
    public function test_filter_homework(): void {
        // Create test data.
        $homeworksarray = [];

        $homeworksarray[] = [
            'id' => 1,
            'course' => 2,
            'name' => 'test 1',
            'timecreated' => time(),
            'timemodified' => 0,
            'intro' => '<p> test 1 </p>',
            'introformat' => 1,
            'description' => '',
            'eventid' => 0,
            'duedate' => time() + 86400000 * 2,
            'time' => time() + 86400000 * 2,
        ];
        $homeworksarray[] = [
            'id' => 2,
            'course' => 3,
            'name' => 'test 2',
            'timecreated' => time(),
            'timemodified' => 0,
            'intro' => '<p> test 2 </p>',
            'introformat' => 1,
            'description' => '',
            'eventid' => 0,
            'duedate' => time() - 86400000,
            'time' => time() - 86400000,
        ];

        $tmparray = filter_homework::filter("previous", $homeworksarray);
        $this->assertEquals(1, count($tmparray));
        $this->assertEquals($homeworksarray[1]["id"], $tmparray[0]["id"]);

        $tmparray = filter_homework::filter("2", $homeworksarray);
        $this->assertEquals(1, count($tmparray));
        $this->assertEquals($homeworksarray[0]["course"], $tmparray[0]["course"]);
    }
}

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
 * homework/tests/modal_test.php
 *
 * @package   block_homework
 * @copyright 2024, cs-24-sw-5-01 <cs-24-sw-5-01@student.aau.dk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_homework;

use advanced_testcase;
use block_homework\external\get_infohomework_modal;
use core\exception\coding_exception;
use dml_exception;
use DOMDocument;
use stdClass;

/**
 * Test for modal rendering.
 * @copyright group 1
 * @package block_homework
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class block_homeworkinfomodal_test extends advanced_testcase {
    /**
     *
     * @return void
     * @runInSeparateProcess
     * @covers :: \block_homework\external\get_infohomework_modal
     */
    public function test_get_info_all_input(): void {
        global $DB;

        // Set up necessary data for the test, such as course and module.
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $homework = new stdClass();
        $homework->id = 1;
        $homework->course = $course->id;
        $homework->name = 'test';
        $homework->timecreated = strtotime('2023-10-01 10:00:00');
        $homework->timemodified = strtotime('2023-10-01 10:00:00');
        $homework->intro = 'test description';
        $homework->introformat = 1;
        $homework->description = 'test description';
        $homework->duedate = strtotime('2023-10-01 10:00:00');
        $homework->eventid = null;

        // Data1.
        $literature = [
            (object)[
                'description' => 'Science project on climate change',
                'endpage' => 15,
                'homework_id' => $homework->id,
                'id' => 2,
                'introformat' => 1,
                'startpage' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 4,
            ],
        ];

        // Data2.
        $links = [
            (object)[
                'description' => 'Reference materials',
                'link' => 'http://example.com/references',
                'homework_id' => $homework->id,
                'id' => 2,
                'timecreated' => strtotime('2023-10-03 09:00:00'),
                'timemodified' => strtotime('2023-10-04 14:00:00'),
                'usermodified' => 6,
            ],
        ];

        // Data3.
        $videos = [
            (object) [
                'description' => 'Presentation for science project',
                'homework_id' => $homework->id,
                'fileid' => 502,
                'id' => 2,
                'introformat' => 1,
                'starttime' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 8,
            ],
        ];

        // Call the external function directly.
        $result = \block_homework\external\get_infohomework_modal::get_info($homework, $course, $literature, $links, $videos);

        // Parse the HTML using DOMDocument to check for the required elements.
        $dom = new DOMDocument();
        @$dom->loadHTML($result['html']);  // Suppressing warnings from invalid HTML.

        // Check that each element is present in the HTML.
        $this->assertNotNull($dom->getElementById('info-homework-modal'), 'Modal container is missing');
        $xpath = new \DOMXPath($dom);
        $homeworkdescription = $xpath->query("//p[@class='homeworkdescription']")->item(0);
        $this->assertEquals($homework->description, $homeworkdescription->textContent, 'Homework description is incorrect');
        $this->assertNotNull($xpath->query("//div[@class='homeworkmaterialcontainer']")->item(0));

        // Check for input with specific attributes & their labels for literature.
        $this->assertNotNull($dom->getElementById('literature-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-text']")->item(0));
        $lit2description = $xpath->query("//div[@id='literature-2']//a")->item(0);
        $this->assertEquals($literature[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='literature-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-literature'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-literature\'');

        // Check for input with specific attributes & their labels for links.
        $this->assertNotNull($dom->getElementById('links-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-link']")->item(0));
        $link2description = $xpath->query("//div[@id='links-2']//a")->item(0);
        $this->assertEquals($links[0]->description, $link2description->textContent);
        $this->assertEquals($links[0]->link, $link2description->getAttribute('href'));
        $this->assertNotNull($xpath->query("//div[@id='links-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-links'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-links\'');

        // Check for input with specific attributes & their labels, for videos.
        $this->assertNotNull($dom->getElementById('videos-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-video-o']")->item(0));
        $lit2description = $xpath->query("//div[@id='videos-2']//a")->item(0);
        $this->assertEquals($videos[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='videos-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-videos'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-videos\'');
    }

    /**
     *
     * @return void
     * @runInSeparateProcess
     * @covers :: \block_homework\external\get_infohomework_modal
     */
    public function test_get_info_no_litterature(): void {
        global $DB;

        // Set up necessary data for the test, such as course and module.
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $homework = new stdClass();
        $homework->id = 1;
        $homework->course = $course->id;
        $homework->name = 'test';
        $homework->timecreated = strtotime('2023-10-01 10:00:00');
        $homework->timemodified = strtotime('2023-10-01 10:00:00');
        $homework->intro = 'test description';
        $homework->introformat = 1;
        $homework->description = 'test description';
        $homework->duedate = strtotime('2023-10-01 10:00:00');
        $homework->eventid = null;

        // Data1.
        $literature = [
        ];

        // Data2.
        $links = [
            (object)[
                'description' => 'Reference materials',
                'link' => 'http://example.com/references',
                'homework_id' => $homework->id,
                'id' => 2,
                'timecreated' => strtotime('2023-10-03 09:00:00'),
                'timemodified' => strtotime('2023-10-04 14:00:00'),
                'usermodified' => 6,
            ],
        ];

        // Data3.
        $videos = [
            (object) [
                'description' => 'Presentation for science project',
                'homework_id' => $homework->id,
                'fileid' => 502,
                'id' => 2,
                'introformat' => 1,
                'starttime' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 8,
            ],
        ];

        // Call the external function directly.
        $result = \block_homework\external\get_infohomework_modal::get_info($homework, $course, $literature, $links, $videos);

        // Parse the HTML using DOMDocument to check for the required elements.
        $dom = new DOMDocument();
        @$dom->loadHTML($result['html']);  // Suppressing warnings from invalid HTML.

        // Check that each element is present in the HTML.
        $this->assertNotNull($dom->getElementById('info-homework-modal'), 'Modal container is missing');
        $xpath = new \DOMXPath($dom);
        $homeworkdescription = $xpath->query("//p[@class='homeworkdescription']")->item(0);
        $this->assertEquals($homework->description, $homeworkdescription->textContent, 'Homework description is incorrect');
        $this->assertNotNull($xpath->query("//div[@class='homeworkmaterialcontainer']")->item(0));

        $this->assertEquals(null, $dom->getElementById('literature-2'));
        $this->assertEmpty($xpath->query("//i[@class='fa-solid fa-file-text']"));
        $this->assertEmpty($xpath->query("//div[@id='literature-2']//a"));
        $this->assertEmpty($xpath->query("//div[@id='literature-2']//form"));
        $this->assertEmpty($xpath->query("//input[@class='homework-time-literature'][@id='2'][@name='homework-time'][@min='1']"));

        // Check for input with specific attributes & their labels for links.
        $this->assertNotNull($dom->getElementById('links-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-link']")->item(0));
        $link2description = $xpath->query("//div[@id='links-2']//a")->item(0);
        $this->assertEquals($links[0]->description, $link2description->textContent);
        $this->assertEquals($links[0]->link, $link2description->getAttribute('href'));
        $this->assertNotNull($xpath->query("//div[@id='links-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-links'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-links\'');

        // Check for input with specific attributes & their labels, for videos.
        $this->assertNotNull($dom->getElementById('videos-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-video-o']")->item(0));
        $lit2description = $xpath->query("//div[@id='videos-2']//a")->item(0);
        $this->assertEquals($videos[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='videos-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-videos'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-videos\'');
    }

    /**
     *
     * @return void
     * @runInSeparateProcess
     * @covers :: \block_homework\external\get_infohomework_modal
     */
    public function test_get_info_no_link(): void {
        global $DB;

        // Set up necessary data for the test, such as course and module.
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $homework = new stdClass();
        $homework->id = 1;
        $homework->course = $course->id;
        $homework->name = 'test';
        $homework->timecreated = strtotime('2023-10-01 10:00:00');
        $homework->timemodified = strtotime('2023-10-01 10:00:00');
        $homework->intro = 'test description';
        $homework->introformat = 1;
        $homework->description = 'test description';
        $homework->duedate = strtotime('2023-10-01 10:00:00');
        $homework->eventid = null;

        // Data1.
        $literature = [
            (object)[
                'description' => 'Science project on climate change',
                'endpage' => 15,
                'homework_id' => $homework->id,
                'id' => 2,
                'introformat' => 1,
                'startpage' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 4,
            ],
        ];

        // Data2.
        $links = [
        ];

        // Data3.
        $videos = [
            (object) [
                'description' => 'Presentation for science project',
                'homework_id' => $homework->id,
                'fileid' => 502,
                'id' => 2,
                'introformat' => 1,
                'starttime' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 8,
            ],
        ];

        // Call the external function directly.
        $result = \block_homework\external\get_infohomework_modal::get_info($homework, $course, $literature, $links, $videos);

        // Parse the HTML using DOMDocument to check for the required elements.
        $dom = new DOMDocument();
        @$dom->loadHTML($result['html']);  // Suppressing warnings from invalid HTML.

        // Check that each element is present in the HTML.
        $this->assertNotNull($dom->getElementById('info-homework-modal'), 'Modal container is missing');
        $xpath = new \DOMXPath($dom);
        $homeworkdescription = $xpath->query("//p[@class='homeworkdescription']")->item(0);
        $this->assertEquals($homework->description, $homeworkdescription->textContent, 'Homework description is incorrect');
        $this->assertNotNull($xpath->query("//div[@class='homeworkmaterialcontainer']")->item(0));

        // Check for input with specific attributes & their labels for literature.
        $this->assertNotNull($dom->getElementById('literature-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-text']")->item(0));
        $lit2description = $xpath->query("//div[@id='literature-2']//a")->item(0);
        $this->assertEquals($literature[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='literature-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-literature'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-literature\'');

        $this->assertEquals(null, $dom->getElementById('links-2'));
        $this->assertEmpty($xpath->query("//i[@class='fa-solid fa-link']"));
        $this->assertEmpty($xpath->query("//div[@id='links-2']//a"));
        $this->assertEmpty($xpath->query("//div[@id='links-2']//form"));
        $this->assertEmpty($xpath->query("//input[@class='homework-time-links'][@id='2'][@name='homework-time'][@min='1']"));

        // Check for input with specific attributes & their labels, for videos.
        $this->assertNotNull($dom->getElementById('videos-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-video-o']")->item(0));
        $lit2description = $xpath->query("//div[@id='videos-2']//a")->item(0);
        $this->assertEquals($videos[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='videos-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-videos'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-videos\'');
    }

    /**
     *
     * @return void
     * @runInSeparateProcess
     * @covers :: \block_homework\external\get_infohomework_modal
     */
    public function test_get_info_no_video(): void {
        global $DB;

        // Set up necessary data for the test, such as course and module.
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $homework = new stdClass();
        $homework->id = 1;
        $homework->course = $course->id;
        $homework->name = 'test';
        $homework->timecreated = strtotime('2023-10-01 10:00:00');
        $homework->timemodified = strtotime('2023-10-01 10:00:00');
        $homework->intro = 'test description';
        $homework->introformat = 1;
        $homework->description = 'test description';
        $homework->duedate = strtotime('2023-10-01 10:00:00');
        $homework->eventid = null;

        // Data1.
        $literature = [
            (object)[
                'description' => 'Science project on climate change',
                'endpage' => 15,
                'homework_id' => $homework->id,
                'id' => 2,
                'introformat' => 1,
                'startpage' => 11,
                'timecreated' => strtotime('2023-10-05 11:00:00'),
                'timemodified' => strtotime('2023-10-06 13:00:00'),
                'usermodified' => 4,
            ],
        ];

        // Data2.
        $links = [
            (object)[
                'description' => 'Reference materials',
                'link' => 'http://example.com/references',
                'homework_id' => $homework->id,
                'id' => 2,
                'timecreated' => strtotime('2023-10-03 09:00:00'),
                'timemodified' => strtotime('2023-10-04 14:00:00'),
                'usermodified' => 6,
            ],
        ];

        // Data3.
        $videos = [
        ];

        // Call the external function directly.
        $result = \block_homework\external\get_infohomework_modal::get_info($homework, $course, $literature, $links, $videos);

        // Parse the HTML using DOMDocument to check for the required elements.
        $dom = new DOMDocument();
        @$dom->loadHTML($result['html']);  // Suppressing warnings from invalid HTML.

        // Check that each element is present in the HTML.
        $this->assertNotNull($dom->getElementById('info-homework-modal'), 'Modal container is missing');
        $xpath = new \DOMXPath($dom);
        $homeworkdescription = $xpath->query("//p[@class='homeworkdescription']")->item(0);
        $this->assertEquals($homework->description, $homeworkdescription->textContent, 'Homework description is incorrect');
        $this->assertNotNull($xpath->query("//div[@class='homeworkmaterialcontainer']")->item(0));

        // Check for input with specific attributes & their labels for literature.
        $this->assertNotNull($dom->getElementById('literature-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-file-text']")->item(0));
        $lit2description = $xpath->query("//div[@id='literature-2']//a")->item(0);
        $this->assertEquals($literature[0]->description, $lit2description->textContent);
        $this->assertNotNull($xpath->query("//div[@id='literature-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-literature'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-literature\'');

        // Check for input with specific attributes & their labels for links.
        $this->assertNotNull($dom->getElementById('links-2'));
        // Check for input with specific attributes & their labels.
        $this->assertNotNull($xpath->query("//i[@class='fa-solid fa-link']")->item(0));
        $link2description = $xpath->query("//div[@id='links-2']//a")->item(0);
        $this->assertEquals($links[0]->description, $link2description->textContent);
        $this->assertEquals($links[0]->link, $link2description->getAttribute('href'));
        $this->assertNotNull($xpath->query("//div[@id='links-2']//form"));
        $input = $xpath->query("//input[@class='homework-time-links'][@id='2'][@name='homework-time'][@min='1']");
        $this->assertEquals(1, $input->length, 'Expected input with class \'homework-time-links\'');

        $this->assertEquals(null, $dom->getElementById('videos-2'));
        $this->assertEmpty($xpath->query("//i[@class='fa-solid fa-file-video-o']"));
        $this->assertEmpty($xpath->query("//div[@id='videos-2']//a"));
        $this->assertEmpty($xpath->query("//div[@id='videos-2']//form"));
        $this->assertEmpty($xpath->query("//input[@class='homework-time-videos'][@id='2'][@name='homework-time'][@min='1']"));
    }

    /**
     *
     * @return void
     * @covers :: \block_homework\external\get_infohomework_modal
     */
    public function test_executethrowsexceptionwhenhomeworknotfound(): void {
        $this->resetAfterTest();

        $this->expectException(\dml_exception::class);
        $homeworkid = 99999999999999999;
        // Call the static method.
        get_infohomework_modal::execute($homeworkid); // Pass a homework ID.
    }

    /**
     *
     * @return void
     * @covers :: \block_homework\external\get_infohomework_modal
     * @throws dml_exception|coding_exception
     */
    public function test_get_file_by_id_notfounderror(): void {
        $this->resetAfterTest();
        $fileid = 99999999999999999;
        // Call the static method.
        $result = get_infohomework_modal::get_file_link_by_id($fileid);
        self::assertEquals(null, $result);
    }
}

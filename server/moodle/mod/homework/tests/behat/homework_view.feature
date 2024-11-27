@mod @mod_homework
Feature: Viewing the homework page

  In order to view homework materials and content
  As a student
  I need to visit the homework view page and check the content

  Background:
    Given the following "users" exist:
      | username  | firstname | lastname | email                     |
      | student1  | Student   | 1        | student1@school.com       |
      | teacher1  | Teacher   | 1        | teacher1@school.com       |

    And the following "course" exist:
      | fullname        | shortname |
      | Example Course  | EC123     |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | EC123 | editingteacher |
    And the following "activities" exists:
      | activity | homework         |
      | course   | EC123            |
      | idnumber | 1                |
      | name     | Myhomework       |
      | intro    | Test description |
      | section  | 0                |
    And the following "mod_homework > materials" exist:
     | id | description       | startpage | endpage | link               | starttime | endtime |file_id | homework_id | timecreated | timemodified | usermodified |
     | 0  | Sample material 1 | 1         | 10      |                    |           |         |123     | 0           | 0           | 0            | 0            |
     | 1  | Sample material 2 |           |         | http://example.com |           |         |        | 1           | 0           | 0            | 0            |
     | 2  | Sample material 3 |           |         |                    | 132       | 3601    |234     | 2           | 0           | 0            | 0            |

    And I log in as "teacher1"
    And I am on "Example Course" course homepage with editing mode off
@javascript
  Scenario: Teacher views the homework materials
    When I click on "Myhomework" "link" in the "homework" activity

    And I should see "Myhomework"


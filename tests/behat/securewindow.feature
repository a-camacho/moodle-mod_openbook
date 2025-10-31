@mod @mod_openbook
Feature: Testing secure window integration in openbook activity
  In order to use the openbook activity in a secure exam
  As a user
  I need to be able to see the openbook resources page in a secure window

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activity" exists:
      | course                | C1                                |
      | activity              | openbook                          |
      | name                  | Openbook resource folder activity |
      | intro                 | description                       |
      | idnumber              | openbook1                         |

  Scenario: The Openbook resource folder shows the view page in a normal/secure layout for teachers/students
    When I am on the "Openbook resource folder activity" "mod_openbook > Edit" page logged in as "teacher1"
    And I set the following fields to these values:
      | Teacher approval    | Automatic     |
      | Student approval    | Automatic     |
      | Upload from         | ## 3 weeks ago ## |
      | To         | ## 2 weeks ago ## |
      | Start secure window | ##yesterday## |
      | End secure window   | ##tomorrow##  |
    And I press "Save and return to course"
    And I am on the "Openbook resource folder activity" "mod_openbook > View" page logged in as "teacher1"
    And "Acceptance test site" "link" should exist
    And I log out
    And I am on the "Openbook resource folder activity" "mod_openbook > View" page logged in as "student1"
    Then "Acceptance test site" "link" should not exist

  @javascript
  Scenario: Override the Openbook resource secure layout date for a student
    When I am on the "Openbook resource folder activity" "mod_openbook > Edit" page logged in as "teacher1"
    And I set the following fields to these values:
      | Teacher approval    | Automatic         |
      | Student approval    | Automatic         |
      | Upload from         | ## 3 weeks ago ## |
      | To                  | ## 2 weeks ago ## |
      | Start secure window | ##yesterday##     |
      | End secure window   | ##tomorrow##      |
    And I press "Save and return to course"
    And I am on the "Openbook resource folder activity" "mod_openbook > Overrides" page logged in as "teacher1"
    And I follow "Add user override"
    And I set the following fields to these values:
      | User                | Student 2        |
      | Start secure window | ## 1 week ago ## |
      | End secure window   | ##yesterday##    |
    And I press "Save changes"
    And "Acceptance test site" "link" should exist
    And I log out
    And I am on the "Openbook resource folder activity" "mod_openbook > View" page logged in as "student1"
    And "Acceptance test site" "link" should not exist
    And I log out
    And I am on the "Openbook resource folder activity" "mod_openbook > View" page logged in as "student2"
    Then "Acceptance test site" "link" should exist

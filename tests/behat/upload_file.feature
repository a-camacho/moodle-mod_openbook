@mod @mod_privatestudentfolder @mod_resource @_file_upload
Feature: Upload file in privatestudentfolder

  Background:
    Given the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@asd.com |
        | student1 | Student | 2 | student1@asd.com |
    And the following "courses" exist:
        | fullname | shortname | category | startdate |
        | Course 1 | C1 | 0 | 1460386247 |
        | Course 2 | C2 | 0 | 1460386247 |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | teacher1 | C2 | editingteacher |
        | student1 | C1 | student        |
    And the following "activities" exist:
        | activity              | course | name     |
        | folder                | C1     | F1       |
        | privatestudentfolder  | C1     | PVS1     |

  @javascript
  Scenario: Upload file in privatestudentfolder instance in course1
    Given I am on the "PVS1" "privatestudentfolder activity" page logged in as teacher1
    And I click on "Edit/upload files" "button"
    And I upload "tests/fixtures/empty.txt" file to "File" filemanager
    And I press "Save changes"
    Then I should see "empty.txt"

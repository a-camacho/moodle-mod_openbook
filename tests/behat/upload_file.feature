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
      | activity              | course | name    |
      | privatestudentfolder  | C1     | PVS1    |

  @javascript
  Scenario: Upload file in privatestudentfolder instance in course1
    Given I am on the "PVS1" "privatestudentfolder activity" page logged in as student1
    And I click on "gotoupload" "button"
    # Upload a file in folder resource
    And I upload "lib/tests/fixtures/empty.txt" file to "Files" filemanager
    And I press "Save changes"
    And I click on "Edit" "button"
    And I click on "empty.txt" "link"
    # Initially, file details are set to default values
    And the following fields match these values:
      | Name    | empty.txt             |
      | Author  | Teacher 1             |
      | licence | Licence not specified |
    # Update the file details for testing
    When I set the following fields to these values:
      | Name    | empty_file.txt      |
      | Author  | Teacher 1           |
      | licence | All rights reserved |
    And I press "Update"
    And I press "Save changes"
    # Confirm that file details have been updated correctly
    And I click on "Edit" "button"
    And I click on "empty_file.txt" "link"
    Then the following fields match these values:
      | Name    | empty_file.txt      |
      | Author  | Teacher 1           |
      | licence | All rights reserved |
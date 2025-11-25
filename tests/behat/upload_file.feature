@mod @mod_openbook @_file_upload
Feature: Upload file in openbook

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
      | student1 | Student   | 1        | student1@asd.com |
      | student2 | Student   | 1        | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category | startdate     |
      | Course 1 | C1        | 0        | ##yesterday## |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name                     | maxbytes | filesarepersonal |
      | openbook | C1     | Openbook resource folder | 8388608  | 0                |

  @javascript
  Scenario: Click on Give Approval button in openbook instance in course1
    Given I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I click on "Give approval" "link"
    Then I should see "File submissions"

  @javascript @_file_upload
  Scenario: Upload file as student in a openbook instance and get automatic teacher approval
    When I am on the "Openbook resource folder" "openbook activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field with xpath "//*[@id='id_allowsubmissionsfromdate_enabled']" to "0"
    And I set the field with xpath "//*[@id='id_duedate_enabled']" to "0"
    And I set the following fields to these values:
      | Files are personal | Yes (files are personal) |
      | Teacher approval   | Automatic                |
    And I press "Save and display"
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student1
    And I click on "Edit/upload files" "button"
    And I upload "mod/openbook/tests/fixtures/empty.txt" file to "Own files" filemanager
    And I press "Save changes"
    And I should see "empty.txt"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I should not see "empty.txt"
    And I click on "1" "link" in the "Files submitted by students" "table_row"
    And I should see "empty.txt"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student1
    Then I should see "empty.txt"

  @javascript @_file_upload
  Scenario: Upload file as student in a openbook instance, set it to shared and watch it as other student
    When I am on the "Openbook resource folder" "openbook activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field with xpath "//*[@id='id_allowsubmissionsfromdate_enabled']" to "0"
    And I set the field with xpath "//*[@id='id_duedate_enabled']" to "0"
    And I set the following fields to these values:
      | Files are personal | No (files can be shared between students) |
      | Teacher approval   | Automatic                                 |
      | Approval from      | ##yesterday##                             |
    And I press "Save and display"
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student1
    And I click on "Edit/upload files" "button"
    And I upload "mod/openbook/tests/fixtures/student_file_shared.pdf" file to "Own files" filemanager
    And I press "Save changes"
    And I should see "student_file_shared.pdf"
    And I set the field with xpath "//select[contains(@name,'studentapproval')]" to "Share"
    And I enable auto-accept for confirm dialogs
    And I press "Save changes"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student2
    Then I should see "student_file_shared.pdf"
    And I log out

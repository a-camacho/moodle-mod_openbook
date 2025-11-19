@mod @mod_openbook @_file_upload
Feature: Upload file as teacher in openbook

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | startdate     |
      | Course 1 | C1        | 0        | ##yesterday## |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name                     | maxbytes | filesarepersonal | teacherapproval |
      | openbook | C1     | Openbook resource folder | 8388608  | 1                | 0               |

  @javascript @_file_upload
  Scenario: Upload file as lecturer in a openbook instance with files are personal
    When I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I click on "Edit/upload files" "button"
    And I should see "Own files"
    And I upload "mod/openbook/tests/fixtures/teacher_file.pdf" file to "Own files" filemanager
    And I press "Save changes"
    Then I should see "teacher_file.pdf"

  @javascript @_file_upload
  Scenario: Upload file as lecturer in a openbook instance with files are sharable
    When I am on the "Openbook resource folder" "openbook activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field with xpath "//*[@id='id_allowsubmissionsfromdate_enabled']" to "0"
    And I set the field with xpath "//*[@id='id_duedate_enabled']" to "0"
    And I set the following fields to these values:
      | Files are personal | No (files can be shared between students) |
      | Teacher approval   | Automatic                                 |
      | Student approval   | Automatic                                 |
    And I press "Save and display"
    And I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I click on "Edit/upload files" "button"
    And I should see "Own files"
    And I upload "mod/openbook/tests/fixtures/teacher_file.pdf" file to "Own files" filemanager
    And I press "Save changes"
    Then I should see "teacher_file.pdf"

  @javascript @_file_upload
  Scenario: As student, view a file a lecturer has uploaded in a openbook instance
    When I am on the "Openbook resource folder" "openbook activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field with xpath "//*[@id='id_allowsubmissionsfromdate_enabled']" to "0"
    And I set the field with xpath "//*[@id='id_duedate_enabled']" to "0"
    And I set the following fields to these values:
      | Files are personal | No (files can be shared between students) |
      | Teacher approval   | Automatic                                 |
      | Student approval   | Automatic                                 |
    And I press "Save and display"
    And I am on the "Openbook resource folder" "openbook activity" page
    And I follow "Edit/upload teacher files"
    And I upload "mod/openbook/tests/fixtures/teacher_file.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student1
    And I should not see "Edit/upload teacher files"
    And I click on "Edit/upload files" "button"
    And I upload "mod/openbook/tests/fixtures/student_file_private.pdf" file to "Own files" filemanager
    And I press "Save changes"
    Then I should see "student_file_private.pdf"
    Then I should see "teacher_file.pdf"

  @javascript @_file_upload
  Scenario: Upload file as teacher 1 and another file as teacher 2 in a openbook instance
    When I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I should see "Teacher files"
    And I follow "Edit/upload teacher files"
    And I should see "Teacher files that are visible to everybody"
    And I upload "mod/openbook/tests/fixtures/teacher_file.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    And I should see "teacher_file.pdf"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as teacher2
    And I should see "Own files"
    And I should see "Teacher files"
    And I follow "Edit/upload teacher files"
    And I should see "Teacher files"
    And I upload "mod/openbook/tests/fixtures/teacher_file_2.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    Then I should see "teacher_file.pdf"
    And I should see "teacher_file_2.pdf"

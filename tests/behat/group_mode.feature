@mod @mod_openbook @_file_upload
Feature: I can use Openbook resource folder groupwise with separate groups
  In order to support students in exams that are groupwise
  As a teacher
  I can provide students documents based on their separate groups

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group A | C1     | G1       |
      | Group B | C1     | G2       |
      | Group C | C1     | G3       |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | teacher1 | G1    |
      | student2 | G2    |
      | teacher2 | G2    |
      | student3 | G3    |
    And the following "activities" exist:
      | activity | course | name                     | maxbytes | filesarepersonal | teacherapproval | groupmode |
      | openbook | C1     | Openbook resource folder | 8388608  | 1                | 0               | 1         |

  @javascript @_file_upload
  Scenario: Upload file to openbook resource folder as teacher 1 in group A and view it as student
    When I am on the "Openbook resource folder" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I should see "Teacher files"
    And I follow "Edit/upload teacher files"
    And I should see "Teacher files that are visible to everybody"
    And I upload "mod/openbook/tests/fixtures/teacher_file.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    And I should see "teacher_file.pdf"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student1
    And I should see "teacher_file.pdf"
    And I log out
    And I am on the "Openbook resource folder" "openbook activity" page logged in as student2
    Then I should not see "teacher_file.pdf"

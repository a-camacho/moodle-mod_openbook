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
  Scenario: Click on Give Approval button in privatestudentfolder instance in course1
    Given I am on the "PVS1" "privatestudentfolder activity" page logged in as teacher1
    And I wait until the page is ready
    And I wait "2" seconds
    And I change viewport size to "1920x1080"
    #Then I click on "Give approval" "link"
    Then I click on "//a[contains(text(), 'Give approval')]" "xpath"
    And I wait "2" seconds
    And I wait until the page is ready
    Then I should see "File submissions"
    
  Scenario: Upload file in privatestudentfolder instance in course1
    Given I am on the "PVS1" "privatestudentfolder activity" page logged in as teacher1
    And I wait until the page is ready
    And I wait "2" seconds
    And I change viewport size to "1920x1080"
    And I wait "3" seconds
    
    Then I click on "//input[@id='id_gotoupload' and @type='submit']" "xpath_element"
    #When I press "Edit/upload files"
    #And I execute script "document.getElementById('id_gotoupload').click();"
    
    #And I wait until the page is ready
    #And I wait "2" seconds
    #And I wait until the filemanager "id_attachment_filemanager_fieldset" is ready
    #And I upload "tests/fixtures/empty.txt" file to "id_attachment_filemanager_fieldset" filemanager
    #And I press "id_savebutton"
    #Then I should see "empty.txt"
    #Then I click on "Give approval" "link"
    #And I wait until the page is ready
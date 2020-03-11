Feature: Security

  Scenario: Login

    Given the following users exists:
      | id         | email        | pass       |
      | 111-222-33 | john@doe.com | testtttt1Q |
    Given I add "Content-Type" header equal to "application/x-www-form-urlencoded"
    Given I send a "POST" request to "/login" with parameters:
      | key           | value        |
      | username      | john@doe.com |
      | password      | testtttt1Q   |
#    Then I should be on the homepage


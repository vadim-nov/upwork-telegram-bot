Feature: Telegram - remove search

  Scenario: Remove search request
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following searches exist:
      | id         | user_id    | name | search_url     |
      | 111-222-33 | 111-222-33 | mobx | https://upwork |
    Given I send a message to bot with text "/remove"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Select search to remove
    """

  Scenario: Remove search complete
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following searches exist:
      | id         | user_id    | name | search_url     |
      | 111-222-33 | 111-222-33 | mobx | https://upwork |
    Given I send a message to bot with callback query "/remove 111-222-33"
    Then the response status code should be 200
    Then user "111-222-33" should have 0 settings
    And the telegram message should contain:
    """
    Search "mobx" is successfully removed
    """

  Scenario: Cancel removing search
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following searches exist:
      | id         | user_id    | name      | search_url     |
      | 111-222-33 | 111-222-33 | test_name | https://upwork |
    Given I send a message to bot with callback query "/remove cancel"
    Then the response status code should be 200
    Then user "111-222-33" should have 1 settings

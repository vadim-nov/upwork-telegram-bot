Feature: Telegram

  Scenario: Start bot
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given I send a message to bot with text "/start"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    I'm Upwork bot.
    """
    And the telegram message log with text "/start" should exist


  Scenario: Add search first step
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given I send a message to bot with text "/add"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Paste the upwork search URL or just enter a keyword
    """

  Scenario: Add search URL
    Given the following users exists:
      | id         | telegram_ref | current_step    |
      | 111-222-33 | 569776780    | added_new_seach |
    Given I send a message to bot with text "https://www.upwork.com/search/jobs/?sort=recency"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Now, give it a name, please.
    """
    And user "111-222-33" should have 1 settings

  Scenario: Add search name and get notification
    Given the following users exists:
      | id         | telegram_ref | current_step      |
      | 111-222-33 | 569776780    | added_search_link |
    Given the following searches exist:
      | id         | user_id    | search_url                                         |
      | 111-222-33 | 111-222-33 | https://www.upwork.com/search/jobs/?sort=recency |
    Given upwork response loaded from "jobs-laravel.xml"
    Given I send a message to bot with text "mobx"
    Then user "111-222-33" should have 1 settings
    Then user "111-222-33" should have search with name "mobx"

  Scenario: Add search name duplicated
    Given the following users exists:
      | id         | telegram_ref | current_step      |
      | 111-222-33 | 569776780    | added_search_link |
    Given the following searches exist:
      | id         | user_id    | search_url     | name | isPending |
      | 111-222-33 | 111-222-33 | https://upwork | mobx | 0         |
    Given I send a message to bot with text "mobx"
    And the telegram message should contain:
    """
    Search <b>mobx</b> already exists.
    """
    And user "111-222-33" should have 1 settings
    And the telegram message log with text "mobx" should exist

  Scenario: Add search stop words and get notification
    Given the following users exists:
      | id         | telegram_ref | current_step      |
      | 111-222-33 | 569776780    | added_search_name |
    Given the following searches exist:
      | id         | user_id    | search_url                                         | name |
      | 111-222-33 | 111-222-33 | https://www.upwork.com/search/jobs/?sort=recency | mobx |
    Given upwork response loaded from "jobs-laravel.xml"
    Given I send a message to bot with text "django"
    And the telegram message should contain:
    """
    We need to manipulate the standard sorting in Laravel Nova
    """
    And the telegram message should NOT contain:
    """
    Django
    """
    And user "111-222-33" should have 1 settings

  Scenario: Search URL invalid format
    Given the following users exists:
      | id         | telegram_ref | current_step    |
      | 111-222-33 | 569776780    | added_new_seach |
    Given I send a message to bot with text "https://wrong-upwork-url.com"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Invalid Upwork search link. Try again please.
    """

  Scenario: Searches limit reached
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following searches exist:
      | id         | user_id    | name      | search_url     |
      | 111-222-33 | 111-222-33 | test_name | https://upwork |
    Given I send a message to bot with text "/add"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Oh, you can have only 1 search link, remove one to replace with something else. Or get supernatural power with our /upgrade.
    """

  Scenario: Searches limit reached with standard plan
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following searches exist:
      | id         | user_id    | name      | search_url             |
      | 111-222-33 | 111-222-33 | test_name | https://upwork/?q=test |
      | 111-222-34 | 111-222-33 | mobx      | https://upwork/?q=mobx |
    Given the following plans exists:
      | id | name     | search_count | update_frequency | money  | is_visible |
      | 1  | Starter  | 1            | 10               | $5.00  | 1          |
      | 2  | Standard | 2            | 5                | $10.00 | 1          |
      | 3  | Premium  | 3            | 1                | $15.00 | 1          |
    Given the following orders exists:
      | id | user       | plan | is_paid | amount_paid |
      | 50 | 111-222-33 | 2    | 1       | $10.00      |
    Given I send a message to bot with text "/add"
    Then the response status code should be 200
    And the telegram message should contain:
    """
    Oh, you can have only 2 search links, remove one to replace with something else. Or get supernatural power with our /upgrade.
    """

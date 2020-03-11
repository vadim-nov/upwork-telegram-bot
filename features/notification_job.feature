Feature: Notification job

  Background:
    Given upwork response loaded from 'jobs-laravel.xml'

  Scenario: telegram:notify free plan
    Given the following users exists:
      | id         | telegram_ref | email         |
      | 111-222-33 | 569776780    |               |
    Given the following searches exist:
      | id         | user_id    | name      | search_url                                         |
      | 111-222-33 | 111-222-33 | test_name | https://www.upwork.com/search/jobs/?sort=recency |
    Given I run command "telegram:notify"
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Notification job is dispatched
    """
    And the telegram message should contain:
    """
    We need to manipulate the standard sorting in Laravel Nova
    """
    And the telegram should send 30 messages
    And upwork job with link "https://www.upwork.com/jobs/Help-with-Laravel-Nova_%7E017db4904b9af76aac?source=rss" should exists

  Scenario: telegram:notify free plan WEB-USERS
    Given the following users exists:
      | id         | telegram_ref | email         |
      | 222-333-44 |              | test@test.com |
    Given the following searches exist:
      | id         | user_id    | name      | search_url                                      |
      | 111-222-33 | 222-333-44 | test_name | https://www.upwork.com/search/jobs/?q=laravel |
    Given I run command "telegram:notify"
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Notification job is dispatched
    """
    And the telegram should send 0 messages
    And upwork job with link "https://www.upwork.com/jobs/Help-with-Laravel-Nova_%7E017db4904b9af76aac?source=rss" should exists

  Scenario: telegram:notify starter plan
    Given the following plans exists:
      | id | name    | search_count | update_frequency | money | is_visible |
      | 1  | Starter | 1            | 10               | $5.00 | 1          |
    Given the following users exists:
      | id         | telegram_ref | email         | plan_id |
      | 111-222-33 | 569776780    |               | 1       |
    Given the following searches exist:
      | id         | user_id    | name      | search_url                                         |
      | 111-222-33 | 111-222-33 | test_name | https://www.upwork.com/search/jobs/?sort=recency |
    Given I run command "telegram:notify" with:
      | --plan  |
      | Starter |
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Notification job is dispatched
    """
    And the telegram message should contain:
    """
    We need to manipulate the standard sorting in Laravel Nova
    """
    And the telegram should send 30 messages

  Scenario: telegram:notify starter plan WEB-USERS
    Given the following plans exists:
      | id | name    | search_count | update_frequency | money | is_visible |
      | 1  | Starter | 1            | 10               | $5.00 | 1          |
    Given the following users exists:
      | id         | telegram_ref | email         | plan_id |
      | 222-333-44 |              | test@test.com | 1       |
    Given the following searches exist:
      | id         | user_id    | name      | search_url                                      |
      | 111-222-33 | 222-333-44 | test_name | https://www.upwork.com/search/jobs/?q=laravel |
    Given I run command "telegram:notify" with:
      | --plan  |
      | Starter |
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Notification job is dispatched
    """
    And the telegram should send 0 messages
    And upwork job with link "https://www.upwork.com/jobs/Help-with-Laravel-Nova_%7E017db4904b9af76aac?source=rss" should exists


  Scenario: telegram:notify with no users for selected plan
    Given the following plans exists:
      | id | name    | search_count | update_frequency | money | is_visible |
      | 1  | Starter | 1            | 10               | $5.00 | 1          |
    Given the following users exists:
      | id         | telegram_ref | email         |
      | 111-222-33 | 569776780    |               |
    Given the following searches exist:
      | id         | user_id    | name      | search_url                                         |
      | 111-222-33 | 111-222-33 | test_name | https://www.upwork.com/search/jobs/?sort=recency |
    Given I run command "telegram:notify" with:
      | --plan  |
      | Starter |
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Notification job is dispatched
    """
    And the telegram should send 0 messages

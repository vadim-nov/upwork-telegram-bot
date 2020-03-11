Feature: Telegram job help lost users

  Scenario: run command telegram:help
    Given the following users exists:
      | id         | telegram_ref | email         |
      | 111-222-33 | 569776780    |               |
    Given the following telegram log messages exist:
      | id         | user_id    | is_inbound | text  | created_at   |
      | 111-222-33 | 111-222-33 | 1          | start | now - 31 min |
    Given I run command "telegram:help"
    Then the command should finish successfully
    And I should see command output:
    """
    [OK] Helping lost users job is dispatched
    """
    And the telegram message should contain:
    """
    Feel free to contact @telerodion 🙋‍♂️
    """
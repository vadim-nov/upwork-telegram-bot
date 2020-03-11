Feature: Telegram login

  Scenario: Telegram login success with existing telegram user
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 415318291    |
    Given I send a "GET" request to "/clb/telegram_login" with parameters:
      | key        | value                                                            |
      | id         | 415318291                                                        |
      | hash       | dfbfa9cdf0a013d412b59044dc05dea5143b9a5742bb6b3576163ea865d4742f |
      | auth_date  | 1536485875                                                       |
      | first_name | Egor                                                             |
      | last_name  | Gorbachev                                                        |
      | photo_url  | https://t.me/i/userpic/320/egorvn.jpg                            |
      | username   | egorvn                                                           |
    Then the response status code should be 302
    Then user should have count 1

  Scenario: Telegram login with new user (register using telegram)
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 111111111    |
    Given I send a "GET" request to "/clb/telegram_login" with parameters:
      | key        | value                                                            |
      | id         | 415318291                                                        |
      | hash       | dfbfa9cdf0a013d412b59044dc05dea5143b9a5742bb6b3576163ea865d4742f |
      | auth_date  | 1536485875                                                       |
      | first_name | Egor                                                             |
      | last_name  | Gorbachev                                                        |
      | photo_url  | https://t.me/i/userpic/320/egorvn.jpg                            |
      | username   | egorvn                                                           |
    Then the response status code should be 302
    Then user should have count 2

  Scenario: Telegram login failed because signature mismatch
    Given I send a "GET" request to "/clb/telegram_login" with parameters:
      | key        | value                                                            |
      | id         | 888888888                                                        |
      | hash       | dfbfa9cdf0a013d412b59044dc05dea5143b9a5742bb6b3576163ea865d4742f |
      | auth_date  | 1536485875                                                       |
      | first_name | Egor                                                             |
      | last_name  | Gorbachev                                                        |
      | photo_url  | https://t.me/i/userpic/320/egorvn.jpg                            |
      | username   | egorvn                                                           |
    Then the response status code should be 400
    Then user should have count 0

Feature: Payment

  Scenario: Request upgrade
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
      | 4   | Hidden   | 3            | 1                | $1.00  | 0          |
    Given I send a message to bot with text "/upgrade"
    Then the response status code should be 200
    And the telegram message should contain:
    """
Current plan: <b>Free</b>
Select your plan:
<b>Starter</b> - $5.00 / month, 1 search, frequency 10
<b>Standard</b> - $10.00 / month, 2 searches, frequency 5
<b>Premium</b> - $15.00 / month, 3 searches, frequency 1
    """

  Scenario: Dev user can see hidden subscriptions
    Given the following users exists:
      | id         | telegram_ref | is_dev |
      | 111-222-33 | 569776780    | 1      |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
      | 4   | Hidden   | 9            | 1                | $1.00  | 0          |
    Given I send a message to bot with text "/upgrade"
    Then the response status code should be 200
    And the telegram message should contain:
    """
Current plan: <b>Free</b>
Select your plan:
<b>Starter</b> - $5.00 / month, 1 search, frequency 10
<b>Standard</b> - $10.00 / month, 2 searches, frequency 5
<b>Premium</b> - $15.00 / month, 3 searches, frequency 1
<b>Hidden</b> - $1.00 / month, 9 searches, frequency 1
    """

  Scenario: Place an order
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
    Given I send a message to bot with callback query "/upgrade Starter"
    And the telegram message should contain:
    """
    Order successfully placed.
    """
    And user "111-222-33" should have 1 unpaid orders
    And user "111-222-33" should have plan null

  Scenario: Place an order for hidden plan
    Given the following users exists:
      | id         | telegram_ref | is_dev |
      | 111-222-33 | 569776780    | 1      |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
      | 4   | Hidden   | 3            | 1                | $1.00  | 0          |
    Given I send a message to bot with callback query "/upgrade Hidden"
    And the telegram message should contain:
    """
    Order successfully placed.
    """
    And user "111-222-33" should have 1 unpaid orders
    And user "111-222-33" should have plan null

  Scenario: Pay order success
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
    Given the following orders exists:
      | id  | user       | plan |
      | 50  | 111-222-33 | 1    |
    Given I send a "GET" request to "/clb/payeer/success" with parameters:
      | key                  | value         |
      | m_operation_id       | 12435         |
      | m_operation_ps       | 12345         |
      | m_operation_date     | 2019-04-18    |
      | m_operation_pay_date | 2019-04-18    |
      | m_shop               | 12345         |
      | m_orderid            | 50            |
      | m_amount             | 5             |
      | m_curr               | USD           |
      | m_desc               | Order ID: 5   |
      | m_status             | success       |
      | m_sign               | F5DCB3F86DAA2A0C8A660C9070197CC80F4AAD80ECC3C5DB59EB369A180356AC |
    Then the response status code should be 200
    And order #"50" should be paid
    And user "111-222-33" should have plan 1

  Scenario: Pay order failed with corrupted data (signature mismatch)
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
    Given the following orders exists:
      | id  | user       | plan |
      | 50  | 111-222-33 | 1    |
    Given I send a "GET" request to "/clb/payeer/success" with parameters:
      | key                  | value         |
      | m_operation_id       | 12435         |
      | m_operation_ps       | 12345         |
      | m_operation_date     | 2019-04-18    |
      | m_operation_pay_date | 2019-04-18    |
      | m_shop               | 12345         |
      | m_orderid            | 50            |
      | m_amount             | 1000          |
      | m_curr               | USD           |
      | m_desc               | Order ID: 5   |
      | m_status             | success       |
      | m_sign               | F5DCB3F86DAA2A0C8A660C9070197CC80F4AAD80ECC3C5DB59EB369A180356AC |
    Then the response status code should be 400
    Then user "111-222-33" should have 1 unpaid orders
    Then user "111-222-33" should have plan null

  Scenario: Pay order failed because money is not enough
    Given the following users exists:
      | id         | telegram_ref |
      | 111-222-33 | 569776780    |
    Given the following plans exists:
      | id  | name     | search_count | update_frequency | money  | is_visible |
      | 1   | Starter  | 1            | 10               | $5.00  | 1          |
      | 2   | Standard | 2            | 5                | $10.00 | 1          |
      | 3   | Premium  | 3            | 1                | $15.00 | 1          |
    Given the following orders exists:
      | id  | user       | plan |
      | 50  | 111-222-33 | 1    |
    Given I send a "GET" request to "/clb/payeer/success" with parameters:
      | key                  | value         |
      | m_operation_id       | 12435         |
      | m_operation_ps       | 12345         |
      | m_operation_date     | 2019-04-18    |
      | m_operation_pay_date | 2019-04-18    |
      | m_shop               | 12345         |
      | m_orderid            | 50            |
      | m_amount             | 1             |
      | m_curr               | USD           |
      | m_desc               | Order ID: 5   |
      | m_status             | success       |
      | m_sign               | 61A5EBC899AE936A8195D01E26BA14F24A00A981A220DBBCEA6CDA1772284890 |
    Then the response status code should be 400
    Then user "111-222-33" should have 1 unpaid orders
    Then user "111-222-33" should have plan null

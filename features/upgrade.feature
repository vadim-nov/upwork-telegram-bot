Feature: Upgrade plan

  Background:
    Given the date frozen at "2019-01-01"
    Given the following users exists:
      | id         | email        |
      | 111-222-33 | john@doe.com |
    Given the following plans exists:
      | id | name     | search_count | update_frequency | money  | is_visible |
      | 1  | Starter  | 1            | 10               | $5.00  | 1          |
      | 2  | Standard | 2            | 5                | $10.00 | 1          |
      | 3  | Premium  | 3            | 1                | $15.00 | 1          |
      | 4  | Hidden   | 3            | 1                | $1.00  | 0          |
    Given the following searches exist:
      | id         | user_id    | search_url     | name |
      | 111-222-33 | 111-222-33 | https://upwork | mobx |
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario:  Fetch plans
    Given I am authenticated as "john@doe.com"

    Given I send a "GET" request to "/api/plans"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/Plan",
    "@id": "\/api\/plans",
    "@type": "hydra:Collection",
    "hydra:member": [
        {
            "@id": "\/api\/plans\/1",
            "name": "Starter",
            "price": "$5.00",
            "searchCount": 1,
            "updateFrequency": 10,
            "isCurrent": false,
            "subTitle": "Every 10 mins update"
        },
        {
            "@id": "\/api\/plans\/2",
            "name": "Standard",
            "price": "$10.00",
            "searchCount": 2,
            "updateFrequency": 5,
            "isCurrent": false,
            "subTitle": "Every 5 mins update"
        },
        {
            "@id": "\/api\/plans\/3",
            "name": "Premium",
            "price": "$15.00",
            "searchCount": 3,
            "updateFrequency": 1,
            "isCurrent": false,
            "subTitle": "Every minute update"
        }
    ],
    "hydra:totalItems": 3
}
    """

  Scenario:  Upgrade starter
    Given I am authenticated as "john@doe.com"
    Given the uuid4 frozen at "c996e7da-73fa-43e4-90d3-a705427a4f1a"

    Given I send a "POST" request to "/api/orders" with body:
    """
{
    "planName": "Standard"
}
    """
    Then the response status code should be 201
    Then the JSON should be equal to:
    """
{
    "@context": {
        "@vocab": "http:\/\/localhost\/api\/docs.jsonld#",
        "hydra": "http:\/\/www.w3.org\/ns\/hydra\/core#",
        "url": "UpgradeRequestOutput\/url",
        "id": "UpgradeRequestOutput\/id",
        "price": "UpgradeRequestOutput\/price",
        "isPaid": "UpgradeRequestOutput\/isPaid",
        "plan": "UpgradeRequestOutput\/plan"
    },
    "@type": "Order",
    "@id": "\/api\/orders\/c996e7da-73fa-43e4-90d3-a705427a4f1a",
    "url": "https:\/\/payer.mock\/c996e7da-73fa-43e4-90d3-a705427a4f1a",
    "id": "c996e7da-73fa-43e4-90d3-a705427a4f1a",
    "price": "$10.00",
    "isPaid": false,
    "plan": "Standard"
}
    """

  Scenario:  Delete order
    Given I am authenticated as "john@doe.com"
    Given the uuid4 frozen at "c996e7da-73fa-43e4-90d3-a705427a4f1a"
    Given the following orders exists:
      | id | user       | plan | is_paid | amount_paid |
      | 50 | 111-222-33 | 2    | 1       | $10.00      |
    Given I send a "DELETE" request to "/api/orders/50"
    Then the response status code should be 204



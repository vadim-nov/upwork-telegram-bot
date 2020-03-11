Feature: User search api

  Background:
    Given the date frozen at "2019-01-01"
    Given the following users exists:
      | id         | email        |
      | 111-222-33 | john@doe.com |


    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario:  Fetch searches
    Given I am authenticated as "john@doe.com"
    Given the following searches exist:
      | id         | user_id    | search_url     | name |
      | 111-222-33 | 111-222-33 | https://upwork | mobx |
    Given I send a "GET" request to "/api/user_searches"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UserSearch",
    "@id": "\/api\/user_searches",
    "@type": "hydra:Collection",
    "hydra:member": [
        {
            "@id": "\/api\/user_searches\/111-222-33",
            "@type": "UserSearch",
            "id": "111-222-33",
            "searchName": "mobx",
            "stopWords": [],
            "searchUrl": "https:\/\/upwork"
        }
    ],
    "hydra:totalItems": 1
}
    """

  Scenario: Create search
    Given I am authenticated as "john@doe.com"
    Given upwork response loaded from "jobs-laravel.xml"
    Given the uuid4 frozen at "0ad252f6-da24-4ded-ba03-2a6987361111"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "https://www.upwork.com/search/jobs/?q=react",
    "searchName": "react"
}
    """
    Then the response status code should be 201
    And upwork job with link "https://www.upwork.com/jobs/Migrate-our-wesbites-from-PHp-PHP_%7E01824e9d1ce27ac757?source=rss" should exists
    And the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UserSearch",
    "@id": "\/api\/user_searches\/0ad252f6-da24-4ded-ba03-2a6987361111",
    "@type": "UserSearch",
    "id": "0ad252f6-da24-4ded-ba03-2a6987361111",
    "searchName": "react",
    "stopWords": [],
    "searchUrl": "https:\/\/www.upwork.com\/search\/jobs\/?q=react"
}
    """

  Scenario: Create search with auto-suggest name
    Given I am authenticated as "john@doe.com"
    Given upwork response loaded from "jobs-laravel.xml"
    Given the uuid4 frozen at "0ad252f6-da24-4ded-ba03-2a6987361111"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "https://www.upwork.com/search/jobs/?q=react",
    "searchName": null,
    "stopWordsJson": null
}
    """
    Then the response status code should be 201
    And upwork job with link "https://www.upwork.com/jobs/Migrate-our-wesbites-from-PHp-PHP_%7E01824e9d1ce27ac757?source=rss" should exists
    And the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UserSearch",
    "@id": "\/api\/user_searches\/0ad252f6-da24-4ded-ba03-2a6987361111",
    "@type": "UserSearch",
    "id": "0ad252f6-da24-4ded-ba03-2a6987361111",
    "searchName": "react",
    "stopWords": [],
    "searchUrl": "https:\/\/www.upwork.com\/search\/jobs\/?q=react"
}
    """

  Scenario: Create search with bad query [1]
    Given I am authenticated as "john@doe.com"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "https://www.upwork.com/o/jobs/bro",
    "searchName": "react"
}
    """
    Then the response status code should be 400
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/ConstraintViolationList",
    "@type": "ConstraintViolationList",
    "hydra:title": "An error occurred",
    "hydra:description": "searchUrl: Invalid Upwork search link. Try again please.",
    "violations": [
        {
            "propertyPath": "searchUrl",
            "message": "Invalid Upwork search link. Try again please."
        }
    ]
}
    """

  Scenario: Create search with bad req
    Given I am authenticated as "john@doe.com"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "",
    "searchName": ""
}
    """
    Then the response status code should be 400
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/ConstraintViolationList",
    "@type": "ConstraintViolationList",
    "hydra:title": "An error occurred",
    "hydra:description": "searchName: This value should not be blank.\nsearchUrl: Invalid Upwork search link. Try again please.\nsearchUrl: This value should not be blank.",
    "violations": [
        {
            "propertyPath": "searchName",
            "message": "This value should not be blank."
        },
        {
            "propertyPath": "searchUrl",
            "message": "Invalid Upwork search link. Try again please."
        },
        {
            "propertyPath": "searchUrl",
            "message": "This value should not be blank."
        }
    ]
}
    """

  Scenario: Create search with simple q
    Given upwork response loaded from "jobs-laravel.xml"
    Given I am authenticated as "john@doe.com"
    Given the uuid4 frozen at "0ad252f6-da24-4ded-ba03-2a6987361111"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "react",
    "searchName": ""
}
    """
    Then the response status code should be 201
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UserSearch",
    "@id": "\/api\/user_searches\/0ad252f6-da24-4ded-ba03-2a6987361111",
    "@type": "UserSearch",
    "id": "0ad252f6-da24-4ded-ba03-2a6987361111",
    "searchName": "react",
    "stopWords": [],
    "searchUrl": "https://www.upwork.com/search/jobs/?q=react"
}
    """

  Scenario: Create search with ab search format
    Given upwork response loaded from "jobs-laravel.xml"
    Given I am authenticated as "john@doe.com"
    Given the uuid4 frozen at "0ad252f6-da24-4ded-ba03-2a6987361111"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "https://www.upwork.com/ab/jobs/search/?q=ClickFunnels&sort=recency",
    "searchName": ""
}
    """
    Then the response status code should be 201
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UserSearch",
    "@id": "\/api\/user_searches\/0ad252f6-da24-4ded-ba03-2a6987361111",
    "@type": "UserSearch",
    "id": "0ad252f6-da24-4ded-ba03-2a6987361111",
    "searchName": "ClickFunnels",
    "stopWords": [],
    "searchUrl": "https:\/\/www.upwork.com\/search\/jobs\/?q=ClickFunnels&sort=recency"
}
    """

  Scenario: Can't create search for free plan (user already has one)
    Given the following searches exist:
      | id         | user_id    | search_url     | name |
      | 111-222-33 | 111-222-33 | https://upwork | mobx |
    Given I am authenticated as "john@doe.com"
    Given the uuid4 frozen at "0ad252f6-da24-4ded-ba03-2a6987361111"
    Given I send a "POST" request to "/api/user_searches" with body:
    """
{
    "searchUrl": "https://www.upwork.com/search/jobs/?q=react",
    "searchName": "react"
}
    """
    Then the response status code should be 400
    Then the JSON should be equal to:
    """
    {
    "@context": "\/api\/contexts\/ConstraintViolationList",
    "@type": "ConstraintViolationList",
    "hydra:title": "An error occurred",
    "hydra:description": "searchUrl: You can have only 1 search link(s). Please upgrade your Plan to add more.",
    "violations": [
        {
            "propertyPath": "searchUrl",
            "message": "You can have only 1 search link(s). Please upgrade your Plan to add more."
        }
    ]
}
    """

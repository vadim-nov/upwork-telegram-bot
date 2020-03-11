Feature: Upwork jobs api

  Background:
    Given the date frozen at "2019-01-01"
    Given the following users exists:
      | id         | email        |
      | 111-222-33 | john@doe.com |
    Given the following searches exist:
      | id         | user_id    | search_url     | name |
      | 111-222-33 | 111-222-33 | https://upwork | mobx |
    Given the following upwork jobs exists:
      | id       | link                                | title | pubDate    | search_id  |
      | 1111-111 | https://www.upwork.com/jobs/Laravel | Lara  | 2019-01-01 | 111-222-33 |
      | 1111-112 | https://www.upwork.com/jobs/CI      | CI    | 2019-01-01 | 111-222-33 |

    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario:  Fetch jobs without Bearer
    Given I send a "GET" request to "/api/upwork_jobs"
    Then the response status code should be 401

  Scenario:  Fetch jobs
    Given I am authenticated as "john@doe.com"
    Given I send a "GET" request to "/api/upwork_jobs"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
{
   "@context":"\/api\/contexts\/UpworkJob",
   "@id":"\/api\/upwork_jobs",
   "@type":"hydra:Collection",
   "hydra:member":[
        {
            "@id": "\/api\/upwork_jobs\/1111-111",
            "@type": "UpworkJob",
            "id": "1111-111",
            "link": "https:\/\/www.upwork.com\/jobs\/Laravel",
            "pubDate": "2019-01-01T00:00:00+00:00",
            "createdAt": "2019-01-01T00:00:00+00:00",
            "isRead": false,
            "cleanedTitle": "Lara",
            "cleanedDescription": "Lara"
        },
        {
            "@id": "\/api\/upwork_jobs\/1111-112",
            "@type": "UpworkJob",
            "id": "1111-112",
            "link": "https:\/\/www.upwork.com\/jobs\/CI",
            "pubDate": "2019-01-01T00:00:00+00:00",
            "createdAt": "2019-01-01T00:00:00+00:00",
            "isRead": false,
            "cleanedTitle": "CI",
            "cleanedDescription": "CI"
        }
   ],
   "hydra:totalItems":2,
    "hydra:search": {
        "@type": "hydra:IriTemplate",
        "hydra:template": "\/api\/upwork_jobs{?pubDate[before],pubDate[strictly_before],pubDate[after],pubDate[strictly_after],isRead,userSearch,userSearch[],order[pubDate],order[isRead]}",
        "hydra:variableRepresentation": "BasicRepresentation",
        "hydra:mapping": [
            {
                "@type": "IriTemplateMapping",
                "variable": "pubDate[before]",
                "property": "pubDate",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "pubDate[strictly_before]",
                "property": "pubDate",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "pubDate[after]",
                "property": "pubDate",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "pubDate[strictly_after]",
                "property": "pubDate",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "isRead",
                "property": "isRead",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "userSearch",
                "property": "userSearch",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "userSearch[]",
                "property": "userSearch",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "order[pubDate]",
                "property": "pubDate",
                "required": false
            },
            {
                "@type": "IriTemplateMapping",
                "variable": "order[isRead]",
                "property": "isRead",
                "required": false
            }
        ]
    }
}
    """

  Scenario:  Mark job as read
    Given I am authenticated as "john@doe.com"
    Given I send a "PUT" request to "/api/upwork_jobs/1111-111" with body:
    """
{
    "isRead": true
}
    """
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UpworkJob",
    "@id": "\/api\/upwork_jobs\/1111-111",
    "@type": "UpworkJob",
    "id": "1111-111",
    "link": "https:\/\/www.upwork.com\/jobs\/Laravel",
    "pubDate": "2019-01-01T00:00:00+00:00",
    "createdAt": "2019-01-01T00:00:00+00:00",
    "isRead": true,
    "cleanedTitle": "Lara",
    "cleanedDescription": "Lara"
}
    """

  Scenario:  Mark job as removed
    Given I am authenticated as "john@doe.com"
    Given I send a "PUT" request to "/api/upwork_jobs/1111-111" with body:
    """
{
    "isRemoved": true
}
    """
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
{
    "@context": "\/api\/contexts\/UpworkJob",
    "@id": "\/api\/upwork_jobs\/1111-111",
    "@type": "UpworkJob",
    "id": "1111-111",
    "link": "https:\/\/www.upwork.com\/jobs\/Laravel",
    "pubDate": "2019-01-01T00:00:00+00:00",
    "createdAt": "2019-01-01T00:00:00+00:00",
    "isRead": false,
    "cleanedTitle": "Lara",
    "cleanedDescription": "Lara"
}
    """

  Scenario:  Mark all jobs as read
    Given I am authenticated as "john@doe.com"
    Given I send a "POST" request to "/api/upwork_jobs/mark-all-as-read"
    Then the response status code should be 200

Feature: Homepage

  Scenario:  Visit homepage
    Given I send a "GET" request to "/"
    Then the response status code should be 200
    And the response should contain "Get freelance jobs and actual reviews smoothly. Apply faster than others."

  Scenario: Contact form
    Given I send a "POST" request to "/contact" with body:
    """
    {"body": "test", "email": "test@test.com", "name": "John"}
    """
    Then the response status code should be 200

@api
Feature: SKOS language mapping feature
  In order to work with multilingual vocabularies
  As a site administrator
  I should be able to configure language mapping between RDF SKOS and Drupal langcodes

  Scenario: Configuration form for saving mapping between RDF SKOS and enabled languages.
    Given I am logged in as a user with the "administer skos concept entities" permissions
    When these languages are available:
      | languages |
      | en        |
      | fr        |
      | pt-pt     |
    And I go to "the SKOS language mapping page"
    Then the "RDF SKOS English (en)" field should contain "en"
    And the "RDF SKOS French (fr)" field should contain "fr"
    # If we don't have configuration, we use first 2 characters of langcode.
    And the "RDF SKOS Portuguese, Portugal (pt-pt)" field should contain "pt"
    # Make sure that validation work properly.
    When I fill in "RDF SKOS English (en)" with "fr"
    And I press "Save configuration"
    Then I should see the following error messages:
      | error messages                               |
      | The langcode for English, fr, is not unique. |
      | The langcode for French, fr, is not unique.  |
    # Make sure that saving of configuration through UI work as expected.
    When I fill in "RDF SKOS English (en)" with "en"
    When I fill in "RDF SKOS Portuguese, Portugal (pt-pt)" with "pt-pt"
    And I press "Save configuration"
    Then I should see the success message "The configuration options have been saved."
    And the "RDF SKOS Portuguese, Portugal (pt-pt)" field should contain "pt-pt"
    And I fill in "RDF SKOS Portuguese, Portugal (pt-pt)" with "pt"
    And I press "Save configuration"
    And the "RDF SKOS Portuguese, Portugal (pt-pt)" field should contain "pt"
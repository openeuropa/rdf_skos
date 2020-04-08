<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\Tests\rdf_skos\Traits\SkosEntityReferenceTrait;
use Drupal\Tests\rdf_skos\Traits\SkosImportTrait;

/**
 * Test the Skos concept select list widget.
 */
class SkosConceptSelectListWidgetTest extends BrowserTestBase {

  use RdfDatabaseConnectionTrait;
  use SkosImportTrait;
  use SkosEntityReferenceTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'rdf_entity',
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp();
    $this->setUpSparql();
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    $this->import($base_url, $this->sparql, 'phpunit');
    $this->enableGraph('fruit');

    $this->drupalCreateContentType([
      'type' => 'article',
    ]);

    $this->createSkosConceptReferenceField(
      'node',
      'article',
      ['http://example.com/fruit'],
      'field_fruit_reference',
      'Fruit',
      'skos_concept_entity_reference_options_select'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    $this->clear($base_url, $this->sparql, 'phpunit');

    parent::tearDown();
  }

  /**
   * Tests the Skos Concept Reference Select list widget.
   */
  public function testSkosConceptSelectList(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'view published skos concept scheme entities',
      'view published skos concept entities',
      'bypass node access',
    ]));
    $this->drupalGet('/node/add');
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Fruit', 'Apple');
    $page->fillField('Title', 'Set Fruit in select box');
    $page->pressButton('Save');
    $this->assertSession()->elementTextContains('css', '.messages--status', 'Article Set Fruit in select box has been created.');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->drupalGetNodeByTitle('Set Fruit in select box');
    // Make sure that field value is saved properly.
    $this->assertEquals('http://example.com/fruit/apple', $node->get('field_fruit_reference')->target_id);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertSession()->fieldValueEquals('Fruit', 'http://example.com/fruit/apple');
  }

}

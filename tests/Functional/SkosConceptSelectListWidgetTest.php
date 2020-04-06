<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\Tests\rdf_skos\Traits\SkosImportTrait;

/**
 * Test the Skos concept select list widget.
 */
class SkosConceptSelectListWidgetTest extends BrowserTestBase {

  use RdfDatabaseConnectionTrait;
  use SkosImportTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
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

    $this->createSkosConceptReferenceField();
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
    $this->drupalGet('node/add');
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Fruit', 'Apple');
    $page->fillField('Title', 'Set Fruit in select box');
    $page->pressButton('Save');
    $this->assertSession()->elementTextContains('css', '.messages--status', 'Article Set Fruit in select box has been created.');
  }

  /**
   * Creates the Skos Concept reference field on the Article node type.
   */
  protected function createSkosConceptReferenceField(): void {
    $handler_settings = [
      'target_bundles' => NULL,
      'auto_create' => FALSE,
      'concept_schemes' => [
        'http://example.com/fruit',
      ],
      'field' => [
        'field_name' => 'field_fruit_reference',
        'entity_type' => 'node',
        'bundle' => 'article',
        'concept_schemes' => [
          'http://example.com/fruit',
        ],
      ],
    ];

    FieldStorageConfig::create([
      'field_name' => 'field_fruit_reference',
      'type' => 'skos_concept_entity_reference',
      'entity_type' => 'node',
      'cardinality' => 1,
      'settings' => [
        'target_type' => 'skos_concept',
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_fruit_reference',
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Fruit',
      'settings' => [
        'handler' => 'default:skos_concept',
        'handler_settings' => $handler_settings,
      ],
    ])->save();

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('node.article.default');

    $form_display->setComponent('field_fruit_reference', [
      'type' => 'skos_concept_entity_reference_options_select',
      'region' => 'content',
    ]);
    $form_display->save();
  }

}

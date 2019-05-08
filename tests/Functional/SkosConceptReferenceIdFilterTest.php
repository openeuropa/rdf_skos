<?php

namespace Drupal\Tests\rdf_skos\Functional;

use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;
use Drupal\Tests\rdf_skos\Traits\SkosImportTrait;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Entity\View;
use Drupal\views\Tests\ViewTestData;

/**
 * Test the taxonomy term index filter.
 *
 * @see \Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid
 *
 * @group taxonomy
 */
class SkosConceptReferenceIdFilterTest extends ViewTestBase {

  use RdfDatabaseConnectionTrait;
  use SkosImportTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'node',
    'rdf_entity',
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  public static $testViews = [''];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE) {
    parent::setUp(FALSE);
    $this->setUpSparql();
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    $this->import($base_url, $this->sparql, 'phpunit');

  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    $this->clear($base_url, $this->sparql, 'phpunit');

    parent::tearDown();
  }

}

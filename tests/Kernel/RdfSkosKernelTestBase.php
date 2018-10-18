<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;
use Drupal\Tests\rdf_skos\Traits\SkosImportTrait;

/**
 * Base class for the SKOS Kernel Tests.
 */
class RdfSkosKernelTestBase extends RdfKernelTestBase {

  use SkosImportTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

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

  /**
   * Configures the SKOS entities to read from a certain graph.
   *
   * @param string $name
   *   The name of the graph.
   */
  protected function enableGraph(string $name) {
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    $info = $this->getTestGraphInfo($base_url, 'phpunit');
    $graph = $info[$name];
    $config = $this->config('rdf_skos.graphs')->get('entity_types');
    $config['skos_concept_scheme'][] = [
      'name' => $name,
      'uri' => $graph['uri'],
    ];

    $config['skos_concept'][] = [
      'name' => $name,
      'uri' => $graph['uri'],
    ];

    $this->config('rdf_skos.graphs')->set('entity_types', $config)->save();
  }

}

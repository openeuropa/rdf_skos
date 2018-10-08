<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Kernel;

use Drupal\rdf_entity\Entity\Query\Sparql\SparqlArg;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;
use EasyRdf\Graph;

/**
 * Base class for the SKOS Kernel Tests.
 */
class RdfSkosKernelTestBase extends RdfKernelTestBase {

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

    $graphs = $this->getTestGraphInfo();

    // Import the test data into Sparql.
    foreach ($graphs as $info) {
      $graph = new Graph($info['data']);
      $graph->load();
      $graph_uri = SparqlArg::uri($info['uri']);
      $query = "INSERT DATA INTO $graph_uri {\n";
      $query .= $graph->serialise('ntriples') . "\n";
      $query .= '}';
      $this->sparql->update($query);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $graphs = $this->getTestGraphInfo();
    foreach ($graphs as $info) {
      $query = <<<EndOfQuery
DELETE {
  GRAPH <{$info['uri']}> {
    ?entity ?field ?value
  }
}
WHERE {
  GRAPH <{$info['uri']}> {
    ?entity ?field ?value
  }
}
EndOfQuery;
    }

    $this->sparql->query($query);
    parent::tearDown();
  }

  /**
   * Configures the SKOS entities to read from a certain graph.
   *
   * @param string $name
   *   The name of the graph.
   */
  protected function enableGraph(string $name) {
    $info = $this->getTestGraphInfo();
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

  /**
   * Returns the info about where the test RDF data is.
   *
   * @return array
   *   The graph info.
   */
  protected function getTestGraphInfo() {
    $base_url = $_ENV['SIMPLETEST_BASE_URL'];
    return [
      'fruit' => [
        'uri' => 'http://example.com/fruit',
        'data' => "$base_url/modules/custom/rdf_skos/tests/test_rdf/fruit.rdf",
      ],
      'vegetables' => [
        'uri' => 'http://example.com/vegetables',
        'data' => "$base_url/modules/custom/rdf_skos/tests/test_rdf/vegetables.rdf",
      ],
    ];
  }

}

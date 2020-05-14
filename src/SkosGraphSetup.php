<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service used to set up the SKOS graphs.
 */
class SkosGraphSetup {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CountrySkosGraphSetup constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Sets up the graphs.
   */
  public function setup(array $graphs = []): void {
    if (empty($graphs)) {
      continue;
    }

    $config = $this->configFactory->getEditable('rdf_skos.graphs')->get('entity_types');

    foreach ($graphs as $name => $graph) {
      $config['skos_concept_scheme'][] = [
        'name' => $name,
        'uri' => $graph,
      ];

      $config['skos_concept'][] = [
        'name' => $name,
        'uri' => $graph,
      ];
    }

    $this->configFactory->getEditable('rdf_skos.graphs')->set('entity_types', $config)->save();
  }

}

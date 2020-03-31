<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched to alter the processed graph results.
 */
class SkosProcessGraphResultsEvent extends Event {

  /**
   * Event name to alter the processed graph results for SKOS entities.
   */
  const ALTER = 'rdf_skos_process_graph_results.alter';

  /**
   * The processed results.
   *
   * @var array
   */
  protected $results = [];

  /**
   * Returns the processed results.
   *
   * @return array
   *   The results.
   */
  public function getResults(): array {
    return $this->results;
  }

  /**
   * Sets the processed results.
   *
   * @param array $results
   *   The results.
   */
  public function setResults(array $results): void {
    $this->results = $results;
  }

}

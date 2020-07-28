<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Represents a concept subset plugin manager.
 */
interface ConceptSubsetPluginManagerInterface extends PluginManagerInterface {

  /**
   * Returns all the plugin definitions that map predicates to Drupal fields.
   *
   * @return array
   *   The definitions.
   */
  public function getPredicateMappingDefinitions(): array;

  /**
   * Returns all the plugin definitions apply to a concept scheme.
   *
   * @param array $concept_schemes
   *   The  selected concept scheme IDs.
   *
   * @return array
   *   The definitions.
   */
  public function getApplicableDefinitionsDefinitions(array $concept_schemes): array;

}

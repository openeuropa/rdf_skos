<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * ConceptSubset plugin manager.
 */
class ConceptSubsetPluginManager extends DefaultPluginManager implements ConceptSubsetPluginManagerInterface {

  /**
   * Constructs ConceptSubsetPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ConceptSubset',
      $namespaces,
      $module_handler,
      'Drupal\rdf_skos\ConceptSubsetInterface',
      'Drupal\rdf_skos\Annotation\ConceptSubset'
    );
    $this->alterInfo('concept_subset_info');
    $this->setCacheBackend($cache_backend, 'concept_subset_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicateMappingDefinitions(): array {
    $definitions = $this->getDefinitions();
    $predicate_mappers = [];
    foreach ($definitions as $id => $definition) {
      if (isset($definition['predicate_mapping']) && (bool) $definition['predicate_mapping']) {
        $predicate_mappers[$id] = $definition;
      }
    }

    return $predicate_mappers;
  }

}

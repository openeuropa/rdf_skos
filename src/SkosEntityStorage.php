<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\rdf_entity\Entity\RdfEntitySparqlStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Storage class for SKOS entities.
 */
class SkosEntityStorage extends RdfEntitySparqlStorage {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('sparql_endpoint'),
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('module_handler'),
      $container->get('rdf_skos.sparql.graph_handler'),
      $container->get('rdf_skos.sparql.field_handler'),
      $container->get('plugin.manager.rdf_entity.id'),
      $container->has('entity.memory_cache') ? $container->get('entity.memory_cache') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getQueryServiceName(): string {
    return 'rdf_skos.entity.query.sparql';
  }

  /**
   * {@inheritdoc}
   */
  protected function processGraphResults($results, array $graph_ids): ?array {
    $return = parent::processGraphResults($results, $graph_ids);
    if ($this->bundleKey === '' && $return) {
      foreach ($return as &$values) {
        unset($values['']);
      }
    }

    return $return;
  }

}

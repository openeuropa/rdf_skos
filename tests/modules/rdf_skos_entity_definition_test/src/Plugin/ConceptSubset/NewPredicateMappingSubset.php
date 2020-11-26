<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos_entity_definition_test\Plugin\ConceptSubset;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\rdf_entity\RdfFieldHandlerInterface;
use Drupal\rdf_skos\ConceptSubsetPluginBase;
use Drupal\rdf_skos\Plugin\PredicateMapperInterface;

/**
 * Test plugin that maps a new value to a new base field.
 *
 * @ConceptSubset(
 *   id = "new_predicate_mapping",
 *   label = @Translation("New predicate mapping"),
 *   description = @Translation("Maps a new value to a new base field."),
 *   predicate_mapping = TRUE
 * )
 */
class NewPredicateMappingSubset extends ConceptSubsetPluginBase implements PredicateMapperInterface {

  /**
   * {@inheritdoc}
   */
  public function alterQuery(QueryInterface $query, $match_operator, array $concept_schemes = [], string $match = NULL): void {
    // We don't need to alter the query for this test.
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicateMapping(): array {
    $mapping = [];

    $mapping['new_dummy_title'] = [
      'column' => 'value',
      'predicate' => ['http://www.w3.org/2004/02/skos/core#dummy'],
      'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
    ];

    return $mapping;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFieldDefinitions(): array {
    $fields = [];

    $fields['new_dummy_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('A new dummy title'))
      ->setDescription(t('A dummy title added to the definition after installation of RDF Skos.'));

    return $fields;
  }

}

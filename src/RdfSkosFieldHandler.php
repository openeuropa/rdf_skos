<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\rdf_entity\Exception\NonExistingFieldPropertyException;
use Drupal\rdf_entity\RdfFieldHandler;
use Drupal\rdf_entity\RdfFieldHandlerInterface;
use Drupal\rdf_skos\Event\SkosPredicateMappingEvent;

/**
 * RDF field handler for SKOS entities.
 */
class RdfSkosFieldHandler extends RdfFieldHandler {

  /**
   * Event name dispatched to gather predicate mappings for SKOS entities.
   */
  const PREDICATE_MAPPING_EVENT = 'rdf_skos_field_handler.predicate_mapping';

  /**
   * {@inheritdoc}
   */
  protected function buildEntityTypeProperties($entity_type_id): void {
    if (!empty($this->outboundMap[$entity_type_id]) && !empty($this->inboundMap[$entity_type_id])) {
      return;
    }

    $mapping = $this->getSkosPredicateMappings($entity_type_id);
    $this->outboundMap[$entity_type_id] = $this->inboundMap[$entity_type_id] = [];
    // We double up the entity type ID instead of the bundle.
    $this->outboundMap[$entity_type_id]['bundles'][$entity_type_id] = $mapping['rdf_type'];
    // We don't have bundles but a key is expected.
    $this->outboundMap[$entity_type_id]['bundle_key'] = NULL;
    $this->inboundMap[$entity_type_id]['bundles'][$mapping['rdf_type']][] = $entity_type_id;
    $field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($entity_type_id);

    foreach ($field_definitions as $field_name => $definition) {
      $field_mapping = $mapping['fields'][$field_name] ?? NULL;
      if (!$field_mapping) {
        continue;
      }

      $field_storage_definition = $definition->getFieldStorageDefinition();
      $this->outboundMap[$entity_type_id]['fields'][$field_name]['main_property'] = $field_storage_definition->getMainPropertyName();

      $property_definition = $field_storage_definition->getPropertyDefinition($field_mapping['column']);
      if (empty($property_definition)) {
        throw new NonExistingFieldPropertyException("Field '$field_name' of type '{$field_storage_definition->getType()}'' has no property '{$field_mapping['column']}'.");
      }
      $data_type = $property_definition->getDataType();

      if (is_array($field_mapping['predicate'])) {
        foreach ($field_mapping['predicate'] as $predicate) {
          $this->outboundMap[$entity_type_id]['fields'][$field_name]['columns'][$field_mapping['column']][$entity_type_id] = [
            'predicate' => $predicate,
            'format' => $field_mapping['format'],
            'serialize' => FALSE,
            'data_type' => $data_type,
          ];

          $this->inboundMap[$entity_type_id]['fields'][$predicate][$entity_type_id] = [
            'field_name' => $field_name,
            'column' => $field_mapping['column'],
            'serialize' => FALSE,
            'type' => $field_storage_definition->getType(),
            'data_type' => $data_type,
          ];
        }
      }
      else {
        $this->outboundMap[$entity_type_id]['fields'][$field_name]['columns'][$field_mapping['column']][$entity_type_id] = [
          'predicate' => $field_mapping['predicate'],
          'format' => $field_mapping['format'],
          'serialize' => FALSE,
          'data_type' => $data_type,
        ];

        $this->inboundMap[$entity_type_id]['fields'][$field_mapping['predicate']][$entity_type_id] = [
          'field_name' => $field_name,
          'column' => $field_mapping['column'],
          'serialize' => FALSE,
          'type' => $field_storage_definition->getType(),
          'data_type' => $data_type,
        ];
      }

    }
  }

  /**
   * Returns the predicate mapping for a given SKOS entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return array
   *   The field mapping.
   */
  protected function getSkosPredicateMappings(string $entity_type_id): array {
    $mapping = [
      'skos_concept_scheme' => [
        'rdf_type' => 'http://www.w3.org/2004/02/skos/core#ConceptScheme',
        'fields' => [
          'title' => [
            'column' => 'value',
            // We use more predicates because SKOS is quite flexible and there
            // are vocabularies which use other predicates for mapping the
            // ConceptScheme titles.
            'predicate' => [
              'http://purl.org/dc/terms/title',
              'http://www.w3.org/2004/02/skos/core#prefLabel',
              'http://www.w3.org/2000/01/rdf-schema#label',
            ],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'has_top_concept' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#hasTopConcept'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
        ],
      ],
      'skos_concept' => [
        'rdf_type' => 'http://www.w3.org/2004/02/skos/core#Concept',
        'fields' => [
          'pref_label' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#prefLabel'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'alt_label' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#altLabel'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'hidden_label' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#hiddenLabel'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'in_scheme' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#inScheme'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'definition' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#definition'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'example' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#example'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'scope_note' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#scopeNote'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'editorial_note' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#editorialNote'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'change_note' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#changeNote'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'history_note' => [
            'column' => 'value',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#historyNote'],
            'format' => RdfFieldHandlerInterface::TRANSLATABLE_LITERAL,
          ],
          'top_concept_of' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#topConceptOf'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'broader' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#broader'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'narrower' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#narrower'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'related' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#related'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'exact_match' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#exactMatch'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'close_match' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#closeMatch'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'broad_match' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#broadMatch'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'narrow_match' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#narrowMatch'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
          'related_match' => [
            'column' => 'target_id',
            'predicate' => ['http://www.w3.org/2004/02/skos/core#relatedMatch'],
            'format' => RdfFieldHandlerInterface::RESOURCE,
          ],
        ],
      ],
    ];

    $event = new SkosPredicateMappingEvent($entity_type_id);
    $event->setMapping($mapping[$entity_type_id]);
    $this->eventDispatcher->dispatch(self::PREDICATE_MAPPING_EVENT, $event);

    return $event->getMapping();
  }

}

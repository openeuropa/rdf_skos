<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Entity\EntityDefinitionUpdateManager;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Updates the definitions of the defined Skos concept fields.
 *
 * New Skos concept fields can be defined as part of ConceptSubset plugins so
 * whenever we have a new field, we need to use this service to update their
 * definition.
 */
class SkosEntityDefinitionUpdateManager {

  /**
   * The core entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * SkosConceptEntityDefinitionUpdateManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager
   *   The core entity definition update manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityDefinitionUpdateManager = $entityDefinitionUpdateManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * Installs all the field definitions.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return array
   *   The installed field names.
   */
  public function installFieldDefinitions(string $entity_type = 'skos_concept'): array {
    $installed = [];

    $change_list = $this->entityDefinitionUpdateManager->getChangeList();
    $changed_definitions = $change_list[$entity_type]['field_storage_definitions'] ?? [];
    if (!$changed_definitions) {
      return $installed;
    }

    $field_names = [];
    foreach ($changed_definitions as $field_name => $status) {
      if ($status === EntityDefinitionUpdateManager::DEFINITION_CREATED) {
        $field_names[] = $field_name;
      }
    }

    $all_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type);
    $definitions = [];
    foreach ($all_definitions as $name => $definition) {
      if (in_array($name, $field_names)) {
        $definitions[$name] = $definition;
      }
    }

    foreach ($definitions as $name => $definition) {
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition($name, $entity_type, $definition->getProvider(), $definition);
      $installed[] = $name;
    }

    return $installed;
  }

}

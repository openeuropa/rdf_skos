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
   * @param array $definitions
   *   The definitions to install.
   *
   * @return array
   *   The installed field names.
   */
  public function installFieldDefinitions(string $entity_type = 'skos_concept', array $definitions = []): array {
    $change_list = $this->entityDefinitionUpdateManager->getChangeList();
    $changed_definitions = $change_list[$entity_type]['field_storage_definitions'] ?? [];
    if (!$changed_definitions) {
      // If there are no fields to install, we bail out.
      return [];
    }

    // Get a list of all the field names that need to be installed.
    $field_names = [];
    foreach ($changed_definitions as $field_name => $status) {
      if ($status === EntityDefinitionUpdateManager::DEFINITION_CREATED) {
        $field_names[] = $field_name;
      }
    }

    if ($definitions) {
      // In case too many definitions were passed, filter out the ones that
      // don't need to be installed.
      $definitions = array_filter($definitions, function ($definition, $field_name) use ($field_names) {
        return in_array($field_name, $field_names);
      }, ARRAY_FILTER_USE_BOTH);

      return $this->doInstallDefinitions($entity_type, $definitions);
    }

    // If no definitions were passed, we use the current ones.
    $all_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type);
    foreach ($all_definitions as $name => $definition) {
      if (in_array($name, $field_names)) {
        $definitions[$name] = $definition;
      }
    }

    return $this->doInstallDefinitions($entity_type, $definitions);
  }

  /**
   * Installs a number of field definitions.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $definitions
   *   The array of definitions, keyed by field name.
   *
   * @return array
   *   The installed definitions.
   */
  protected function doInstallDefinitions(string $entity_type, array $definitions): array {
    $installed = [];
    foreach ($definitions as $name => $definition) {
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition($name, $entity_type, $definition->getProvider(), $definition);
      $installed[] = $name;
    }

    return $installed;
  }

}

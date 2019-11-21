<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\rdf_entity\Entity\RdfEntitySparqlStorage;
use Drupal\rdf_entity\RdfFieldHandlerInterface;
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

    $this->processGraphResultTranslations($return);
    return $return;
  }

  /**
   * {@inheritdoc}
   *
   * Ensure that if there are duplicate fields for the field that is mapped to
   * the bundle predicate, we use the correct one.
   */
  protected function getActiveBundle(array $entity_values): ?string {
    $bundle_predicates = $this->bundlePredicate;
    $bundles = [];
    foreach ($bundle_predicates as $bundle_predicate) {
      if (isset($entity_values[$bundle_predicate])) {
        $bundle_data = $entity_values[$bundle_predicate];
        foreach ($bundle_data[LanguageInterface::LANGCODE_DEFAULT] as $key => $uri) {
          try {
            $bundles += $this->fieldHandler->getInboundBundleValue($this->entityTypeId, $uri);
          }
          catch (\Exception $exception) {
            // We do nothing in this case.
            continue;
          }
        }
      }
    }

    if (empty($bundles)) {
      return NULL;
    }

    return reset($bundles);
  }

  /**
   * Processes the translations for the graph results.
   *
   * Since SKOS data does not have langcode information per "entity", we need
   * to construct the data the parent class expects to create translations
   * when hydrating the entities. We do this by checking all the translatable
   * literal fields and compile a list of translation languages across all
   * these fields. And we indicate that we want our generated entity to have
   * a translation of all these languages.
   *
   * Moreover, we populate for each field that have no translation value in a
   * certain language with defaults so that the resulting entity can have
   * fallback values when requested in certain language.
   *
   * @param array $results
   *   The results to process.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function processGraphResultTranslations(array &$results): void {
    $langcode_key = $this->getEntityType()->getKey('langcode');
    // Loop through each set of entity values and determine the languages
    // in which there are translations.
    foreach ($results as $id => $entity_values) {
      $translations = [];

      if (isset($entity_values[$langcode_key])) {
        // If the values already contain langcode information, do nothing.
        continue;
      }

      // Loop trough each individual translatable field and keep track of the
      // languages in which it has values.
      foreach ($entity_values as $field_name => $field_values) {
        if (!$this->isFieldTranslatable($field_name)) {
          continue;
        }

        $translations = array_merge($translations, array_keys($field_values));
      }

      // Skip the default language from the found translation languages.
      $translations = array_filter($translations, function ($langcode) {
        return $langcode !== LanguageInterface::LANGCODE_DEFAULT;
      });

      if (!$translations) {
        continue;
      }

      $results[$id][$langcode_key] = $this->prepareLangcodeValues($translations);

      // Go through each translation language and populate each field with
      // default values in each language where values are missing.
      foreach ($translations as $langcode) {
        foreach ($results[$id] as $field_name => $field_values) {
          if (!$this->isFieldTranslatable($field_name)) {
            continue;
          }

          if (!isset($field_values[$langcode]) && isset($field_values[LanguageInterface::LANGCODE_DEFAULT])) {
            $results[$id][$field_name][$langcode] = $field_values[LanguageInterface::LANGCODE_DEFAULT];
          }
        }
      }
    }
  }

  /**
   * Prepares the langcode values for the translation processing.
   *
   * Given an array of langcodes, prepare the values expected by
   * RdfEntitySparqlStorage::getFromStorage() to determine the entity
   * translations.
   *
   * @param array $langcodes
   *   The langcodes.
   *
   * @return array
   *   The values.
   */
  protected function prepareLangcodeValues(array $langcodes): array {
    $values = [];
    foreach ($langcodes as $langcode) {
      $values[$langcode] = [
        [
          'value' => $langcode,
        ],
      ];
    }

    return $values;
  }

  /**
   * Checks if a given field is translatable: is a translatable literal.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return bool
   *   Whether it is translatable or not.
   */
  protected function isFieldTranslatable(string $field_name): bool {
    try {
      $format = $this->fieldHandler->getFieldFormat($this->getEntityType()->id(), $field_name);
    }
    catch (\Exception $exception) {
      return FALSE;
    }

    $format = reset($format);
    return $format === RdfFieldHandlerInterface::TRANSLATABLE_LITERAL;
  }

}

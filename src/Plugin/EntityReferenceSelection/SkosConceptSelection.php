<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Selection plugin for SKOS Concepts.
 *
 * @EntityReferenceSelection(
 *   id = "default:skos_concept",
 *   label = @Translation("SKOS Concept selection"),
 *   entity_types = {"skos_concept"},
 *   group = "default",
 *   weight = 1
 * )
 */
class SkosConceptSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      // Empty array means allow all.
      'concept_schemes' => [],
        // In case the plugin is used in a reference field, we can store some
        // info about it.
      'field' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $configuration = $this->getConfiguration();
    $concept_scheme_ids = $this->entityManager->getStorage('skos_concept_scheme')->getQuery()
      ->execute();

    $form['concept_schemes'] = [
      '#type' => 'select',
      '#title' => $this->t('Concept Schemes'),
      '#description' => $this->t('Concept Schemes to filter by. Leave empty to allow all.'),
      '#options' => [],
      '#default_value' => array_values($configuration['concept_schemes']),
      '#multiple' => TRUE,
    ];

    if (empty($concept_scheme_ids)) {
      return $form;
    }

    /** @var \Drupal\rdf_skos\Entity\ConceptSchemeInterface[] $concept_schemes */
    $concept_schemes = $this->entityManager->getStorage('skos_concept_scheme')->loadMultiple($concept_scheme_ids);
    $options = [];
    foreach ($concept_schemes as $concept_scheme) {
      $options[$concept_scheme->id()] = $concept_scheme->getTitle();
    }

    $form['concept_schemes']['#options'] = $options;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::validateConfigurationForm($form, $form_state);

    $settings = $form_state->getValue('settings');
    $concept_schemes = $settings['handler_settings']['concept_schemes'];
    if ($concept_schemes) {
      $concept_schemes = array_values($concept_schemes);
      $settings['handler_settings']['concept_schemes'] = $concept_schemes;
    }

    // Add field information that can be used in the selection handler. This
    // comes from the actual SkosConceptEntityReferenceItem and we need it so
    // that the selection plugin query builder can receive this information.
    $field = $form_state->get('field');
    if ($field instanceof FieldConfigInterface) {
      $settings['handler_settings']['field'] = [
        'field_name' => $field->getName(),
        'entity_type' => $field->getTargetEntityTypeId(),
        'bundle' => $field->getTargetBundle(),
        'concept_schemes' => $concept_schemes,
      ];
    }

    $form_state->setValue('settings', $settings);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS'): QueryInterface {
    $query = parent::buildEntityQuery($match, $match_operator);
    $configuration = $this->getConfiguration();
    if (!empty($configuration['field'])) {
      // Allow query alterations when used for a reference field.
      $query->addTag('skos_concept_field_selection_plugin');
      $query->addMetaData('field', $configuration['field']);
    }

    $concept_schemes = $configuration['concept_schemes'];
    if (empty($concept_schemes)) {
      return $query;
    }
    $query->condition('in_scheme', $concept_schemes, 'IN');

    return $query;
  }

}

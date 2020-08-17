<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin for the 'skos_concept_entity_reference_options_select' widget.
 *
 * @FieldWidget(
 *   id = "skos_concept_entity_reference_options_select",
 *   label = @Translation("SKOS Concept select list"),
 *   description = @Translation("A select list widget for RDF SKOS Concepts."),
 *   field_types = {
 *     "skos_concept_entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class SkosConceptEntityReferenceOptionsSelectWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'order' => 'key',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['order'] = [
      '#type' => 'radios',
      '#title' => t('Select order'),
      '#default_value' => $this->getSetting('order'),
      '#options' => [
        'key' => $this->t('by Key'),
        'label' => $this->t('by Label'),
      ],
      '#description' => t('Select whether the options should be ordered by key or by label.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $order = $this->getSetting('order') == 'key' ? 'by Key' : 'by Label';
    $summary[] = t('Order: @order', ['@order' => $order]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getOptions(FieldableEntityInterface $entity) {
    $options = parent::getOptions($entity);
    $order = $this->getSetting('order');
    if ($order == 'key') {
      return $options;
    }

    // If we have an empty label take it out before sorting.
    if ($empty_label = $this->getEmptyLabel()) {
      array_shift($options);
    }
    asort($options);
    if ($empty_label) {
      $options = ['_none' => $empty_label] + $options;
    }
    return $options;
  }

}

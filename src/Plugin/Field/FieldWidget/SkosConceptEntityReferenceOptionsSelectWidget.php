<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Plugin\Field\FieldWidget;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, TransliterationInterface $transliteration, LanguageManagerInterface $language_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->transliteration = $transliteration;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('transliteration'),
      $container->get('language_manager')
    );
  }

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
    $language = $this->languageManager->getConfigOverrideLanguage() ?: $this->languageManager->getCurrentLanguage();
    uasort($options, function (string $a, string $b) use ($language): int {
      return $this->transliteration->transliterate($a, $language->getId()) <=> $this->transliteration->transliterate($b, $language->getId());
    });
    if ($empty_label) {
      $options = ['_none' => $empty_label] + $options;
    }
    return $options;
  }

}

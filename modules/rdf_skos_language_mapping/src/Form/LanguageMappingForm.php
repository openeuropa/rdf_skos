<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos_language_mapping\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure RDF SKOS language mapping settings for this site.
 */
class LanguageMappingForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new LanguageMappingForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rdf_skos_language_mapping_language_mapping';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rdf_skos_language_mapping.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rdf_skos_language_mapping.settings');

    $form['language_mapping'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#title' => $this->t('Language mapping'),
      '#open' => TRUE,
      '#description' => $this->t('Language mapping between the RDF SKOS and enabled languages of Drupal.'),
    ];

    $languages = $this->languageManager->getLanguages();
    $mapped_langcodes = $config->get('language_mapping');
    foreach ($languages as $langcode => $language) {
      $t_args = [
        '%language' => $language->getName(),
        '%langcode' => $language->getId(),
      ];

      $form['language_mapping'][$langcode] = [
        '#type' => 'textfield',
        '#title' => $this->t('RDF SKOS %language (%langcode)', $t_args),
        '#maxlength' => 64,
        '#default_value' => isset($mapped_langcodes[$langcode]) ? $mapped_langcodes[$langcode] : substr($langcode, 0, 2),
        '#required' => TRUE,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $languages = $this->languageManager->getLanguages();

    // Count repeated values for uniqueness check.
    $count = array_count_values($form_state->getValue('language_mapping'));
    foreach ($languages as $langcode => $language) {
      $value = $form_state->getValue(['language_mapping', $langcode]);
      if (isset($count[$value]) && $count[$value] > 1) {
        // Throw a form error if there are two languages with the same langcode.
        $form_state->setErrorByName("language_mapping][$langcode", $this->t('The langcode for %language, %value, is not unique.', ['%language' => $language->getName(), '%value' => $value]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('rdf_skos_language_mapping.settings')
      ->set('language_mapping', $form_state->getValue('language_mapping'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

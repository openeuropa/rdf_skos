<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos_language_mapping\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rdf_skos\Event\SkosProcessGraphResultsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * RDF SKOS language mapping event subscriber.
 */
class RdfSkosLanguageMappingSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new NodeAdminRouteSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
  }

  /**
   * Process of Graph Results handler.
   *
   * @param \Drupal\rdf_skos\Event\SkosProcessGraphResultsEvent $event
   *   Response event.
   */
  public function onProcessGraphResults(SkosProcessGraphResultsEvent $event) {
    $result = $event->getResults();

    if (empty($this->configFactory->get('rdf_skos_language_mapping.settings')->get('language_mapping'))) {
      return;
    }

    $concept_schema = $this->entityTypeManager->getStorage('skos_concept_scheme');
    $langcode_key = $concept_schema->getEntityType()->getKey('langcode');

    $mapping = array_flip($this->configFactory->get('rdf_skos_language_mapping.settings')->get('language_mapping'));
    foreach ($result as $id => $entity_values) {
      foreach ($entity_values as $field_name => $field_values) {
        foreach ($field_values as $langcode => $values) {
          if (array_key_exists($langcode, $mapping) && $langcode !== $mapping[$langcode]) {
            if ($field_name === $langcode_key) {
              // Replace value and key of langcode.
              $result[$id][$field_name][$langcode] = [['value' => $mapping[$langcode]]];
            }
            $result[$id][$field_name][$mapping[$langcode]] = $result[$id][$field_name][$langcode];
            unset($result[$id][$field_name][$langcode]);
          }
        }
      }
    }
    $event->setResults($result);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SkosProcessGraphResultsEvent::ALTER => ['onProcessGraphResults'],
    ];
  }

}

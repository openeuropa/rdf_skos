<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos_language_mapping\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Constructs a new NodeAdminRouteSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
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

    if (empty($this->configFactory->get('rdf_skos_language_mapping.settings')->get('mapping'))) {
      return;
    }

    $mapping = array_flip($this->configFactory->get('rdf_skos_language_mapping.settings')->get('mapping'));
    foreach ($result as $id => $entity_values) {
      foreach ($entity_values as $field_name => $field_values) {
        foreach ($field_values as $langcode => $values) {
          if (array_key_exists($langcode, $mapping)) {
            $result[$id][$field_name][$mapping[$langcode]] = $result[$id][$field_name][$langcode];
            unset($result[$id][$field_name][$langcode]);
          }
        }
      }
    }
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

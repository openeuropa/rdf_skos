<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rdf_entity\ActiveGraphEvent;
use Drupal\rdf_entity\Event\RdfEntityEvents;
use Drupal\rdf_entity\RdfGraphHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Ensure the correct graph is used for reading the SKOS entities.
 *
 * This is used when the SKOS entities are param converted.
 *
 * @see \Drupal\rdf_entity\ParamConverter\RdfEntityConverter
 */
class SkosActiveGraphSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The RDF graph handler service.
   *
   * @var \Drupal\rdf_entity\RdfGraphHandlerInterface
   */
  protected $rdfGraphHandler;

  /**
   * Constructs a new event subscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\rdf_entity\RdfGraphHandlerInterface $rdf_graph_handler
   *   The RDF graph handler service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RdfGraphHandlerInterface $rdf_graph_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->rdfGraphHandler = $rdf_graph_handler;
  }

  /**
   * Set the appropriate graph as an active graph for the SKOS entities.
   *
   * @param \Drupal\rdf_entity\ActiveGraphEvent $event
   *   The event object to process.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the access is denied and redirects to user login page.
   */
  public function graphForEntityConvert(ActiveGraphEvent $event): void {
    $defaults = $event->getRouteDefaults();
    if (!$defaults['_route']) {
      return;
    }
    $entity_type_id = $event->getEntityTypeId();
    if (!in_array($entity_type_id, ['skos_concept_scheme', 'skos_concept'])) {
      return;
    }

    // By default, we look in all graphs.
    $graphs = $this->rdfGraphHandler->getEntityTypeGraphIds($entity_type_id);
    $event->setGraphs($graphs);
    $event->stopPropagation();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RdfEntityEvents::GRAPH_ENTITY_CONVERT => ['graphForEntityConvert', 100],
    ];
  }

}

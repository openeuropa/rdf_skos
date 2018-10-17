<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\rdf_skos\Entity\ConceptInterface;
use Drupal\rdf_skos\Entity\ConceptSchemeInterface;

/**
 * View builder handler for the Concept Scheme entities.
 */
class ConceptSchemeViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $build_list): array {
    $build_list = parent::buildMultiple($build_list);

    // Render the top level concepts.
    foreach ($build_list as &$item) {
      if ($item['#view_mode'] !== 'full') {
        continue;
      }

      /** @var \Drupal\rdf_skos\Entity\ConceptSchemeInterface $concept_scheme */
      $concept_scheme = $item['#skos_concept_scheme'];
      $top = $this->getTopConcepts($concept_scheme);
      if (empty($top)) {
        continue;
      }

      $items = [];
      foreach ($top as $concept) {
        $items[] = $concept->toLink();
      }

      $item[] = [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Top level concepts'),
      ];

      $item[] = [
        '#type' => 'pager',
      ];
    }

    return $build_list;
  }

  /**
   * Returns the Concepts that are at the top level. Checking both directions.
   *
   * @param \Drupal\rdf_skos\Entity\ConceptSchemeInterface $concept_scheme
   *   The Concept Scheme.
   *
   * @return array|\Drupal\rdf_skos\Entity\ConceptInterface[]
   *   The Concept.
   */
  protected function getTopConcepts(ConceptSchemeInterface $concept_scheme): array {
    $concepts = $concept_scheme->getTopConcepts();
    $ids = $this->entityManager->getStorage('skos_concept')->getQuery()
      ->condition('top_concept_of', $concept_scheme->id())
      ->pager(30)
      ->execute();

    if (!$ids) {
      return $concepts;
    }

    $concepts = array_filter($concepts, function (ConceptInterface $concept) use ($ids) {
      return !in_array($concept->id(), $ids);
    });

    $concepts = array_merge($concepts, $this->entityManager->getStorage('skos_concept')->loadMultiple($ids));
    return $concepts;
  }

}

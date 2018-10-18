<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Kernel;

/**
 * Tests the RDF SKOS entities.
 */
class RdfSkosEntitiesKernelTest extends RdfSkosKernelTestBase {

  /**
   * Test that SKOS entities are not found without config.
   */
  public function testNoConfig(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $ids = $entity_type_manager->getStorage('skos_concept_scheme')->getQuery()
      ->execute();
    $this->assertEmpty($ids);

    $ids = $entity_type_manager->getStorage('skos_concept')->getQuery()
      ->execute();
    $this->assertEmpty($ids);
  }

  /**
   * Test that config exposes SKOS Concepts.
   */
  public function testConfigEnablesSkosEntities(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Configure to read from the fruits graph.
    $this->enableGraph('fruit');
    $ids = $entity_type_manager->getStorage('skos_concept_scheme')->getQuery()
      ->execute();
    $this->assertCount(1, $ids);
    $id = reset($ids);
    $this->assertEquals('http://example.com/fruit', $id);

    $ids = $entity_type_manager->getStorage('skos_concept')->getQuery()
      ->execute();
    $this->assertCount(6, $ids);
  }

  /**
   * Tests SKOS entities have data.
   */
  public function testSkosEntities(): void {
    $this->enableGraph('fruit');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $ids = $entity_type_manager->getStorage('skos_concept_scheme')->getQuery()
      ->execute();
    $concept_schemes = $entity_type_manager->getStorage('skos_concept_scheme')->loadMultiple($ids);
    $this->assertCount(1, $concept_schemes);
    /** @var \Drupal\rdf_skos\Entity\ConceptSchemeInterface $concept_scheme */
    $concept_scheme = reset($concept_schemes);
    $this->assertEquals('Fruit', $concept_scheme->getTitle());

    // Assert top concepts.
    $top_concepts = $concept_scheme->getTopConcepts();
    $top_concept = reset($top_concepts);
    $this->assertEquals('Citrus fruit', $top_concept->getPreferredLabel());

    // Test concepts.
    $ids = $entity_type_manager->getStorage('skos_concept')->getQuery()
      ->condition('inScheme', $concept_scheme->id())
      ->execute();
    $concepts = $entity_type_manager->getStorage('skos_concept')->loadMultiple($ids);
    $this->assertCount(6, $concepts);
    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $citrus */
    $citrus = $concepts['http://example.com/fruit/citrus-fruit'];
    $this->assertEquals('Citrus fruit ALT', $citrus->getAlternateLabel());
    $this->assertEquals('Citrus fruit HIDDEN', $citrus->getHiddenLabel());
    $this->assertEquals('lemons, oranges, limes, mandarines, grapefruit, satsumas', $citrus->getExample());
    $this->assertEquals('Fruit that make you salivate sometimes', $citrus->getDefinition());

    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $lemon */
    $lemon = $concepts['http://example.com/fruit/lemon'];
    $this->assertEquals('Citrus fruit', $lemon->getBroader()[0]->getPreferredLabel());

    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $pear */
    $pear = $concepts['http://example.com/fruit/pear'];
    $this->assertEquals('Apple', $pear->getRelated()[0]->getPreferredLabel());

    $concepts = $entity_type_manager->getStorage('skos_concept')->loadByProperties(['pref_label' => 'Exotic fruit']);
    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $concept */
    $concept = reset($concepts);
    $this->assertEquals('Fruit', $concept->topConceptOf()[0]->getTitle());
    $this->assertEquals('Fruit', $concept->getConceptSchemes()[0]->getTitle());
    $this->assertEquals('Banana', $concept->getNarrower()[0]->getPreferredLabel());
  }

  /**
   * Tests that SKOS entities can be read from multiple graphs.
   */
  public function testMultipleGraphs(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->enableGraph('fruit');
    $this->enableGraph('vegetables');

    $ids = $entity_type_manager->getStorage('skos_concept_scheme')->getQuery()
      ->execute();
    $this->assertCount(2, $ids);
    $ids = $entity_type_manager->getStorage('skos_concept')->getQuery()
      ->execute();
    $this->assertCount(7, $ids);
  }

}

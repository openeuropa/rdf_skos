<?php

declare(strict_types = 1);

namespace Drupal\Tests\rdf_skos\Kernel;

use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;

/**
 * Tests the SKOS entity reference field.
 */
class RdfSkosEntityReferenceTest extends RdfSkosKernelTestBase {

  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable both graphs.
    $this->enableGraph('fruit');
    $this->enableGraph('vegetables');

    $graphs = $this->getTestGraphInfo();
    $fruit = $graphs['fruit'];

    // Create a reference field to Fruit.
    $this->createEntityReferenceField(
      'entity_test',
      'entity_test',
      'field_fruit',
      'Fruit',
      'skos_concept',
      'default',
      [
        'concept_schemes' => [
          $fruit['uri'],
        ],
      ]
    );

    // Create a reference field to Fruit and Vegetables.
    $vegetables = $graphs['vegetables'];
    $this->createEntityReferenceField(
      'entity_test',
      'entity_test',
      'field_fruits_veggies',
      'Fruits and Vegetables',
      'skos_concept',
      'default',
      [
        'concept_schemes' => [
          $fruit['uri'],
          $vegetables['uri'],
        ],
      ]
    );
  }

  /**
   * Tests the SKOS Concept reference fields.
   */
  public function testReferenceFields(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    /** @var \Drupal\entity_test\Entity\EntityTest $entity */
    $entity = $entity_type_manager->getStorage('entity_test')
      ->create(['type' => 'entity_test']);

    // The fruit field should only reference fruit concepts.
    $entity->set('field_fruit', 'http://example.com/fruit/apple');
    $violations = $entity->field_fruit->validate();
    $this->assertCount(0, $violations);
    $entity->set('field_fruit', 'http://example.com/vegetables/potato');
    $violations = $entity->field_fruit->validate();
    $this->assertCount(1, $violations);
    $this->assertEquals($violations[0]->getMessage(), t('This entity (%type: %id) cannot be referenced.', ['%type' => 'skos_concept', '%id' => 'http://example.com/vegetables/potato']));

    // The fruits_veggies field should be able to reference both.
    $entity->set('field_fruits_veggies', 'http://example.com/fruit/apple');
    $violations = $entity->field_fruits_veggies->validate();
    $this->assertEquals($violations->count(), 0);
    $entity->set('field_fruits_veggies', 'http://example.com/vegetables/potato');
    $violations = $entity->field_fruits_veggies->validate();
    $this->assertCount(0, $violations);
  }

}

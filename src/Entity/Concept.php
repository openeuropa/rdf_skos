<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the SKOS Concept entity.
 *
 * @ContentEntityType(
 *   id = "skos_concept",
 *   label = @Translation("SKOS Concept"),
 *   handlers = {
 *     "storage" = "Drupal\rdf_skos\SkosEntityStorage",
 *     "list_builder" = "Drupal\rdf_skos\ConceptListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\rdf_skos\Form\ConceptForm",
 *       "add" = "Drupal\rdf_skos\Form\ConceptForm",
 *       "edit" = "Drupal\rdf_skos\Form\ConceptForm",
 *       "delete" = "Drupal\rdf_skos\Form\ConceptDeleteForm",
 *     },
 *     "access" = "Drupal\rdf_skos\ConceptAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\rdf_skos\ConceptHtmlRouteProvider",
 *     },
 *   },
 *   base_table = null,
 *   translatable = TRUE,
 *   admin_permission = "administer skos concept entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "prefLabel",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/skos_concept/{skos_concept}",
 *     "add-form" = "/admin/structure/skos_concept/add",
 *     "edit-form" = "/admin/structure/skos_concept/{skos_concept}/edit",
 *     "delete-form" = "/admin/structure/skos_concept/{skos_concept}/delete",
 *     "collection" = "/admin/structure/skos_concept",
 *   },
 *   field_ui_base_route = "skos_concept"
 * )
 */
class Concept extends ContentEntityBase implements ConceptInterface {

  /**
   * {@inheritdoc}
   */
  public function getPreferredLabel(): ?string {
    return $this->get('prefLabel')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreferredLabel(string $label): ConceptInterface {
    $this->set('prefLabel', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlternateLabel(): ?string {
    return $this->get('altLabel')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAlternateLabel(string $label): ConceptInterface {
    $this->set('altLabel', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHiddenLabel(): ?string {
    return $this->get('hiddenLabel')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setHiddenLabel(string $label): ConceptInterface {
    $this->set('hiddenLabel', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition(): ?string {
    return $this->get('definition')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopeNote(): ?string {
    return $this->get('scopeNote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getHistoryNote(): ?string {
    return $this->get('historyNote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditorialNote(): ?string {
    return $this->get('editorialNote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangeNote(): ?string {
    return $this->get('changeNote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getExample(): ?string {
    return $this->get('example')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getConceptSchemes(): array {
    return $this->get('inScheme')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setConceptSchemes(array $concept_schemes): ConceptInterface {
    $this->set('inScheme', $concept_schemes);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function topConceptOf(): array {
    return $this->get('topConceptOf')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getBroader(): array {
    return $this->get('broader')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getNarrower(): array {
    return $this->get('narrower')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getRelated(): array {
    return $this->get('related')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getExactMatch(): array {
    return $this->get('exactMatch')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getCloseMatch(): array {
    return $this->get('closeMatch')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getBroadMatch(): array {
    return $this->get('broadMatch')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getNarrowMatch(): array {
    return $this->get('narrowMatch')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedMatch(): array {
    return $this->get('relatedMatch')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Change the ID field from integer to string.
    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('ID'))
      ->setDescription(t('The SKOS Concept ID.'))
      ->setReadOnly(TRUE);

    $fields['prefLabel'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Preferred Label'))
      ->setDescription(t('The preferred label of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'sting',
      ]);

    $fields['altLabel'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Alternate Label'))
      ->setDescription(t('The alternate label of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['hiddenLabel'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hidden Label'))
      ->setDescription(t('The hidden label of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['definition'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Definition'))
      ->setDescription(t('The definition of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['example'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Example'))
      ->setDescription(t('The example of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['scopeNote'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Scope Note'))
      ->setDescription(t('The scope note of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['editorialNote'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Editorial Note'))
      ->setDescription(t('The editorial note of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['changeNote'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Change Note'))
      ->setDescription(t('The change note of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['historyNote'] = BaseFieldDefinition::create('string')
      ->setLabel(t('History Note'))
      ->setDescription(t('The history note of the Concept.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'sting',
      ]);

    $fields['inScheme'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('In Scheme'))
      ->setDescription(t('The Concept Schemes this Concept belongs to.'))
      ->setSetting('target_type', 'skos_concept_scheme')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['topConceptOf'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Top Concept Of'))
      ->setDescription(t('The Concept Schemes this Concept is the top concept of.'))
      ->setSetting('target_type', 'skos_concept_scheme')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['broader'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Broader Concepts'))
      ->setDescription(t('The broader Concepts.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['narrower'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Narrower Concepts'))
      ->setDescription(t('The narrower Concepts.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['related'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related Concepts'))
      ->setDescription(t('The related Concepts.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['exactMatch'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Exact Match Concepts'))
      ->setDescription(t('The exact match Concepts from other Concept Schemes.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['closeMatch'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Close Match Concepts'))
      ->setDescription(t('The close match Concepts from other Concept Schemes.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['broadMatch'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Broad Match Concepts'))
      ->setDescription(t('The broader Concepts from other Concept Schemes.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['narrowMatch'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Narrow Match Concepts'))
      ->setDescription(t('The narrower Concepts from other Concept Schemes.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    $fields['relatedMatch'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related Match Concepts'))
      ->setDescription(t('The related Concepts from other Concept Schemes.'))
      ->setSetting('target_type', 'skos_concept')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ]);

    return $fields;
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for SKOS Concept edit forms.
 */
class ConceptForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label SKOS Concept.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label SKOS Concept.', [
          '%label' => $entity->label(),
        ]));
    }

    $form_state->setRedirect('entity.skos_concept.canonical', ['skos_concept' => $entity->id()]);
  }

}

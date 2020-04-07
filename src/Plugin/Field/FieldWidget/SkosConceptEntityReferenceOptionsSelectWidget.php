<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;

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
class SkosConceptEntityReferenceOptionsSelectWidget extends OptionsSelectWidget {}

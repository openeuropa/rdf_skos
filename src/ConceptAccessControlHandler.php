<?php

declare(strict_types = 1);

namespace Drupal\rdf_skos;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the SKOS Concept entity.
 *
 * @see \Drupal\rdf_skos\Entity\Concept.
 */
class ConceptAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\rdf_skos\Entity\ConceptInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view published skos concept entities');

      // For the moment, we only allow read.
      case 'update':
        return AccessResult::forbidden();

      case 'delete':
        return AccessResult::forbidden();
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResultInterface {
    // For the moment, we only allow read.
    return AccessResult::forbidden();
  }

}

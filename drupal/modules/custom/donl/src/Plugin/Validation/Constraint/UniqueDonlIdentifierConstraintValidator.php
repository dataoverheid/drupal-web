<?php

namespace Drupal\donl\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueDonlIdentifier constraint.
 */
class UniqueDonlIdentifierConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    // This is a single-item field so we only need to validate the first.
    $item = $items->first();

    $id = NULL;
    if ($node = $item->getEntity()) {
      $id = $node->id();
    }

    if ($item && \Drupal::service('donl_search.request')->checkIdentifierUsage($item->value, $id)) {
      $this->context->addViolation($constraint->alreadyInUse, ['%identifier' => $item->value]);
    }
  }

}

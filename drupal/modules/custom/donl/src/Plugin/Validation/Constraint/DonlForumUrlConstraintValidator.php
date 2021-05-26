<?php

namespace Drupal\donl\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DonlForumUrlConstraint constraint.
 */
class DonlForumUrlConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $url = $item->getValue();
      if (!preg_match('/https?:\/\/(www\.)?((geoforum.nl)|(.*.datacommunities.nl))(\/.*)?/', $url)) {
        $this->context->addViolation($constraint->invalidLink, ['%url' => $url]);
      }
    }
  }

}

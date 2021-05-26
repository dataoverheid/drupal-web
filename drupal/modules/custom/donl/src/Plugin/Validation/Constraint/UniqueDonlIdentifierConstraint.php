<?php

namespace Drupal\donl\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the identifier is unique.
 *
 * @Constraint(
 *   id = "UniqueDonlIdentifier",
 *   label = @Translation("Unique DONL Identifier", context = "Validation"),
 * )
 */
class UniqueDonlIdentifierConstraint extends Constraint {

  public $alreadyInUse = 'The given identifier %identifier is already in use.';

}

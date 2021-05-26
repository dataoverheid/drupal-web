<?php

namespace Drupal\donl\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the identifier is unique.
 *
 * @Constraint(
 *   id = "DonlForumUrlConstraint",
 *   label = @Translation("DONL forum URL", context = "Validation"),
 * )
 */
class DonlForumUrlConstraint extends Constraint {

  public $invalidLink = 'The given url %url is not a valid forum link.';

}

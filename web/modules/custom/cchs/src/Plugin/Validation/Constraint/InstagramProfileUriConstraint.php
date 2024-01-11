<?php

namespace Drupal\cchs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a unique integer.
 *
 * @Constraint(
 *   id = "InstagramProfileUriConstraint",
 *   label = @Translation("Instagram profile URI validator", context = "Validation"),
 *   type = "string"
 * )
 */

class InstagramProfileUriConstraint extends Constraint {

  public $notValid = '%value is not a valid and clean Instagram profile URI';

}

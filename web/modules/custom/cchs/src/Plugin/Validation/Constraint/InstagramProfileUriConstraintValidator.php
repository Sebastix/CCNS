<?php

namespace Drupal\cchs\Plugin\Validation\Constraint;

use Drupal\link\Plugin\Field\FieldType\LinkItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the InstagramProfileUri constraint.
 */
class InstagramProfileUriConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint)
  {
    /** @var LinkItem $item */
    foreach ($value as $item) {
      // validate the uri string in $item with regex
      // TODO
      $uri = $item->getUrl()->getUri();
      $regex = '/(?:(?:http|https):\/\/)?(?:www\.)?(?:instagram\.com|instagr\.am)\/([A-Za-z0-9-_\.]+)\/?$/im';
      preg_match($regex, $uri, $matches);
      if (isset($matches[1])) {
        $match = $matches[1];
        if ($match == 'p') {
          // This is a post

        }
        if ($match == 'stories') {
          // This is a story
        }
        if ($match == 'reel') {
          // This is a reel

        }
      } else {
        // if not valid:
        $this->context->addViolation($constraint->notValid, ['%value' => $uri]);
      }
    }
  }
}

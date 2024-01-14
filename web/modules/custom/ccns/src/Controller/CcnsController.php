<?php declare(strict_types = 1);

namespace Drupal\ccns\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for ccns routes.
 */
final class CcnsController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function __invoke(): array {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}

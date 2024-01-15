<?php declare(strict_types = 1);

namespace Drupal\ccns\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for ccns routes.
 */
final class CcnsController extends ControllerBase {
  public function post(): array
  {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

  public function login(): array
  {
    $build['content'] = [
      '#theme' => 'nostr_login',
    ];
    return $build;
  }

  public function loginModal(): AjaxResponse
  {
    $response = new AjaxResponse();
    $content = 'Content of login modal';
    $response->addCommand(new OpenModalDialogCommand('Modal title', $content, ['width' => 'auto', 'height' => 'auto']));
    return $response;
  }

}

<?php declare(strict_types = 1);

namespace Drupal\ccns\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenOffCanvasDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for ccns routes.
 */
final class CcnsController extends ControllerBase {
  public function post(): array
  {
    $build['content'] = [
      '#theme' => 'post',
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
    // @todo use a template file for the content of this modal
    $content = '<br /><button id="nostr-login-nip07" class="btn btn-warning">Login with Nostr extension</button>';
    // Add nostr-login library to the response which contains an event listeners for button#nostr-login-nip07.
    $attachments['library'][] = 'ccns/login-options';
    $response->setAttachments($attachments);
    $response->addCommand(new OpenOffCanvasDialogCommand('Login', $content, ['width' => '30%'], NULL, 'side'));
    return $response;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function createUser(Request $request): JsonResponse
  {
    $response = new JsonResponse();
    try {
      $postData = json_decode($request->getContent());
      // Check if user already exist.
      if ($user = user_load_by_mail($postData->npub.'@ccns.social')) {
        if(!$user->hasRole('ccns')){
          $ccns_role = Role::load('ccns');
          $user->addRole($ccns_role->id());
          $user->save();
        }
        // Check if username is still the same.
        if ($postData->profile->name !== $user->getAccountName()) {
          $user->setUsername($postData->profile->name);
          $user->save();
        }
      } else {
        $user = User::create();
        $user->setUsername($postData->profile->name); // This username must be unique and accept only [a-Z,0-9, - _ @].
        $user->setPassword('password');
        $user->setEmail($postData->npub.'@ccns.social');
        $user->set('field_npub', $postData->npub);
        $user->enforceIsNew();
        $user->activate();
        $user->save();
        // Add role and save user.
        $ccns_role = Role::load('ccns');
        $user->addRole($ccns_role->id());
        $user->save();
      }
      // login the user.
      if (!\Drupal::currentUser()->id()) {
        user_login_finalize($user);
      }
      $responseData = [
        'userid' => $user->id()
      ];
      $response->setData($responseData);
    } catch (EntityStorageException $e) {

    }
    return $response;
  }

}

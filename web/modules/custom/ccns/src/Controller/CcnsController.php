<?php declare(strict_types = 1);

namespace Drupal\ccns\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenOffCanvasDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\file\Entity\File;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use GuzzleHttp\Exception\GuzzleException;
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
    $content = '<br />';
    $content .= '<button id="nostr-login-nip07" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; cursor: pointer;">Log in with a Nostr extension</button>';
    $content .= '<br /><br />';
    $content .= '<button id="nostr-login-bunker" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; opacity: .25;">Log in with a nsecbunker</button>';
    $content .= '<br /><br />';
    $content .= '<button id="nostr-login-nsec" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; opacity: .25;">Log in with a nsec key</button>';
    // Add nostr-login library to the response which contains an event listeners for these buttons.
    $attachments['library'][] = 'ccns/login-options';
    $response->setAttachments($attachments);
    $response->addCommand(new OpenOffCanvasDialogCommand('Login', $content, ['width' => '30%'], NULL, 'side'));
    return $response;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws EntityStorageException
   * @throws GuzzleException
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
        // Set fields.
        $user->set('field_npub', $postData->npub);
        // Add role and save user.
        $ccns_role = Role::load('ccns');
        $user->addRole($ccns_role->id());
        $user->enforceIsNew();
        $user->activate();
        $user->save();

        // Download avatar file.
        $client = \Drupal::httpClient();
        $source_uri = $postData->profile->image;
        if (!mkdir('sites/default/files/nostr-avatars/') && !is_dir('sites/default/files/nostr-avatars/')) {
          throw new \RuntimeException(sprintf('Directory "%s" was not created', 'sites/default/files/nostr_avatars/'));
        }
        $file_extension = pathinfo($source_uri, PATHINFO_EXTENSION);
        $destination_uri = 'sites/default/files/nostr-avatars/'.$postData->profile->name.'.'.$file_extension;
        /** @var \GuzzleHttp\Psr7\Response $guzzle_response */
        $guzzle_response = $client->get($source_uri, ['sink' => $destination_uri]);
        // Create file entity with downloaded avatar file.
        $file = File::create();
        $file->setFileUri('public://nostr-avatars/'.$postData->profile->name.'.'.$file_extension);
        $file->setOwnerId($user->id());
        $file->setMimeType($guzzle_response->getHeaderLine('content-type'));
        $file->setFilename($postData->profile->name);
        $file->setPermanent();
        $file->save();
        // Set user picture with this file.
        $user->set('user_picture', $file->id());
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
      \Drupal::logger('ccns')->error($e->getMessage());
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
    return $response;
  }

}

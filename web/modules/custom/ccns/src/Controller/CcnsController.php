<?php declare(strict_types = 1);

namespace Drupal\ccns\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenOffCanvasDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use GuzzleHttp\Exception\GuzzleException;
use swentel\nostr\Event\Event;
use swentel\nostr\Filter\Filter;
use swentel\nostr\Message\RequestMessage;
use swentel\nostr\Relay\Relay;
use swentel\nostr\Relay\RelaySet;
use swentel\nostr\Request\Request as NostrRequest;
use swentel\nostr\Subscription\Subscription;
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
    $content .= '<button id="nostr-login-nip07" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; cursor: pointer;">Connect with a Nostr extension</button>';
    $content .= '<br /><br />';
    $content .= '<button id="nostr-login-bunker" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; opacity: .25;">Connect with a nsecbunker</button>';
    $content .= '<br /><br />';
    $content .= '<button id="nostr-login-nsec" class="btn btn-warning" style="width:100%; padding: 10px; font-weight: bold; font-size: 1.2rem; opacity: .25;">Connect with a nsec key</button>';
    $content .= '<br /><br />';
    $content .= '<div id="drupal-login" class="hidden">';
    $content .= '<p>For now connecting your Nostr profile in a PWA on iOS is still impossible (the only option available, is to share your private key here).</p>';
    $content .= sprintf('<a href="%s">Drupal login with account</a>', Url::fromRoute('user.login')->toString());
    $content .= '</div>';
    // Add nostr-login library to the response which contains an event listeners for these buttons.
    $attachments['library'][] = 'ccns/connect-options';
    $response->setAttachments($attachments);
    $response->addCommand(new OpenOffCanvasDialogCommand('Connect', $content, ['width' => '30%'], NULL, 'side'));
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
      $postData = json_decode($request->getContent(), false);
      // Check if the profile data is posted.
      if ($postData->profile === null) {
        throw new \RuntimeException('No profile data found');
      }
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
        $user_picture_file_id = $user->get('user_picture')->getValue()[0]['target_id'];
        $user_picture_file = File::load($user_picture_file_id);
        // TODO check if we need to update the avatar
      } else {
        $user = User::create();
        $user->setUsername($postData->profile->name); // This username must be unique and accept only [a-Z,0-9, - _ @].
        $pwd = bin2hex(random_bytes(12));
        $user->setPassword($pwd);
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
        $destination_uri = 'sites/default/files/nostr-avatars/'.$postData->npub.'.'.$file_extension;
        if (!file_exists($destination_uri)) {
          /** @var \GuzzleHttp\Psr7\Response $guzzle_response */
          $guzzle_response = $client->get($source_uri, ['sink' => $destination_uri]);
          // Create file entity with downloaded avatar file.
          $file = File::create();
          $file->setFileUri('public://nostr-avatars/'.$postData->npub.'.'.$file_extension);
          $file->setOwnerId($user->id());
          $file->setMimeType($guzzle_response->getHeaderLine('content-type'));
          $file->setFilename($postData->npub);
          $file->setPermanent();
          $file->save();
          // Set user picture with this file.
          $user->set('user_picture', $file->id());
          $user->save();
        }
      }
      // login the user.
      if (!\Drupal::currentUser()->id()) {
        user_login_finalize($user);
      }
      $responseData = [
        'userid' => $user->id(),
      ];
      $response->setData($responseData);
    } catch (EntityStorageException $e) {
      \Drupal::logger('ccns')->error($e->getMessage());
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
    return $response;
  }

  public function globalFeed(Request $request): array {
    // Check if query param nostr_php is set
    if ($request->query->has('nostr-php')) {
      $time_start = microtime(true);
      /*
       * Get latest 50 event kinds 39701 from the following relays:
       * wss://relay.damus.io/
       * wss://relay.primal.net/
       * wss://nos.lol/
       * wss://khatru.nostrver.se/
      */
      $subscription = new Subscription();
      $filter = new Filter();
      $filter->setKinds([39701]);
      $filter->setLimit(50);
      $requestMessage = new RequestMessage($subscription->getId(), [$filter]);
      $relaySet = new RelaySet();
      $relays = [
        new Relay('wss://relay.damus.io/'),
        new Relay('wss://relay.primal.net/'),
        new Relay('wss://nos.lol/'),
        new Relay('wss://khatru.nostrver.se/'),
      ];
      $relaySet->setRelays($relays);
      $request = new NostrRequest($relaySet, $requestMessage);
      $response = $request->send();
      /**
       * @var string $relayUrl
       *   The relay URL.
       * @var object $relayResponses
       *   RelayResponses which will contain the messages returned by the relay.
       *   Each message will also contain the event.
       */
      $events = [];
      $profileToBeFetched = [];
      foreach ($response as $relayUrl => $relayResponses) {
        /** @var \swentel\nostr\RelayResponse\RelayResponse $relayResponse */
        foreach ($relayResponses as $relayResponse) {
          if ($relayResponse->type === 'EVENT') {
            if (isset($events[$relayResponse->event->id])) {
              // We need to filter out duplicate events, event is already in the array.
              continue;
            }
            // Format data into a Nostr event object.
            $events[$relayResponse->event->id] = $relayResponse->event;
            $e = new Event();
            $e->setId($relayResponse->event->id);
            $e->setPublicKey($relayResponse->event->pubkey);
            $e->setContent($relayResponse->event->content);
            $e->setCreatedAt($relayResponse->event->created_at);
            $e->setSignature($relayResponse->event->sig);
            $e->setKind($relayResponse->event->kind);
            $e->setTags($relayResponse->event->tags);
            // Fetch profile data of pubkey
            if ($relayResponse->event->pubkey) {
              $profileToBeFetched[$relayResponse->event->pubkey] = $relayResponse->event->pubkey;
            }
            $events[$relayResponse->event->id]->e = $e;
          }
        }
      }
      if (!empty($profileToBeFetched)) {
        $profiles = [];
        $profileFilter = new Filter();
        $profileFilter->setKinds([0]);
        $profileFilter->setAuthors($profileToBeFetched);
        $requestProfileMessage = new RequestMessage($subscription->getId(), [$profileFilter]);
        $relay = new Relay('wss://profiles.nostrver.se');
        $requestProfile = new NostrRequest($relay, $requestProfileMessage);
        $response = $requestProfile->send();
        foreach ($response as $relayUrl => $relayResponses) {
          foreach ($relayResponses as $relayResponse) {
            if ($relayResponse->type === 'EVENT') {
              // Decode content JSON string to object
              if (is_string($relayResponse->event->content)) {
                $relayResponse->event->content = json_decode($relayResponse->event->content, true);
              }
              $profiles[$relayResponse->event->pubkey] = $relayResponse->event;
            }
          }
        }
      }
      // Sort array on created_at value
      usort($events, function ($a, $b) {
        return $b->e->getCreatedAt() <=> $a->e->getCreatedAt();
      });
      $nostr_php = $events;
      $time_end = microtime(true);
      $speed = number_format(($time_end - $time_start), 2, '.', '');
      // TODO cache these results?

    }
    $build['content'] = [
      '#theme' => 'global_feed',
      '#nostr_php' => $nostr_php ?? [],
      '#profiles' => $profiles ?? [],
      '#speed' => $speed ?? NULL,
    ];
    $build['#attached']['library'][] = 'ccns/kind-39701';
    return $build;
  }

  public function forYouFeed(Request $request): array {
    $build['content'] = [
      '#theme' => 'for_you_feed',
    ];
    return $build;
  }

}

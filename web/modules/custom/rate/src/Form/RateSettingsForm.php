<?php

namespace Drupal\rate\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure rate settings for the site.
 */
class RateSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rate_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rate.settings'];
  }

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Http Client object.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * RateSettingsForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \GuzzleHttp\Client $http_client
   *   Http client object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Client $http_client) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rate.settings');

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Rate settings'),
      '#collapsbile' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['settings']['disable_log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable log messages'),
      '#default_value' => $config->get('disable_log'),
      '#description' => $this->t('This will disable log messages when voting in rate module.'),
    ];

    $form['settings']['disable_fontawesome'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable fontawesome'),
      '#default_value' => $config->get('disable_fontawesome'),
      '#description' => $this->t('This will disable fontawesome library from loading with rate module.'),
    ];

    $form['bot'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Bot detection'),
      '#description' => $this->t('Bots can be automatically banned from voting if they rate more than a given amount of votes within one minute or hour. This threshold is configurable below. Votes from the same IP-address will be ignored forever after reaching this limit.'),
      '#collapsbile' => FALSE,
      '#collapsed' => FALSE,
    ];

    $threshold_options = array_combine([0, 10, 25, 50, 100, 250, 500, 1000], [
      0,
      10,
      25,
      50,
      100,
      250,
      500,
      1000,
    ]);
    $threshold_options[0] = $this->t('disable');

    $form['bot']['bot_minute_threshold'] = [
      '#type' => 'select',
      '#title' => $this->t('1 minute threshold'),
      '#options' => $threshold_options,
      '#default_value' => $config->get('bot_minute_threshold'),
    ];

    $form['bot']['bot_hour_threshold'] = [
      '#type' => 'select',
      '#title' => $this->t('1 hour threshold'),
      '#options' => $threshold_options,
      '#default_value' => $config->get('bot_hour_threshold'),
    ];

    $form['bot']['botscout_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BotScout.com API key'),
      '#default_value' => $config->get('botscout_key'),
      '#description' => $this->t('Rate will check the voters IP against the BotScout database if it has an API key. You can request a key at %url.', ['%url' => 'http://botscout.com/getkey.htm']),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $messenger = $this->messenger();
    if ($form_state->getValue(['botscout_key'])) {
      $uri = "http://botscout.com/test/?ip=84.16.230.111&key=" . $form_state->getValue(['botscout_key']);
      try {
        $response = $this->httpClient->get($uri, ['headers' => ['Accept' => 'text/plain']]);
        $data = (string) $response->getBody();
        $status_code = $response->getStatusCode();
        if (empty($data)) {
          $messenger->addWarning($this->t('An empty response was returned from botscout.'));
        }
        elseif ($status_code == 200) {
          if (in_array(substr($data, 0, 1), ['Y', 'N'], TRUE)) {
            $messenger->addStatus($this->t('Rate has succesfully contacted the BotScout server.'));
          }
          else {
            $form_state->setErrorByName('botscout_key', $this->t('Invalid API-key.'));
          }
        }
        else {
          $messenger->addWarning($this->t('Rate was unable to contact the BotScout server.'));
        }
      }
      catch (RequestException $e) {
        $messenger->addWarning($this->t('An error occurred contacting BotScout.'));
        watchdog_exception('rate', $e);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('rate.settings');

    if ((bool) $config->get('disable_log') !== (bool) $form_state->getValue('disable_log')) {
      $config->set('disable_log', $form_state->getValue('disable_log'))
        ->save();
    }

    if ((bool) $config->get('disable_fontawesome') !== (bool) $form_state->getValue('disable_fontawesome')) {
      $config->set('disable_fontawesome', $form_state->getValue('disable_fontawesome'))
        ->save();
      Cache::invalidateTags(['library_info']);
    }

    $config->set('bot_minute_threshold', $form_state->getValue('bot_minute_threshold'))
      ->set('bot_hour_threshold', $form_state->getValue('bot_hour_threshold'))
      ->set('botscout_key', $form_state->getValue('botscout_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\rate\Plugin;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The rate.bot_detector service.
 */
class RateBotDetector {
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * Client IP.
   *
   * @var string
   */
  protected $ip;

  /**
   * HTTP User agent.
   *
   * @var string
   */
  protected $agent;

  /**
   * The config factory wrapper to fetch settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Http Client object.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * RateBotDetector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection object.
   * @param \GuzzleHttp\Client $http_client
   *   Http client object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Database connection object.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $database, Client $http_client, RequestStack $request_stack, TimeInterface $time) {
    $this->config = $config_factory->get('rate.settings');
    $this->database = $database;
    $this->httpClient = $http_client;
    $this->ip = $request_stack->getCurrentRequest()->getClientIp();
    $this->agent = $request_stack->getCurrentRequest()->headers->get('User-Agent');
    $this->time = $time;
  }

  /**
   * Check if the given IP is a local IP-address.
   *
   * @return bool
   *   True if local IP; false otherwise.
   */
  private function isLocal() {
    $match = explode('.', $this->ip);
    if ($match[0] == 10
      || $match[0] == 127
      || ($match[0] == 192 && $match[1] == 168)
      || ($match[0] == 172 && $match[1] >= 16 && $match[1] <= 31)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Save IP address as a bot.
   */
  private function registerBot() {
    $this->database->insert('rate_bot_ip')->fields(['ip' => $this->ip])->execute();
  }

  /**
   * Check if the IP-address exists in the local bot database.
   *
   * @return bool
   *   TRUE if IP is in database; false otherwise.
   */
  protected function checkIp() {
    return (bool) $this->database->select('rate_bot_ip', 'rbi')
      ->fields('rbi', ['id'])
      ->condition('rbi.ip', $this->ip)
      ->range(0, 1)
      ->execute()
      ->fetchField();
  }

  /**
   * Check if the given user agent matches the local bot database.
   *
   * @return bool
   *   True if match found; false otherwise.
   */
  protected function checkAgent() {
    $sql = 'SELECT 1 FROM {rate_bot_agent} WHERE :agent LIKE pattern LIMIT 1';
    return (bool) $this->database->query($sql, [':agent' => $this->agent])->fetchField();
  }

  /**
   * Check the number of votes between now and $interval seconds ago.
   *
   * @param int $interval
   *   Interval in seconds.
   *
   * @return int
   *   Number of votes between not and internval.
   */
  protected function checkThreshold($interval) {
    $sql = 'SELECT COUNT(*) FROM {votingapi_vote} WHERE vote_source = :ip AND timestamp > :time';
    return $this->database->query($sql, [':ip' => $this->ip, ':time' => $this->time->getRequestTime() - $interval])->fetchField();
  }

  /**
   * Check if botscout thinks the IP is a bot.
   *
   * @return bool
   *   True if botscout returns a positive; false otherwise.
   */
  protected function checkBotscout() {
    $key = $this->config->get('botscout_key');

    if ($key) {
      // @todo Move to config.
      $uri = "http://botscout.com/test/?ip=$this->ip&key=$key";

      try {
        $response = $this->httpClient->get($uri, ['headers' => ['Accept' => 'text/plain']]);
        $data = (string) $response->getBody();
        $status_code = $response->getStatusCode();
        if (!empty($data) && $status_code == 200) {
          if (substr($data, 0, 1) === 'Y') {
            return TRUE;
          }
        }
      }
      catch (RequestException $e) {
        $this->messenger()->addMessage($this->t('An error occurred contacting BotScout.'), 'warning');
        watchdog_exception('rate', $e);
      }
    }

    return FALSE;
  }

  /**
   * Check if the current user is blocked.
   *
   * This function will first check if the user is already known to be a bot.
   * If not, it will check if we have valid reasons to assume the user is a bot.
   *
   * @return bool
   *   True if bot detected; false otherwise.
   */
  public function checkIsBot() {
    if ($this->isLocal()) {
      // The IP-address is a local IP-address. This is probably because of
      // misconfigured proxy servers. Do only the user agent check.
      return $this->checkAgent();
    }

    if ($this->checkIp()) {
      return TRUE;
    }

    if ($this->checkAgent()) {
      // Identified as a bot by its user agent. Register this bot by IP-address
      // as well, in case this bots uses multiple agent strings.
      $this->registerBot();
      return TRUE;
    }

    $threshold = $this->config->get('bot_minute_threshold');

    if ($threshold && ($this->checkThreshold(60) > $threshold)) {
      $this->registerBot();
      return TRUE;
    }

    $threshold = $this->config->get('bot_hour_threshold');

    // Always count, even if threshold is disabled. This is to determine if we
    // can skip the BotScout check.
    $count = $this->checkThreshold(3600);
    if ($threshold && ($count > $threshold)) {
      $this->registerBot();
      return TRUE;
    }

    if (!$count && $this->checkBotscout()) {
      $this->registerBot();
      return TRUE;
    }

    return FALSE;
  }

}

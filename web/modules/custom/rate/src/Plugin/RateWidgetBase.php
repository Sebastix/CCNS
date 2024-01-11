<?php

namespace Drupal\rate\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\votingapi\VoteResultFunctionManager;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Base class for Rate widget plugins.
 */
class RateWidgetBase extends PluginBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The votingapi result manager.
   *
   * @var \Drupal\votingapi\VoteResultFunctionManager
   */
  protected $votingapiResult;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Database connection object.
   *
   * @var \Drupal\rate\RateBotDetector
   */
  protected $botDetector;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\votingapi\VoteResultFunctionManager $vote_result
   *   Vote result function service.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\rate\RateBotDetector $bot_detector
   *   The bot detector service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, VoteResultFunctionManager $vote_result, EntityFormBuilderInterface $form_builder, AccountInterface $account, RequestStack $request_stack, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, RateBotDetector $bot_detector) {
    $this->entityTypeManager = $entity_type_manager;
    $this->votingapiResult = $vote_result;
    $this->entityFormBuilder = $form_builder;
    $this->account = $account;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->botDetector = $bot_detector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.votingapi.resultfunction'),
      $container->get('entity.form_builder'),
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('bot_detector')
    );
  }

  /**
   * Return label.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Return minimal value.
   */
  public function getValues() {
    return $this->getPluginDefinition()['values'];
  }

  /**
   * Gets the widget form as configured for given parameters.
   *
   * @return \Drupal\Core\Form\FormInterface
   *   configured vote form
   */
  public function getForm($entity_type, $entity_bundle, $entity_id, $vote_type, $value_type, $rate_widget, $settings, $user_id = NULL) {
    $options = $settings->get('options');
    $vote = $this->getEntityForVoting($entity_type, $entity_bundle, $entity_id, $vote_type, $value_type, $rate_widget, $settings, $user_id);

    // Give other modules a chance to alter the rate widget options.
    $this->moduleHandler->invokeAll('rate_widget_options_alter',
      [
        &$options,
        $entity_type,
        $entity_bundle,
        $entity_id,
        $rate_widget,
        $settings,
        $user_id,
      ]
    );

    $new_options = [];
    $option_classes = [];

    // For Fivestar we need only the values and labels, omit classes.
    foreach ($options as $option) {
      $new_options[$option['value']] = isset($option['label']) ? $option['label'] : '';
      $option_classes[$option['value']] = isset($option['class']) ? $option['class'] : '';
    }

    // Add information if the form is built in a view.
    $is_views_field = FALSE;
    $views_url = FALSE;
    if ($settings->get('isViewsField') == TRUE) {
      $is_views_field = TRUE;
      $views_url = $settings->get('viewsUrl');
    }

    /*
     * @TODO: remove custom entity_form_builder once
     *   https://www.drupal.org/node/766146 is fixed.
     */
    $form = $this->entityFormBuilder->getForm($vote, 'rate_vote', [
      'settings' => $settings,
      'plugin' => $this,
      'options' => $new_options,
      'classes' => $option_classes,
      'show_own_vote' => '1',
      'readonly' => FALSE,
      'is_views_field' => $is_views_field,
      'views_url' => $views_url,
      // @todo Remove following keys when #766146 is fixed (multiple form_ids).
      'entity_type' => $entity_type,
      'entity_bundle' => $entity_bundle,
      'entity_id' => $entity_id,
      'vote_type' => $vote_type,
      'rate_widget' => $rate_widget,
    ]);

    return $form;
  }

  /**
   * Checks whether currentUser is allowed to vote.
   *
   * @return bool
   *   True if user is allowed to vote
   */
  public function canVote($vote, $account = FALSE) {
    if (!$account) {
      $account = $this->account;
    }
    $entity = $this->entityTypeManager
      ->getStorage($vote->getVotedEntityType())
      ->load($vote->getVotedEntityId());
    if (!$entity) {
      return FALSE;
    }

    if ($vote->getVotedEntityType() == 'comment') {
      $perm = 'cast rate vote on ' . $entity->getFieldName() . ' on ' . $entity->getCommentedEntityTypeId() . ' of ' . $entity->getCommentedEntity()->bundle();
    }
    else {
      $perm = 'cast rate vote on ' . $vote->getVotedEntityType() . ' of ' . $entity->bundle();
    }
    $can_vote = $account->hasPermission($perm);

    // Allow modules to implement custom logic for user vote check.
    $this->moduleHandler->invokeAll('rate_can_vote',
      [
        &$can_vote,
        $vote,
        $entity,
        $account,
      ]
    );
    return $can_vote;
  }

  /**
   * Checks whether IP from request is a known bot.
   *
   * @return bool
   *   True if we have a bot voting
   */
  public function isBotVote() {
    $is_bot_vote = $this->botDetector->checkIsBot();
    return $is_bot_vote;
  }

  /**
   * Returns a Vote entity.
   *
   * Checks whether a vote was already done and if this vote should be reused
   * instead of adding a new one.
   *
   * @return \Drupal\votingapi\Entity\Vote
   *   The vote entity.
   */
  public function getEntityForVoting($entity_type, $entity_bundle, $entity_id, $vote_type, $value_type, $rate_widget, $settings, $user_id) {
    $storage = $this->entityTypeManager->getStorage('vote');
    $vote_data = [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'type'  => $vote_type,
      'value_type' => $value_type,
      'rate_widget' => $rate_widget,
    ];
    $vote_data['user_id'] = (!is_null($user_id)) ? $user_id : $this->account->id();

    // Give other modules a chance to alter the data for vote creation.
    $this->moduleHandler->invokeAll('rate_vote_data_alter',
      [
        &$vote_data,
        $entity_type,
        $entity_bundle,
        $entity_id,
        $rate_widget,
        $settings,
        $user_id,
      ]
    );

    $vote = $storage->create($vote_data);
    $voting_settings = $settings->get('voting');
    $timestamp_offset = $this->getWindow('user_window', $entity_type, $entity_bundle, $rate_widget, $voting_settings);

    if ($this->account->isAnonymous()) {
      $vote_data['vote_source'] = hash('sha256', serialize($this->requestStack->getCurrentRequest()->getClientIp()));
      $timestamp_offset = $this->getWindow('anonymous_window', $entity_type, $entity_bundle, $rate_widget, $voting_settings);
    }

    $query = $this->entityTypeManager->getStorage('vote')->getQuery();
    $query->accessCheck(TRUE);

    foreach ($vote_data as $key => $value) {
      $query->condition($key, $value);
    }

    // Check if rollover is 'Immediately' or value in seconds.
    if ($timestamp_offset >= 0) {
      $query->condition('timestamp', time() - $timestamp_offset, '>');
    }

    $votes = $query->execute();
    if ($votes && count($votes) > 0) {
      $vote = $storage->load(array_shift($votes));
    }
    else {
      // On a new vote, set value to NULL, so we can trigger on and store zero.
      if ($vote->isNew()) {
        $vote->setValue(NULL);
      }
    }
    return $vote;
  }

  /**
   * Get results.
   */
  public function getResults($entity, $result_function = FALSE, $reset = FALSE) {
    if ($reset) {
      drupal_static_reset(__FUNCTION__);
    }
    $resultCache = &drupal_static(__FUNCTION__);
    if (!$resultCache || !isset($resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()]) || !$entity->id()) {
      $resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()] = $this->votingapiResult->getResults($entity->getVotedEntityType(), $entity->getVotedEntityId());
    }

    $result = isset($resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()]) ? $resultCache[$entity->getVotedEntityType()][$entity->getVotedEntityId()] : [];
    $result = !empty($result) && array_key_exists($entity->bundle(), $result) ? $result[$entity->bundle()] : [];
    if ($result_function && array_key_exists($result_function, $result) && $result[$result_function]) {
      $result = $result[$result_function];
    }
    return $result;
  }

  /**
   * Get time window settings.
   */
  public function getWindow($window_type, $entity_type_id, $entity_bundle, $rate_widget, $voting_settings) {
    // Check for rollover window settings in widget or use votingapi setting.
    $window_field_setting = isset($voting_settings[$window_type]) ? $voting_settings[$window_type] : -2;
    $use_site_default = FALSE;

    // Use votingapi site-wide setting if requested or window not set.
    if ($window_field_setting === NULL || $window_field_setting === -2) {
      $use_site_default = TRUE;
    }

    $window = $window_field_setting;
    if ($use_site_default) {
      /** @var \Drupal\Core\Config\ImmutableConfig $voting_configuration */
      $voting_configuration = $this->configFactory->get('votingapi.settings');
      $window = $voting_configuration->get($window_type);
    }
    return $window;
  }

  /**
   * Generate the result summary.
   */
  public function getVoteSummary(ContentEntityInterface $vote) {
    $results = $this->getResults($vote);
    $widget_name = $vote->rate_widget->value;
    $widget = $this->entityTypeManager->getStorage('rate_widget')->load($widget_name);
    $field_results = [];

    foreach ($results as $key => $result) {
      if (strpos($key, '.') && strpos($key, ':')) {
        if ((substr($key, strrpos($key, '.') + 1) === $widget_name)) {
          $key = explode(':', $key);
          $field_results[$key[0]] = ($result != 0) ? ceil($result * 10) / 10 : 0;
        }
      }
    }
    return [
      '#theme' => 'rate_widgets_summary',
      '#vote' => $vote,
      '#results' => $field_results,
      '#rate_widget' => $widget_name,
      '#widget_template' => $widget->get('template'),
    ];
  }

  /**
   * Returns the votes for an entity.
   *
   * @return array
   *   Vote entity results.
   */
  public function getVotes($entity_type, $entity_bundle, $entity_id, $vote_type, $value_type, $rate_widget, $user_id = FALSE) {
    $storage = $this->entityTypeManager->getStorage('vote');
    $vote_data = [
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'type'  => $vote_type,
      'value_type'  => $value_type,
      'rate_widget' => $rate_widget,
    ];
    if (!empty($user_id)) {
      $vote_data['user_id'] = $user_id;
    }
    $query = $this->entityTypeManager->getStorage('vote')->getQuery();
    foreach ($vote_data as $key => $value) {
      $query->condition($key, $value);
    }
    $query->accessCheck(TRUE);
    $votes = $query->execute();
    $vote_values = [];
    if ($votes && count($votes) > 0) {
      foreach ($votes as $id) {
        $vote = $storage->load($id);
        $vote_values[$vote->getValue()][] = $vote->getValue();
      }
    }
    return $vote_values;
  }

}

<?php

namespace Drupal\rate;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for vote type forms.
 */
class RateWidgetForm extends EntityForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the VoteTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, EntityFieldManagerInterface $entity_field_manager, ModuleHandlerInterface $module_handler, DateFormatter $date_formatter, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->moduleHandler = $module_handler;
    $this->dateFormatter = $date_formatter;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('module_handler'),
      $container->get('date.formatter'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $rate_widget = $this->entity;
    $widget_template = $rate_widget->get('template');

    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add rate widget');
    }
    else {
      $form['#title'] = $this->t('Edit %label rate widget', ['%label' => $rate_widget->label()]);
    }

    // Collect rate widget templates from other modules.
    $template_list = [];
    foreach ($this->moduleHandler->getImplementations('rate_templates') as $module) {
      foreach ($this->moduleHandler->invoke($module, 'rate_templates') as $name => $template) {
        $template_list[$name] = $template->template_title;
        if ($name == $widget_template) {
          $current_template = $template;
          $current_template->id = $name;
        }
      }
    }
    $form_state->set('template_list', $template_list);

    // If we create a widget or change the template - show selector instead.
    if (is_null($widget_template) || ($this->operation == 'template') || ($form_state->has('page') && $form_state->get('page') == 'template_selector')) {
      return self::templateSelector($form, $form_state);
    }

    $form_state->set('page', 'rate_widget_form');

    $form['label'] = [
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#default_value' => $rate_widget->label(),
      '#description' => $this->t('The human-readable name of this rate widget. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rate_widget->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => ['Drupal\rate\Entity\RateWidget', 'load'],
        'source' => ['label'],
      ],
      '#description' => $this->t('A unique machine-readable name for this rate widget. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    // Template selector.
    $form['template'] = [
      '#type' => 'hidden',
      '#value' => $rate_widget->get('template'),
    ];
    $form['template-selector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Template'),
      '#required' => TRUE,
      '#default_value' => $template_list[$rate_widget->get('template')] ? $template_list[$rate_widget->get('template')] : '',
      '#size' => 30,
      '#attributes' => ['disabled' => 'disabled'],
    ];
    if ($this->operation == 'edit') {
      $url = Url::fromRoute('entity.rate_widget.template_form', ['rate_widget' => $this->entity->id()])->toString();
      $form['template-selector']['#description'] = $this->t('The template can be changed [<a href="@template-selector">Change</a>]', ['@template-selector' => $url]);
    }

    if ($current_template->customizable) {
      // Vote types.
      $vote_types = [
        'percent' => $this->t('Percentage'),
        'points' => $this->t('Points'),
        'option' => $this->t('Options'),
      ];
      $form['value_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Value type'),
        '#options' => $vote_types,
        '#default_value' => $rate_widget->get('value_type') ? $rate_widget->get('value_type') : $current_template->value_type,
        '#required' => TRUE,
      ];
    }
    else {
      $form['value_type'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value type'),
        '#default_value' => $current_template->value_type ? $current_template->value_type : 'option',
        '#size' => 30,
        '#attributes' => ['disabled' => 'disabled'],
        '#required' => TRUE,
      ];
    }

    // Options table.
    $rate_widget_options = $rate_widget->get('options');

    if ($rate_widget->isNew()) {
      if (count($current_template->options) > 0) {
        // If the template has options defined - use them.
        $options_items = $current_template->options;
      }
      else {
        // Otherwise - create an empty row.
        $options_items = [];
      }
    }
    else {
      if (count($rate_widget_options) > 0) {
        if ($form_state->get('overwrite_values') === TRUE) {
          // We are changing the template of an existing widget - overwrite.
          $options_items = $current_template->options;
        }
        else {
          // We are editing an existing widget - use saved values.
          $options_items = isset($rate_widget_options['table']) ? $rate_widget_options['table'] : $rate_widget_options;
        }
      }
      else {
        // Widget has no saved values - create an empty row.
        $options_items = [];
      }
    }

    $form['#tree'] = TRUE;
    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#description' => $this->t('Define the available voting options/buttons. To delete an option - delete its values in the fields <i>value, label</i> and <i>class</i> and save the rate widget.'),
      '#open' => TRUE,
      '#prefix' => '<div id="options-table-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['options']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Value'),
        $this->t('Label'),
        $this->t('Class'),
      ],
      '#responsive' => TRUE,
    ];

    $options_items_count = count($options_items) + $form_state->get('options_items_count');
    for ($i = 0; $i < $options_items_count; $i++) {
      $form['options']['table'][$i]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#title_display' => 'invisible',
        '#default_value' => isset($options_items[$i]['value']) ? $options_items[$i]['value'] : '',
        '#size' => 8,
      ];
      $form['options']['table'][$i]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#title_display' => 'invisible',
        '#default_value' => isset($options_items[$i]['label']) ? $options_items[$i]['label'] : '',
        '#size' => 40,
      ];
      $form['options']['table'][$i]['class'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Class'),
        '#title_display' => 'invisible',
        '#default_value' => isset($options_items[$i]['class']) ? $options_items[$i]['class'] : '',
        '#size' => 40,
      ];
      if (!$current_template->customizable) {
        $form['options']['table'][$i]['value']['#attributes'] = ['disabled' => 'disabled'];
        $form['options']['table'][$i]['label']['#attributes'] = ['disabled' => 'disabled'];
      }
    }
    // Hide the "Add another option" button, if template is not customizable.
    if ($current_template->customizable) {
      $form['options']['actions'] = [
        '#type' => 'actions',
        '#prefix' => '<div id="action-buttons-wrapper">',
        '#suffix' => '</div>',
      ];
      $form['options']['actions']['add_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add another option'),
        '#submit' => ['::addOne'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'options-table-wrapper',
        ],
      ];
    }
    // Entities.
    $form['entities'] = [
      '#type' => 'details',
      '#title' => $this->t('Entities'),
      '#description' => $this->t('Select the entities and/or comments on those entities, on which to enable this widget.'),
      '#open' => TRUE,
      '#prefix' => '<div id="options-table-wrapper">',
      '#suffix' => '</div>',
    ];
    $comment_module_enabled = $this->moduleHandler->moduleExists('comment');
    $comment_header = ($comment_module_enabled) ? $this->t('Comment') : $this->t('Comment (disabled)');
    $form['entities']['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Entity Type'),
        $this->t('Entity'),
        $comment_header,
      ],
      '#responsive' => TRUE,
    ];
    $entity_types = $this->entityTypeManager->getDefinitions();
    $entity_type_ids = array_keys($entity_types);
    $rate_widget_entities = $rate_widget->get('entity_types') ? $rate_widget->get('entity_types') : [];
    $rate_widget_comments = $rate_widget->get('comment_types') ? $rate_widget->get('comment_types') : [];

    foreach ($entity_types as $entity_type_id => $entity_type) {
      // Restrict voting on certain content types - not shown in the list.
      // Comments are handled throu the content types, blocks, menu items etc.
      // Also, don't allow voting on votes, that would be weird.
      $restrict_entities = [
        'comment',
        'block_content',
        'contact_message',
        'path_alias',
        'menu_link_content',
        'shortcut',
        'vote',
        'vote_result',
      ];
      if ($entity_type->getBundleOf() && !in_array($entity_type->getBundleOf(), $restrict_entities)) {
        $bundles = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple();
        if (!empty($bundles)) {
          foreach ($bundles as $bundle) {
            $row = [];
            $return_value = $entity_type->getBundleOf() . '.' . $bundle->id();
            $row['entity_type'] = ['#plain_text' => $bundle->label()];
            $row['entity_type_id'] = ['#plain_text' => $entity_type->getBundleOf()];
            // Entity column.
            $row['entity_enabled'] = [
              '#type' => 'checkbox',
              '#empty_value' => '',
              '#required' => FALSE,
              '#default_value' => in_array($return_value, $rate_widget_entities),
              '#return_value' => $return_value,
            ];
            // Comment column.
            $row['comment_enabled'] = [
              '#type' => 'checkbox',
              '#empty_value' => '',
              '#required' => FALSE,
              '#default_value' => in_array($return_value, $rate_widget_comments),
              '#return_value' => $return_value,
              '#disabled' => !$comment_module_enabled,
            ];
            $form['entities']['table'][] = $row;
          }
        }
      }
      elseif ($entity_type->getGroup() == 'content' && !in_array($entity_type->getBundleEntityType(), $entity_type_ids) && !in_array($entity_type->id(), $restrict_entities)) {
        $row = [];
        $return_value = $entity_type_id . '.' . $entity_type_id;
        $row['entity_type'] = ['#plain_text' => $entity_type->getLabel()->__toString()];
        $row['entity_type_id'] = ['#plain_text' => $entity_type_id];
        // Entity column.
        $row['entity_enabled'] = [
          '#type' => 'checkbox',
          '#empty_value' => '',
          '#required' => FALSE,
          '#default_value' => in_array($return_value, $rate_widget_entities),
          '#return_value' => $return_value,
        ];
        // Comment column.
        $row['comment_enabled'] = [
          '#type' => 'checkbox',
          '#empty_value' => '',
          '#required' => FALSE,
          '#default_value' => in_array($return_value, $rate_widget_comments),
          '#return_value' => $return_value,
          '#disabled' => !$comment_module_enabled,
        ];
        $form['entities']['table'][] = $row;
      }
    }

    // Voting settings.
    $voting = $rate_widget->get('voting');
    $form['voting'] = [
      '#type' => 'details',
      '#title' => $this->t('Voting settings'),
      '#open' => TRUE,
    ];
    $form['voting']['use_deadline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use a vote deadline'),
      '#default_value' => isset($voting['use_deadline']) ? $voting['use_deadline'] : 0,
      '#description' => $this->t('Enables a deadline date field on the respective node. If deadline is set and date passed, the widget will be shown as disabled.'),
    ];
    // Additional settings for rollover 'Never' or 'Immediately'.
    // Work in progress for both options in votingapi module.
    // See https://www.drupal.org/project/votingapi/issues/3060468 (Reg. user).
    // See https://www.drupal.org/project/votingapi/issues/2791129 (Anonymous).
    // @todo When those options get committed in votingapi - rewrite this.
    $unit_options = [
      300,
      900,
      1800,
      3600,
      10800,
      21600,
      32400,
      43200,
      86400,
      172800,
      345600,
      604800,
    ];
    $options = [];
    foreach ($unit_options as $option) {
      $options[$option] = $this->dateFormatter->formatInterval($option);
    }
    $options[0] = $this->t('Immediately');
    $options[-1] = $this->t('Never');
    $options[-2] = $this->t('Votingapi setting');

    $form['voting']['anonymous_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Anonymous vote rollover'),
      '#description' => $this->t('The amount of time that must pass before two anonymous votes from the same computer are considered unique. Setting this to <i>never</i> will eliminate most double-voting, but will make it impossible for multiple anonymous on the same computer (like internet cafe customers) from casting votes.'),
      '#options' => $options,
      '#default_value' => isset($voting['anonymous_window']) ? $voting['anonymous_window'] : -2,
    ];
    $form['voting']['user_window'] = [
      '#type' => 'select',
      '#title' => $this->t('Registered user vote rollover'),
      '#description' => $this->t('The amount of time that must pass before two registered user votes from the same user ID are considered unique. Setting this to <i>never</i> will eliminate most double-voting for registered users.'),
      '#options' => $options,
      '#default_value' => isset($voting['user_window']) ? $voting['user_window'] : -2,
    ];

    // Display settings.
    $display = $rate_widget->get('display');
    $form['display'] = [
      '#type' => 'details',
      '#title' => $this->t('Display settings'),
      '#open' => TRUE,
    ];
    $form['display']['display_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => isset($display['display_label']) ? $display['display_label'] : '',
      '#description' => $this->t('Optional label for the rate widget.'),
    ];
    $form['display']['label_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label classes'),
      '#default_value' => isset($display['label_class']) ? $display['label_class'] : '',
      '#description' => $this->t('Enter classes, separated with space.'),
    ];
    $options = [
      'inline' => $this->t('Inline with the widget'),
      'above' => $this->t('Above the widget'),
      'hidden' => $this->t('Hide the label'),
    ];
    $form['display']['label_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Position of the label'),
      '#options' => $options,
      '#default_value' => isset($display['label_position']) ? $display['label_position'] : 'above',
    ];
    $form['display']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => isset($display['description']) ? $display['description'] : '',
      '#description' => $this->t('Optional description which will be visible on the rate widget.'),
    ];
    $form['display']['description_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description classes'),
      '#default_value' => isset($display['description_class']) ? $display['description_class'] : '',
      '#description' => $this->t('Enter classes, separated with space.'),
    ];
    $options = [
      'below' => $this->t('Under the widget'),
      'right' => $this->t('To the right of the widget'),
      'hidden' => $this->t('Hide the description'),
    ];
    $form['display']['description_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Position of the description'),
      '#options' => $options,
      '#default_value' => isset($display['description_position']) ? $display['description_position'] : 'below',
    ];
    $form['display']['readonly'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Read only widget'),
      '#default_value' => isset($display['readonly']) ? $display['readonly'] : 0,
    ];

    // Results settings.
    $results = $rate_widget->get('results');
    $form['results'] = [
      '#type' => 'details',
      '#title' => $this->t('Results'),
      '#description' => $this->t('Note that these settings do not apply for rate widgets inside views. Widgets in views will display the average voting when a relationship to the voting results is used and the users vote in case of a relationship to the votes.'),
      '#open' => TRUE,
    ];
    $options = [
      'below' => $this->t('Under the widget (or option)'),
      'right' => $this->t('To the right of the widget'),
      'hidden' => $this->t('Hide the results summary'),
    ];
    $form['results']['result_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Position of the results summary'),
      '#options' => $options,
      '#default_value' => isset($results['result_position']) ? $results['result_position'] : 'right',
    ];
    $options = [
      'user_vote_empty' => $this->t('User vote if available, empty otherwise'),
      'user_vote_average' => $this->t('User vote if available, option results otherwise'),
      'vote_average' => $this->t('Option results'),
      'vote_hidden' => $this->t('Hide option results'),
    ];
    $form['results']['result_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Which rating (option results) should be displayed?'),
      '#options' => $options,
      '#default_value' => isset($results['result_type']) ? $results['result_type'] : 'user_vote_empty',
      '#description' => $this->t('Option results: shown for each option and based on the value type function - average (percentage), count (option) or sum (points).'),
    ];

    return $form;
  }

  /**
   * Builds the template selector form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function templateSelector(array &$form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $rate_widget = $this->entity;
    $form_state->set('page', 'template_selector');
    $template_list = $form_state->get('template_list');

    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#empty_value' => '',
      '#required' => TRUE,
      '#options' => $template_list,
      '#default_value' => $rate_widget->get('template'),
    ];
    if ($this->operation == 'template') {
      $form_state->set('overwrite_values', TRUE);
      $form['template']['#description'] = $this->t('CAUTION: Changing the template will overwrite your current settings for options and value type!');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $page = $form_state->get('page');
    if (isset($page) && $page == 'template_selector') {
      $actions['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        // Custom validation handler for the template_selectos.
        '#validate' => ['::templateSelectorNextValidate'],
        // Custom submission handler for the template_selector.
        '#submit' => ['::templateSelectorNextSubmit'],
      ];
    }
    else {
      $actions = parent::actions($form, $form_state);
      $actions['submit']['#value'] = $this->t('Save rate widget');
      $actions['delete']['#value'] = $this->t('Delete rate widget');
    }
    return $actions;
  }

  /**
   * Provides custom validation handler for the template selector.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function templateSelectorNextValidate(array &$form, FormStateInterface $form_state) {
    $template = $form_state->getValue('template');

    if (!isset($template) || $template == '') {
      // Set an error if "template" is empty.
      $form_state->setErrorByName('template', $this->t('Select a template to continue!'));
    }
  }

  /**
   * Provides custom submission handler for the template selector.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function templateSelectorNextSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->set('page', 'rate_widget_form');
    if (!$this->entity->isNew()) {
      $this->setOperation('edit');
    }
    else {
      $this->setOperation('add');
    }
    $form_state->setRebuild(TRUE);
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['options'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the options table items counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $form_state->set('options_items_count', 1);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Ensure the options are properly defined.
    if ($form_state->hasValue('options')) {
      $options = $form_state->getValue('options');
      // Need at least one otion with value and label defined.
      if (is_array($options['table']) && count($options['table']) == 1) {
        $element = $form['options']['table'][0];
        $message = $this->t('You need at least one option with value and label defined.');
        if (empty($options['table'][0]['value'])) {
          $form_state->setError($element['value'], $message);
        }
        if (empty($options['table'][0]['label'])) {
          $form_state->setError($element['label'], $message);
        }
      }
      else {
        // Ensure there are no duplicate option values.
        $values = array_column($options['table'], 'value');
        if ($values != array_unique($values)) {
          $value_count = array_count_values($values);
          foreach ($value_count as $value => $count) {
            if ($count > 1) {
              foreach ($options['table'] as $row => $settings) {
                if ($settings['value'] == $value) {
                  $message = $this->t('Option values have to be unique.');
                  $form_state->setError($form['options']['table'][$row]['value'], $message);
                }
              }
            }
          }
        }
        // If there is a value set ensure there is also a label.
        $message = $this->t('You need a label defined for the option.');
        foreach ($options['table'] as $row => $settings) {
          if (!empty($settings['value']) && empty($settings['label'])) {
            $element = $form['options']['table'][$row];
            $form_state->setError($element['label'], $message);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $rate_widget = $this->entity;
    $rate_widget->set('id', trim($rate_widget->id()));
    $rate_widget->set('label', trim($rate_widget->label()));
    $rate_widget->set('template', $rate_widget->get('template'));
    $rate_widget->set('value_type', $rate_widget->get('value_type'));

    // Prepare the options for saving.
    $options = $rate_widget->get('options');
    unset($options['actions']);
    $options = $options['table'];

    // Remove empty optons.
    foreach ($options as $key => $value) {
      if ($options[$key]['value'] == NULL) {
        unset($options[$key]);
      }
    }
    // Reindex and set the options.
    $options = array_values($options);
    $rate_widget->set('options', $options);

    // Prepare the entities for saving.
    $entities = $rate_widget->get('entities');
    $entities = $entities['table'];

    // Remove empty entities.
    foreach ($entities as $key => $value) {
      if ($entities[$key]['entity_enabled'] == NULL && $entities[$key]['comment_enabled'] == NULL) {
        unset($entities[$key]);
      }
    }
    // Reindex and set the entities.
    $entities = array_values($entities);
    $entity_types = [];
    $comment_types = [];

    // Save current entities to remove rate_vote_deadline field.
    $current_entities = $rate_widget->get('entity_types');
    $rate_widget->set('entities', $entities);

    // Split the values in separate arrays for compatibility with D7.
    foreach ($entities as $key => $value) {
      if ($value['entity_enabled'] && $value['comment_enabled']) {
        $entity_types[] = $value['entity_enabled'];
        $comment_types[] = $value['comment_enabled'];
      }
      elseif ($value['entity_enabled']) {
        $entity_types[] = $value['entity_enabled'];
      }
      elseif ($value['comment_enabled']) {
        $comment_types[] = $value['comment_enabled'];
      }
    }
    $rate_widget->set('entity_types', $entity_types);
    $rate_widget->set('comment_types', $comment_types);

    // Remove deadline field if rate widget was detached or deadline unchecked.
    $removed_entities = array_diff($current_entities, $rate_widget->get('entity_types'));
    $deadline_value = $form['voting']['use_deadline']['#default_value'];
    $deadline_new_value = $form['voting']['use_deadline']['#value'];

    if ($deadline_new_value == 0 && $deadline_new_value != $deadline_value) {
      $remove_deadline = TRUE;
    }
    $field_name = 'field_rate_vote_deadline';

    // Remove the deadline when the use deadline checkbox is being unset.
    if (isset($remove_deadline) && !empty($current_entities)) {
      foreach ($current_entities as $key => $entity) {
        $parameter = explode('.', $entity);
        $field_config = FieldConfig::loadByName($parameter[0], $parameter[1], $field_name);
        if (!empty($field_config)) {
          $field_config->delete();
          $this->messenger->addStatus($this->t('Field %field removed from %entity.', ['%field' => 'Rate vote deadline', '%entity' => $entity]));
        }
      }
    }
    // Remove the deadline when the entity list of the widget is changed.
    if ($deadline_new_value == 1 && !isset($remove_deadline) && !empty($removed_entities)) {
      foreach ($removed_entities as $key => $entity) {
        $parameter = explode('.', $entity);
        $field_config = FieldConfig::loadByName($parameter[0], $parameter[1], $field_name);
        if (!empty($field_config)) {
          $field_config->delete();
          $this->messenger->addStatus($this->t('Field %field removed from %entity.', ['%field' => 'Rate vote deadline', '%entity' => $entity]));
        }
      }
    }

    // Set the voting, display and results settings.
    $voting = ($rate_widget->get('voting')) ? $rate_widget->get('voting') : [];
    $display = ($rate_widget->get('display')) ? $rate_widget->get('display') : [];
    $results = ($rate_widget->get('results')) ? $rate_widget->get('results') : [];

    $rate_widget->set('voting', $voting);
    $rate_widget->set('display', $display);
    $rate_widget->set('results', $results);

    // Save the widget.
    $status = $rate_widget->save();

    $t_args = ['%name' => $rate_widget->label()];

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The rate widget %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The rate widget %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $rate_widget->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('rate_widget')->notice('Added rate widget %name.', $context);
    }
    $this->entityFieldManager->clearCachedFieldDefinitions();
    $form_state->setRedirect('entity.rate_widget.collection');
  }

}

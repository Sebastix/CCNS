<?php

namespace Drupal\rate\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Views field handler for the rate widget.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("rate_widget_field")
 */
class RateWidgetField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options.
   *
   * @return array
   *   Array of options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['id_column'] = ['default' => ''];
    $options['widgets'] = ['default' => ''];
    $options['widget_display'] = ['default' => ''];
    $options['display_overrides'] = ['default' => []];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // Let the user select the ID to vote on and avoid relationship nightmare.
    $field_list = [];
    $field_handlers = $this->view->getHandlers('field');
    foreach ($field_handlers as $field_name) {
      if ($field_name['entity_type'] && $field_name['plugin_id'] != 'rate_widget_field') {
        if (empty($field_name['label'])) {
          $field_list[$field_name['id']] = $field_name['id'];
        }
        else {
          $field_list[$field_name['id']] = $field_name['label'];
        }
      }
    }

    $form['id_column'] = [
      '#title' => $this->t('Which field column holds the entity ID?'),
      '#type' => 'select',
      '#default_value' => $this->options['id_column'],
      '#options' => $field_list,
      '#description' => $this->t('Enable and hide the ID field of the entity, which has a Rate widget attached to it.'),
    ];

    // Handle multiple widgets per entity.
    $widgets = \Drupal::service('entity_type.manager')->getStorage('rate_widget')->loadMultiple();
    $entity_types = [];
    $widget_count = 0;
    foreach ($widgets as $id => $widget) {
      $widget_entities = $widget->get('entity_types');
      if (is_array($widget_entities) && count($widget_entities) > 0) {
        foreach ($widget_entities as $entity) {
          $entity = str_replace('.', ':', $entity);
          $entity_types[$entity][$id] = $widget->label();
          if (count($entity_types[$entity]) > $widget_count) {
            $widget_count = count($entity_types[$entity]);
          }
        }
      }
    }
    // Let the user select the widget to use in this view field.
    if ($widget_count > 1) {
      $form['widgets'] = [
        '#type' => 'table',
        '#caption' => $this->t('<strong>Some entities have multiple widgets attached - select the ones to be shown in this field.<strong>'),
        '#header' => ['Entity', 'Widget'],
      ];
      foreach ($entity_types as $entity => $widgets) {
        if (count($widgets) > 1) {
          $form['widgets'][$entity]['entity'] = [
            '#type' => 'item',
            '#markup' => $entity,
          ];
          $form['widgets'][$entity]['widget'] = [
            '#type' => 'select',
            '#options' => $widgets,
            '#default_value' => $this->options['widgets'][$entity]['widget'],
          ];
        }
      }
    }
    else {
      $form['widgets'] = [];
    }
    // Select how to display the widget.
    $widget_display_options = [
      'full' => $this->t('Full'),
      'readonly' => $this->t('Read only'),
      'summary' => $this->t('Result summary'),
    ];
    $form['widget_display'] = [
      '#title' => $this->t('Show widget'),
      '#type' => 'select',
      '#default_value' => $this->options['widget_display'],
      '#options' => $widget_display_options,
      '#default_value' => $this->options['widget_display'],
    ];
    // Override rate widget display settings.
    $form['display_overrides'] = [
      '#title' => $this->t('Override rate widget display options'),
      '#type' => 'checkboxes',
      '#options' => [
        'hide_label' => $this->t('Hide label'),
        'hide_description' => $this->t('Hide description'),
        'hide_summary' => $this->t('Hide summary'),
      ],
      '#default_value' => $this->options['display_overrides'],
      '#description' => $this->t('Unchecking all options will show the rate widget as configured.'),
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $row) {
    $column = $this->options['id_column'];
    $widgets = $this->options['widgets'];
    $display_overrides = $this->options['display_overrides'];
    $widget_display = $this->options['widget_display'];
    $widget_storage = \Drupal::service('entity_type.manager')->getStorage('rate_widget');
    $rate_widget_base_service = \Drupal::service('rate.vote_widget_base');

    // Check, if the field is in _entity (base table)
    if (isset($row->_entity->{$column})) {
      $entity_id = $row->_entity->id();
      $entity_type_id = $row->_entity->getEntityTypeId();
      if ($entity_type_id == 'user' || $entity_type_id == 'comment' || $entity_type_id == 'file') {
        $bundle = $entity_type_id;
      }
      elseif ($entity_type_id == 'group') {
        $bundle = $row->_entity->getGroupType()->id();
      }
      elseif ($entity_type_id == 'group_content') {
        $bundle = $row->_entity->getGroupContentType()->id();
      }
      elseif ($entity_type_id == 'taxonomy_term') {
        $bundle = $row->_entity->getVocabularyId();
      }
      else {
        $bundle = $row->_entity->getType();
      }
    }
    // Check, if the field is in _relationship_entities.
    elseif (isset($row->_relationship_entities)) {
      $relationship_entity = array_keys($row->_relationship_entities);
      foreach ($relationship_entity as $rel) {
        if (isset($row->_relationship_entities[$rel]->{$column})) {
          $entity_id = $row->_relationship_entities[$rel]->id();
          $entity_type_id = $row->_relationship_entities[$rel]->getEntityTypeId();
          if ($entity_type_id == 'user' || $entity_type_id == 'comment') {
            $bundle = $entity_type_id;
          }
          elseif ($entity_type_id == 'group') {
            $bundle = $row->_relationship_entities[$rel]->getGroupType()->id();
          }
          elseif ($entity_type_id == 'group_content') {
            $bundle = $row->_relationship_entities[$rel]->getGroupContentType()->id();
          }
          elseif ($entity_type_id == 'taxonomy_term') {
            $bundle = $row->_relationship_entities[$rel]->getVocabularyId();
          }
          else {
            $bundle = $row->_relationship_entities[$rel]->getType();
          }
        }
      }
    }
    if (!isset($entity_id) || !isset($entity_type_id) || !isset($bundle)) {
      return;
    }
    else {
      // Get the widgets assigned to this entity.
      $query = \Drupal::entityQuery('rate_widget');
      $query->accessCheck(TRUE);
      $query->condition('entity_types.*', [$entity_type_id . '.' . $bundle], 'IN');
      $widget_ids = $query->execute();

      // Exit if this entity:bundle has no rate widgets attached.
      if (!isset($widget_ids) || count($widget_ids) == 0) {
        return;
      }
      if (isset($widget_ids)) {
        if (count($widget_ids) > 1) {
          // Check if we have a widget selected in view field settings.
          $selected_widget = $widgets[$entity_type_id . ':' . $bundle]['widget'];
          if (isset($selected_widget) && in_array($selected_widget, $widget_ids)) {
            $widget = $selected_widget;
          }
          else {
            // Get the first widget.
            $widget = array_shift($widget_ids);
          }
        }
        else {
          // Get the first widget.
          $widget = array_shift($widget_ids);
        }
      }
      if (!isset($widget)) {
        return;
      }
      $widget_name = $widget;
      $widget = $widget_storage->load($widget);
      $widget_template = $widget->get('template');
      $value_type = $widget->get('value_type');

      // Apply overrides from the view field settings.
      $display_settings = $widget->get('display');
      $results_settings = $widget->get('results');

      $display_settings['label_position'] = ($display_overrides['hide_label'] === 'hide_label') ? 'hidden' : $display_settings['label_position'];
      $display_settings['description_position'] = ($display_overrides['hide_description'] === 'hide_description') ? 'hidden' : $display_settings['description_position'];
      $results_settings['result_position'] = ($display_overrides['hide_summary'] === 'hide_summary') ? 'hidden' : $results_settings['result_position'];
      $display_settings['readonly'] = ($widget_display == 'readonly') ? 1 : 0;

      $widget->set('display', $display_settings);
      $widget->set('results', $results_settings);

      // Currently using only two vote types - change, if more needed/used.
      $vote_type = ($widget_template == 'fivestar') ? $widget_template : 'updown';

      // Mark the widget being part of a view and add the view url.
      $widget->set('isViewsField', FALSE);
      $widget->set('viewsUrl', $this->view->getUrl());

      // Get the rate widget rating form.
      $form = $rate_widget_base_service->getForm($entity_type_id, $bundle, $entity_id, $vote_type, $value_type, $widget_name, $widget);
      $form = ($widget_display === 'summary') ? $form['#results'] : $form;
      $form_container = [
        'rating' => [
          '#theme' => 'container',
          '#attributes' => [
            'class' => ['rate-widget', $widget_template],
          ],
          '#children' => [
            'form' => $form,
          ],
        ],
        '#attached' => [
          'library' => ['rate/w-' . $widget_template],
        ],
      ];
      $build[$widget_name] = $form_container;
      return $build;
    }
  }

}

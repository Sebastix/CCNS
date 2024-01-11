<?php

/**
 * @file
 * rate.views.inc
 */

/**
 * Implements hook_views_data_alter().
 */
function rate_views_data_alter(array &$data) {
  $entity_types = \Drupal::entityTypeManager()->getDefinitions();

  foreach ($entity_types as $entity_type_id => $entity_type) {
    $data[$entity_type_id][$entity_type_id . '_rate_widget_field'] = [
      'title' => t('Rate widget'),
      'field' => [
        'title' => t('Rate widget'),
        'help' => t('Displays a widget to vote this entity.'),
        'id' => 'rate_widget_field',
      ],
    ];
  }
}

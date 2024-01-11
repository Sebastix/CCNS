<?php

namespace Drupal\rate;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of rate widget entities.
 *
 * @see \Drupal\rate\Entity\RateWidget
 */
class RateWidgetListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['id'] = [
      'data' => $this->t('Machine name'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['template'] = [
      'data' => $this->t('Template'),
    ];
    $header['value_type'] = [
      'data' => $this->t('Value type'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['entity_types'] = [
      'data' => $this->t('Entities'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $comment_module_enabled = \Drupal::service('module_handler')->moduleExists('comment');
    $comment_header = ($comment_module_enabled) ? $this->t('Comment') : $this->t('Comment (disabled)');
    $header['comment_types'] = [
      'data' => $comment_header,
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];
    $row['id'] = $entity->id();
    $row['template'] = $entity->get('template');
    $row['value_type'] = $entity->get('value_type');
    $row['entity_types'] = [
      'data' => [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $entity->get('entity_types'),
      ],
    ];
    $row['comment_types'] = [
      'data' => [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $entity->get('comment_types'),
      ],
    ];
    return $row + parent::buildRow($entity);
  }

}

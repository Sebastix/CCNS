<?php

namespace Drupal\Tests\rate\Traits;

use Drupal\rate\Entity\RateWidget;

/**
 * Trait to assist rate widget creation for tests.
 */
trait RateWidgetCreateTrait {

  /**
   * Helper function to create and save a rate widget entity.
   *
   * @param string $id
   *   The rate widget machine_name.
   * @param string $label
   *   The rate widget label.
   * @param string $template
   *   The rate widget template.
   * @param array $options
   *   The value options array for the rate widget.
   * @param array $entity_types
   *   The entity types the rate widget is attached to.
   * @param array $comment_types
   *   The comment types the rate widget is attached to.
   * @param array $voting
   *   The voting settings.
   * @param array $display
   *   The display settings.
   * @param array $results
   *   The results settings.
   *
   * @return \Drupal\rate\RateWidgetInterface
   *   A saved rate widget entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRateWidget($id = NULL, $label = NULL, $template = NULL, array $options = [], array $entity_types = [], array $comment_types = [], array $voting = [], array $display = [], array $results = []) {
    // Set defaults, if not provided in call.
    $id = $id ?: mb_strtolower($this->randomMachineName());
    $label = $label ?: $this->randomString();
    $template = $template ?: 'yesno';
    $voting = $voting ?: ['use_deadline' => 0];

    $rate_widget = RateWidget::Create([
      'id' => $id,
      'label' => $label,
      'template' => $template,
      'options' => $options,
      'entity_types' => $entity_types,
      'comment_types' => $comment_types,
      'voting' => $voting,
      'display' => $display,
      'results' => $results,
    ]);
    $rate_widget->save();

    return $rate_widget;
  }

}

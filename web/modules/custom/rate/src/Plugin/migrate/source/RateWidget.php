<?php

namespace Drupal\rate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\DummyQueryTrait;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Rate widget migrate source plugin.
 *
 * @MigrateSource(
 *   id = "rate_widget",
 *   source_module = "rate"
 * )
 */
class RateWidget extends DrupalSqlBase {

  use DummyQueryTrait;

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => 'Name',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'name' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $rate_field_widgets = $this->getDatabase()
      ->select('variable', 'v')
      ->fields('v', ['value'])
      ->condition('v.name', 'rate_widgets')
      ->execute()
      ->fetchField();

    $unserialised_rate_field_widgets = unserialize($rate_field_widgets);
    $array_reduce = array_reduce(
     $unserialised_rate_field_widgets,
      function (array $data, object $item) {
        $data[] = (array) $item;
        return $data;
      },
      []
    );
    return new \ArrayIterator($array_reduce);
  }

  /**
   * {@inheritdoc}
   */
  public function count($refresh = FALSE): int {
    return (int) $this->initializeIterator()->count();
  }

}

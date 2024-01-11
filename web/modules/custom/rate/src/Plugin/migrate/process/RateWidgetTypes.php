<?php

namespace Drupal\rate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Maps D7 settings to D9 values.
 *
 * @MigrateProcessPlugin(
 *   id = "rate_widgets_process_types",
 *   handle_multiples = TRUE
 * )
 */
class RateWidgetTypes extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result = [];
    foreach ($value as $n_type) {
      array_push($result, 'node.' . $n_type);
    }
    return $result;
  }

}

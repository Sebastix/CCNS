<?php

namespace Drupal\rate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Maps D7 settings to D9 values.
 *
 * @MigrateProcessPlugin(
 *   id = "rate_widgets_process_options",
 *   handle_multiples = TRUE
 * )
 */
class RateWidgetOptions extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    [$options, $template] = $value;
    if ($template === 'slider') {
      return [
        0 => [
          'value' => '10',
          'label' => '1',
          'class' => '',
        ],
        1 => [
          'value' => '20',
          'label' => '2',
          'class' => '',
        ],
        2 => [
          'value' => '30',
          'label' => '3',
          'class' => '',
        ],
        3 => [
          'value' => '40',
          'label' => '4',
          'class' => '',
        ],
        4 => [
          'value' => '50',
          'label' => '5',
          'class' => '',
        ],
        5 => [
          'value' => '60',
          'label' => '6',
          'class' => '',
        ],
        6 => [
          'value' => '70',
          'label' => '7',
          'class' => '',
        ],
        7 => [
          'value' => '80',
          'label' => '8',
          'class' => '',
        ],
        8 => [
          'value' => '90',
          'label' => '9',
          'class' => '',
        ],
        9 => [
          'value' => '100',
          'label' => '10',
          'class' => '',
        ],
      ];
    }

    $temp = [];
    foreach ($options as $opt) {
      array_push($temp, [
        'value' => $opt[0],
        'label' => $opt[1],
        'class' => '',
      ]);
    }

    return $temp;
  }

}

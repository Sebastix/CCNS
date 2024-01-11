<?php

namespace Drupal\Tests\rate\Kernel\migrate;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

/**
 * Tests D7 rate migration plugin.
 *
 * @covers \Drupal\rate\Plugin\migrate\source\RateWidgetTypes
 * @covers \Drupal\rate\Plugin\migrate\source\RateWidgetOptions
 * @group rate
 */
class RateWidgetMigrationTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rate',
    'votingapi',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getFixtureFilePath() {
    return implode(DIRECTORY_SEPARATOR, [
      drupal_get_path('module', 'rate'),
      'tests',
      'fixtures',
      'drupal7.php',
    ]);
  }

  /**
   * Tests votingapi migration.
   */
  public function testVoteMigration() {
    $this->installEntitySchema('vote');
    $this->startCollectingMessages();
    // d7_vote_type migration needs to be executed before any d7_vote migration.
    // @see Drupal\Tests\votingapi\Kernel\migrate\VoteMigrationTest.
    $this->executeMigrations(['d7_vote_type', 'd7_vote:comment:article']);
    $this->executeMigrations(['d7_vote']);
    $this->assertNoMigrationMessages();
    $storage = \Drupal::entityTypeManager()->getStorage('vote');
    assert($storage instanceof EntityStorageInterface);
    $votes = $storage->loadMultiple();
    $this->assertCount(5, $votes);
    $array_1 = $votes[1]->toArray();
    $this->assertEquals($array_1['rate_widget'][0], ['value' => 'fivestar_test']);
    $this->assertEquals($array_1['type'][0], ['target_id' => 'fivestar']);
  }

  /**
   * Tests rate migration.
   */
  public function testRateWidgets() {
    $this->startCollectingMessages();
    $this->executeMigration('d7_rate_widgets');
    $this->assertNoMigrationMessages();
    $storage = \Drupal::entityTypeManager()->getStorage('rate_widget');
    assert($storage instanceof EntityStorageInterface);
    $rate_widgets = $storage->loadMultiple();
    $this->assertCount(8, $rate_widgets);
    $this->assertEquals(
    [
      0 => 'fivestar_test',
      1 => 'thumbs_up_down_test',
      2 => 'yes_no_test',
      3 => 'thumbs_up_test',
      4 => 'number_up_down_test',
      5 => 'emotion_test',
      6 => 'slider_test',
      7 => 'custom_test',
    ],
    array_keys($rate_widgets));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'fivestar_test',
      'label' => 'fivestar_test',
      'template' => 'fivestar',
      'value_type' => 'percent',
      'entity_types' => [
        'node.article',
      ],
      'comment_types' => [
        'node.article',
      ],
      'options' => [
        [
          'value' => '0',
          'label' => '1',
          'class' => '',
        ],
        [
          'value' => '25',
          'label' => '2',
          'class' => '',
        ],
        [
          'value' => '56',
          'label' => '3',
          'class' => '',
        ],
        [
          'value' => '77',
          'label' => '4',
          'class' => '',
        ],
        [
          'value' => '100',
          'label' => '5',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => 'rate me please',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
      $rate_widgets['fivestar_test']->toArray(),
      ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'number_up_down_test',
      'label' => 'number_up_down_test ',
      'template' => 'numberupdown',
      'value_type' => 'points',
      'entity_types' => [],
      'comment_types' => [],
      'options' => [
        0 => [
          'value' => '1',
          'label' => '+1',
          'class' => '',
        ],
        1 => [
          'value' => '-1',
          'label' => '-1',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => '',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
      array_diff_key(
      $rate_widgets['number_up_down_test']->toArray(),
      ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'thumbs_up_down_test',
      'label' => 'thumbs_up_down_test',
      'template' => 'thumbsupdown',
      'value_type' => 'points',
      'entity_types' => [
        0 => 'node.page',
      ],
      'comment_types' => [
        0 => 'node.page',
      ],
      'options' => [
        0 => [
          'value' => '1',
          'label' => 'up',
          'class' => '',
        ],
        1 => [
          'value' => '-1',
          'label' => 'down',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => '',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['thumbs_up_down_test']->toArray(),
    ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'yes_no_test',
      'label' => 'yes_no_test',
      'template' => 'yesno',
      'value_type' => 'option',
      'entity_types' => [
        0 => 'node.page',
      ],
      'comment_types' => [
        0 => 'node.page',
      ],
      'options' => [
        0 => [
          'value' => '1',
          'label' => 'yes',
          'class' => '',
        ],
        1 => [
          'value' => '2',
          'label' => 'no',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => 'Famous sites for tourists',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['yes_no_test']->toArray(),
    ['uuid' => 'uuid']
    ));
    $this->assertEquals(
    [
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'thumbs_up_test',
      'label' => 'thumbs_up_test',
      'template' => 'thumbsup',
      'value_type' => 'points',
      'entity_types' => [
        0 => 'node.inline_test',
      ],
      'comment_types' => [],
      'options' => [
        0 => [
          'value' => '1',
          'label' => 'up',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => 'Famous sites for tourists',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['thumbs_up_test']->toArray(),
    ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'emotion_test',
      'label' => 'emotion_test',
      'template' => 'emotion',
      'value_type' => 'option',
      'entity_types' => [],
      'comment_types' => [
        0 => 'node.page',
        1 => 'node.inline_test',
      ],
      'options' => [
        0 => [
          'value' => '1',
          'label' => 'funny',
          'class' => '',
        ],
        1 => [
          'value' => '2',
          'label' => 'mad',
          'class' => '',
        ],
        2 => [
          'value' => '3',
          'label' => 'angry',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => '',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['emotion_test']->toArray(),
    ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'slider_test',
      'label' => 'slider_test',
      'template' => 'custom',
      'value_type' => 'percent',
      'entity_types' => [
        0 => 'node.page',
      ],
      'comment_types' => [
        0 => 'node.article',
      ],
      'options' => [
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
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => '',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['slider_test']->toArray(),
    ['uuid' => 'uuid']
    ));
    $this->assertEquals([
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'custom_test',
      'label' => 'custom_test',
      'template' => 'custom',
      'value_type' => 'option',
      'entity_types' => [],
      'comment_types' => [],
      'options' => [
        0 => [
          'value' => '20',
          'label' => 'value 1',
          'class' => '',
        ],
        1 => [
          'value' => '80',
          'label' => 'value 2',
          'class' => '',
        ],
      ],
      'voting' => [
        'use_deadline' => 0,
        'anonymous_window' => -2,
        'user_window' => -2,
      ],
      'display' => [
        'display_label' => '',
        'label_class' => '',
        'label_position' => 'above',
        'description_class' => '',
        'description_position' => 'below',
        'readonly' => 0,
        'description' => '',
      ],
      'results' => [
        'result_position' => 'right',
        'result_type' => 'user_vote_empty',
      ],
    ],
    array_diff_key(
    $rate_widgets['custom_test']->toArray(),
    ['uuid' => 'uuid']
    ));
  }

  /**
   * Checks migration messages & shows dev friendly output if there are errors.
   */
  public function assertNoMigrationMessages() {
    $messages_as_strings = [];
    $dummies = [];
    foreach ($this->migrateMessages as $type => $messages) {
      foreach ($messages as $message) {
        $messages_as_strings[$type][] = (string) $message;
      }
      $dummies[$type] = array_fill(0, count($messages), '...');
    }
    $this->assertEquals($dummies, $messages_as_strings);
  }

}

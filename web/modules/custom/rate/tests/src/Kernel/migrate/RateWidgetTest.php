<?php

namespace Drupal\Tests\rate\Kernel\migrate;

use Drupal\Tests\migrate\Kernel\MigrateSqlSourceTestBase;

/**
 * Tests D7 rate source plugin.
 *
 * @covers \Drupal\rate\Plugin\migrate\source\RateWidget
 * @group rate
 */
class RateWidgetTest extends MigrateSqlSourceTestBase {

  /**
   * Modules needed for tests.
   *
   * @var array
   */
  protected static $modules = [
    'migrate_drupal',
    'votingapi',
    'rate',
  ];

  /**
   * Returns an array for the expected data.
   *
   * @return array
   *   Array containing the expected data.
   */
  public function providerSource(): array {
    return [
      'first test case' => [
        'source_data' => [
          'variable' => [
            [
              'name' => 'rate_widgets',
              'value' => 'a:2:{i:1;O:8:"stdClass":29:{s:4:"name";s:17:"something_to_vote";s:3:"tag";s:4:"vote";s:5:"title";s:25:"something to vote lol 123";s:10:"node_types";a:1:{i:0;s:7:"article";}s:13:"comment_types";a:1:{i:0;s:7:"article";}s:7:"options";a:5:{i:0;a:2:{i:0;s:1:"0";i:1;s:1:"1";}i:1;a:2:{i:0;s:2:"25";i:1;s:1:"2";}i:2;a:2:{i:0;s:2:"50";i:1;s:1:"3";}i:3;a:2:{i:0;s:2:"75";i:1;s:1:"4";}i:4;a:2:{i:0;s:3:"100";i:1;s:1:"5";}}s:8:"template";s:8:"fivestar";s:12:"node_display";s:1:"2";s:14:"teaser_display";b:0;s:15:"comment_display";s:1:"2";s:17:"node_display_mode";s:1:"1";s:19:"teaser_display_mode";s:1:"1";s:20:"comment_display_mode";s:1:"1";s:5:"roles";a:3:{i:3;i:0;i:1;i:0;i:2;i:0;}s:22:"allow_voting_by_author";i:1;s:16:"noperm_behaviour";s:1:"1";s:9:"displayed";s:1:"1";s:20:"displayed_just_voted";s:1:"2";s:11:"description";s:14:"rate me please";s:22:"description_in_compact";b:1;s:27:"delete_vote_on_second_click";s:1:"1";s:22:"use_source_translation";b:1;s:10:"value_type";s:7:"percent";s:5:"theme";s:22:"rate_template_fivestar";s:3:"css";s:62:"sites/all/modules/contrib/rate/templates/fivestar/fivestar.css";s:2:"js";s:61:"sites/all/modules/contrib/rate/templates/fivestar/fivestar.js";s:9:"translate";b:1;s:10:"expiration";s:2:"-1";s:25:"expiration_allow_override";b:0;}i:2;O:8:"stdClass":28:{s:4:"name";s:7:"rate_22";s:3:"tag";s:4:"vote";s:5:"title";s:7:"Rate 22";s:10:"node_types";a:1:{i:0;s:4:"page";}s:13:"comment_types";a:1:{i:0;s:4:"page";}s:7:"options";a:2:{i:0;a:2:{i:0;i:1;i:1;s:2:"up";}i:1;a:2:{i:0;i:-1;i:1;s:4:"down";}}s:8:"template";s:14:"thumbs_up_down";s:12:"node_display";s:1:"2";s:14:"teaser_display";b:0;s:15:"comment_display";s:1:"2";s:17:"node_display_mode";s:1:"1";s:19:"teaser_display_mode";s:1:"1";s:20:"comment_display_mode";s:1:"1";s:5:"roles";a:3:{i:3;i:0;i:1;i:0;i:2;i:0;}s:22:"allow_voting_by_author";i:1;s:16:"noperm_behaviour";s:1:"1";s:9:"displayed";s:1:"1";s:20:"displayed_just_voted";s:1:"2";s:11:"description";s:0:"";s:22:"description_in_compact";b:1;s:27:"delete_vote_on_second_click";s:1:"0";s:22:"use_source_translation";b:1;s:10:"value_type";s:6:"points";s:5:"theme";s:28:"rate_template_thumbs_up_down";s:3:"css";s:74:"sites/all/modules/contrib/rate/templates/thumbs-up-down/thumbs-up-down.css";s:9:"translate";b:0;s:10:"expiration";s:2:"-1";s:25:"expiration_allow_override";b:0;}}',
            ],
          ],
          'system' => [
            [
              'filename' => 'modules/system/system.module',
              'name' => 'system',
              'type' => 'module',
              'status' => '1',
              'schema_version' => '7055',
            ],
            [
              'filename' => 'modules/rate/rate.module',
              'name' => 'rate',
              'type' => 'module',
              'status' => '1',
              'schema_version' => '7055',
            ],
          ],
        ],
        'expected_data' => [
          [
            'name' => 'something_to_vote',
            'tag' => 'vote',
            'title' => 'something to vote lol 123',
            'node_types' => [
              0 => 'article',
            ],
            'comment_types' => [
              0 => 'article',
            ],
            'options' => [
              0 => [
                0 => '0',
                1 => '1',
              ],
              1 => [
                0 => '25',
                1 => '2',
              ],
              2 => [
                0 => '50',
                1 => '3',
              ],
              3 => [
                0 => '75',
                1 => '4',
              ],
              4 => [
                0 => '100',
                1 => '5',
              ],
            ],
            'template' => 'fivestar',
            'node_display' => '2',
            'teaser_display' => FALSE,
            'comment_display' => '2',
            'node_display_mode' => '1',
            'teaser_display_mode' => '1',
            'comment_display_mode' => '1',
            'roles' => [
              3 => 0,
              1 => 0,
              2 => 0,
            ],
            'allow_voting_by_author' => 1,
            'noperm_behaviour' => '1',
            'displayed' => '1',
            'displayed_just_voted' => '2',
            'description' => 'rate me please',
            'description_in_compact' => TRUE,
            'delete_vote_on_second_click' => '1',
            'use_source_translation' => TRUE,
            'value_type' => 'percent',
            'theme' => 'rate_template_fivestar',
            'css' => 'sites/all/modules/contrib/rate/templates/fivestar/fivestar.css',
            'js' => 'sites/all/modules/contrib/rate/templates/fivestar/fivestar.js',
            'translate' => TRUE,
            'expiration' => '-1',
            'expiration_allow_override' => FALSE,
          ],
          [
            'name' => 'rate_22',
            'tag' => 'vote',
            'title' => 'Rate 22',
            'node_types' => [
              0 => 'page',
            ],
            'comment_types' => [
              0 => 'page',
            ],
            'options' => [
              0 => [
                0 => 1,
                1 => 'up',
              ],
              1 => [
                0 => -1,
                1 => 'down',
              ],
            ],
            'template' => 'thumbs_up_down',
            'node_display' => '2',
            'teaser_display' => FALSE,
            'comment_display' => '2',
            'node_display_mode' => '1',
            'teaser_display_mode' => '1',
            'comment_display_mode' => '1',
            'roles' => [
              3 => 0,
              1 => 0,
              2 => 0,
            ],
            'allow_voting_by_author' => 1,
            'noperm_behaviour' => '1',
            'displayed' => '1',
            'displayed_just_voted' => '2',
            'description' => '',
            'description_in_compact' => TRUE,
            'delete_vote_on_second_click' => '0',
            'use_source_translation' => TRUE,
            'value_type' => 'points',
            'theme' => 'rate_template_thumbs_up_down',
            'css' => 'sites/all/modules/contrib/rate/templates/thumbs-up-down/thumbs-up-down.css',
            'translate' => FALSE,
            'expiration' => '-1',
            'expiration_allow_override' => FALSE,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $id = $this->randomMachineName(16);
    $this->migration->id()->willReturn($id);
    $this->migration->getBaseId()->willReturn($id);
  }

}

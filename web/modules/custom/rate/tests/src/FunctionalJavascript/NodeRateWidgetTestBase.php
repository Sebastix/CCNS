<?php

namespace Drupal\Tests\rate\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\rate\Traits\AssertRateWidgetTrait;
use Drupal\Tests\rate\Traits\NodeVoteTrait;
use Drupal\Tests\rate\Traits\RateWidgetCreateTrait;

/**
 * Base class for Node Rate Widget tests.
 *
 * @package Drupal\Tests\rate\FunctionalJavascript
 */
abstract class NodeRateWidgetTestBase extends WebDriverTestBase {

  use RateWidgetCreateTrait;
  use AssertRateWidgetTrait;
  use NodeVoteTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'node',
    'datetime',
    'rate',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An array of users.
   *
   * @var \Drupal\user\UserInterface[]
   */
  protected $users;

  /**
   * An array of nodes.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();

    $this->nodes['article'][1] = $this->drupalCreateNode([
      'type' => 'article',
      'nid' => 1,
    ])->save();

    $this->nodes['article'][2] = $this->drupalCreateNode([
      'type' => 'article',
      'nid' => 2,
    ])->save();
  }

}

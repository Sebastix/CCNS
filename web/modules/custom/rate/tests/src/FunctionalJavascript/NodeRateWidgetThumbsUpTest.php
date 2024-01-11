<?php

namespace Drupal\Tests\rate\FunctionalJavascript;

use Drupal\rate\Entity\RateWidget;

/**
 * Tests for the "Thumbs Up" widget.
 *
 * @group rate
 */
class NodeRateWidgetThumbsUpTest extends NodeRateWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the rate widget.
    $options = [
      ['value' => 1, 'label' => 'Up'],
    ];
    $this->createRateWidget('thumbs_up', 'Thumbs Up', 'thumbsup', $options, ['node.article']);

    // Reset any static cache.
    drupal_static_reset();

    // Verify the new widget has been added correctly.
    $rate_widget = RateWidget::load('thumbs_up');
    $this->assertEquals($rate_widget->getLabel(), 'Thumbs Up');

    $permissions = [
      'access content',
      'cast rate vote on node of article',
    ];
    $this->users[1] = $this->createUser($permissions);
    $this->users[2] = $this->createUser($permissions);
    $this->users[3] = $this->createUser($permissions);
  }

  /**
   * Tests voting.
   */
  public function testVoting() {
    // Log in as first user and vote 'Up'.
    $this->drupalLogin($this->users[1]);
    $this->drupalGet('node/1');
  }

}

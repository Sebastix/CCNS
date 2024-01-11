<?php

namespace Drupal\Tests\rate\FunctionalJavascript;

use Drupal\rate\Entity\RateWidget;

/**
 * Tests for the "Fivestar" widget.
 *
 * @group rate
 */
class NodeRateWidgetFivestarTest extends NodeRateWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the rate widget.
    $options = [
      ['value' => 1, 'label' => 'Star 1'],
      ['value' => 2, 'label' => 'Star 2'],
      ['value' => 3, 'label' => 'Star 3'],
      ['value' => 4, 'label' => 'Star 4'],
      ['value' => 5, 'label' => 'Star 5'],
    ];
    $this->createRateWidget('fivestar', 'Fivestar', 'fivestar', $options, ['node.article']);

    // Reset any static cache.
    drupal_static_reset();

    // Verify the new widget has been added correctly.
    $rate_widget = RateWidget::load('fivestar');
    $this->assertEquals($rate_widget->getLabel(), 'Fivestar');

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
    // Log in as first user and vote 5 stars.
    $this->drupalLogin($this->users[1]);
    $this->drupalGet('node/1');

    // Log in as different user and vote 3 stars.
    $this->drupalLogin($this->users[2]);
    $this->drupalGet('node/1');
  }

}

<?php

namespace Drupal\Tests\rate\FunctionalJavascript;

use Drupal\rate\Entity\RateWidget;

/**
 * Tests for the "Yes / No" widget.
 *
 * @group rate
 */
class NodeRateWidgetYesNoTest extends NodeRateWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the rate widget.
    $options = [
      ['value' => 1, 'label' => 'Yes'],
      ['value' => -1, 'label' => 'No'],
    ];
    $this->createRateWidget('yesno', 'Yes / No', 'yesno', $options, ['node.article']);

    // Reset any static cache.
    drupal_static_reset();

    // Verify the new widget has been added correctly.
    $rate_widget = RateWidget::load('yesno');
    $this->assertEquals($rate_widget->getLabel(), 'Yes / No');

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
    // Log in as first user and vote 'Yes'.
    $this->drupalLogin($this->users[1]);
    $this->drupalGet('node/1');
  }

}

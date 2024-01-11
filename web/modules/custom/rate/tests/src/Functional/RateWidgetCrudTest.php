<?php

namespace Drupal\Tests\rate\Functional;

/**
 * Testing the CRUD functionality for the Rate widget entity.
 *
 * @group rate
 */
class RateWidgetCrudTest extends RateWidgetTestBase {

  /**
   * Creating/reading/updating/deleting the rate widget entity and test it.
   */
  public function testCrudEntityType() {
    // Create the rate widget.
    $options = [];
    $entity_types = ['node.article'];
    $comment_types = [];
    $voting = ['use_deadline' => 0];
    $display = [];
    $results = [];

    // Anonymous users don't have access to the node rating page.
    $this->drupalGet(sprintf('node/%s/node-rating', $this->nodes['article'][1]->id()));
    $this->assertSession()->statusCodeEquals(403);

    // When logged in, but no widget is configured, the node rating returns a
    // 404 error.
    $this->drupalLogin($this->rootUser);
    $this->drupalGet(sprintf('node/%s/node-rating', $this->nodes['article'][1]->id()));
    $this->assertSession()->statusCodeEquals(404);

    $created_rate_widget = $this->createRateWidget('dummy_rate_widget', 'Dummy rate widget', 'fivestar', $options, $entity_types, $comment_types, $voting, $display, $results);

    // Reset any static cache.
    drupal_static_reset();

    // A rate widget was configured. The node rating page now returns a valid
    // response.
    $this->drupalGet(sprintf('node/%s/node-rating', $this->nodes['article'][1]->id()));
    $this->assertSession()->statusCodeEquals(200);

    // Load the rate widget and verify its structure.
    $rate_widget = $this->loadRateWidget('dummy_rate_widget');

    $values = [
      'Label' => 'Label',
      'Options' => 'Options',
    ];
    foreach ($values as $key => $label) {
      $this->assertEquals(
        call_user_func([$rate_widget, 'get' . $key]),
        call_user_func([$created_rate_widget, 'get' . $key]),
        'The ' . $label . ' between the rate widget we created and loaded were not the same');
    }

    // Verifying updating action.
    $rate_widget->setLabel('New label');
    $rate_widget->save();

    // Reset any static cache.
    drupal_static_reset();

    $rate_widget = $this->loadRateWidget('dummy_rate_widget');
    $this->assertEquals($rate_widget->getLabel(), 'New label', 'The rate widget was updated successfully');

    // Delete the rate widget any try to load it from the DB.
    $rate_widget->delete();

    // Reset any static cache.
    drupal_static_reset();

    $this->assertNull($this->loadRateWidget('dummy_rate_widget'), 'The rate widget was not found in the DB');
  }

}

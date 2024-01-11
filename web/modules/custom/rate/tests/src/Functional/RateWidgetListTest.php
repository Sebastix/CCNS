<?php

namespace Drupal\Tests\rate\Functional;

/**
 * Testing the listing functionality for the Rate widget entity.
 *
 * @group rate
 */
class RateWidgetListTest extends RateWidgetTestBase {

  /**
   * The user object.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Listing of rate widgets.
   */
  public function testEntityTypeList() {
    $this->user = $this->drupalCreateUser(['administer rate']);
    $this->drupalLogin($this->user);

    $this->drupalGet('admin/structure/rate_widgets');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(200);
  }

}

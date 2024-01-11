<?php

namespace Drupal\Tests\rate\Traits;

/**
 * Assert methods to verify results of voting for a single node.
 *
 * This trait is meant to be used only by Functional and FunctionalJavascript
 * test classes.
 */
trait AssertRateWidgetTrait {

  /**
   * Assert "Fivestar".
   *
   * @param int $expected
   *   The expected number of stars (from 0 to 5).
   */
  public function assertFivestar($expected) {
    $session = $this->assertSession();
    $session->elementExists('css', '.rate-widget-fivestar');
    if ($expected > 0) {
      $session->elementExists('css', '.rate-fivestar-btn-filled.rate-fivestar-' . $expected);
    }
    if ($expected < 5) {
      $session->elementExists('css', '.rate-fivestar-btn-empty.rate-fivestar-' . ++$expected);
    }
  }

  /**
   * Assert "Fivestar" by node id.
   *
   * @param int $nid
   *   The node id.
   * @param int $expected
   *   The expected number of stars (from 0 to 5).
   */
  protected function assertFivestarById($nid, $expected) {
    $session = $this->assertSession();
    $node_selector = '[data-drupal-selector="rate-node-' . $nid . '"]';

    $session->elementExists('css', '.rate-fivestar-1');

    if ($expected > 0) {
      $session->elementExists('css', $node_selector . ' .rate-fivestar-btn-filled.rate-fivestar-' . $expected);
    }
    if ($expected < 5) {
      $session->elementExists('css', $node_selector . ' .rate-fivestar-btn-empty.rate-fivestar-' . ++$expected);
    }
  }

  /**
   * Assert "Number Up / Down".
   *
   * @param string $expected
   *   The expected result of voting (e.g. '-1' or '+2').
   */
  public function assertNumberUpDown($expected) {
    $session = $this->assertSession();
    $session->elementExists('css', '.rate-widget-number-up-down');
    $session->elementTextContains('css', '.rate-number-up-down-rating', $expected);
  }

  /**
   * Assert "Thumbs Up / Down".
   *
   * @param int $expected_up
   *   The expected result of voting for 'Up'. Result in percent (e.g. 67).
   * @param int $expected_down
   *   The expected result of voting for 'Down'. Result in percent (e.g. 33).
   */
  public function assertThumbsUpDown($expected_up, $expected_down) {
    $session = $this->assertSession();
    $session->elementExists('css', '.rate-widget-thumbs-up-down');
    $session->elementTextContains('css', '.thumb-up', $expected_up . '%');
    $session->elementTextContains('css', '.thumb-down', $expected_down . '%');
  }

  /**
   * Assert "Thumbs Up".
   *
   * @param int $expected
   *   The expected result of voting. Sum of votes (e.g. 2).
   */
  public function assertThumbsUp($expected) {
    $session = $this->assertSession();
    $session->elementExists('css', '.rate-widget-thumbs-up');
    $session->elementTextContains('css', '.rate-score', $expected);
  }

  /**
   * Assert "YesNo".
   *
   * @param int $expected_yes
   *   The expected result of voting for 'Yes'. Sum of votes (e.g. 6).
   * @param int $expected_no
   *   The expected result of voting for 'No'. Sum of votes (e.g. 2).
   */
  public function assertYesNo($expected_yes, $expected_no) {
    $session = $this->assertSession();
    $session->elementExists('css', '.rate-widget-yesno');
    $session->elementTextContains('css', '.rate-yes', $expected_yes);
    $session->elementTextContains('css', '.rate-no', $expected_no);
  }

}

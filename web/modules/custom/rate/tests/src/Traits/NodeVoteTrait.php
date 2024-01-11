<?php

namespace Drupal\Tests\rate\Traits;

/**
 * Provides methods to voting on nodes.
 *
 * This trait is meant to be used only by Functional and FunctionalJavascript
 * test classes.
 */
trait NodeVoteTrait {

  /**
   * Vote "Fivestar" by node id.
   *
   * @param int $nid
   *   The node id.
   * @param int $stars
   *   The number of stars vote (from 1 to 5).
   */
  protected function voteFivestarById($nid, $stars) {
    $this->assertSession()->elementExists('css', '.rate-fivestar-1');
    $node_selector = '[data-drupal-selector="rate-node-' . $nid . '"]';
    $this->click($node_selector . ' .rate-fivestar-' . $stars);
  }

  /**
   * Un-vote "Fivestar" by node id.
   *
   * @param int $nid
   *   The node id.
   */
  protected function unVoteFivestarById($nid) {
    $this->assertSession()->elementExists('css', '.rate-fivestar-1');
    $node_selector = '[data-drupal-selector="rate-node-' . $nid . '"]';
    $this->click($node_selector . ' .rate-undo');
  }

  /**
   * Vote Fivestar for the single node.
   *
   * @param int $stars
   *   The number of stars vote (from 1 to 5).
   */
  protected function voteFivestar($stars) {
    $this->assertSession()->elementExists('css', '.rate-widget-fivestar');
    $this->click('.rate-fivestar-' . $stars);
  }

}

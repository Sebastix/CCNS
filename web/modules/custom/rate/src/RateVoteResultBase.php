<?php

namespace Drupal\rate;

use Drupal\votingapi\VoteResultFunctionBase;

/**
 * Base class to handle the vote results for a rate widget.
 */
class RateVoteResultBase extends VoteResultFunctionBase implements RateVoteResultInterface {

  /**
   * Get votes for field.
   */
  public function getVotesForField($votes) {
    $plugin_id  = explode('.', $this->getDerivativeId());
    $field_name = $plugin_id[2];
    foreach ($votes as $key => $vote) {
      if ($vote->rate_widget->value != $field_name) {
        unset($votes[$key]);
      }
    }
    return $votes;
  }

  /**
   * Calculate results.
   */
  public function calculateResult($votes) {
    return count($votes);
  }

}

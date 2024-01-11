<?php

namespace Drupal\rate\Plugin\VoteResultFunction;

use Drupal\rate\RateVoteResultBase;

/**
 * An average of a set of votes.
 *
 * @VoteResultFunction(
 *   id = "rate_sum",
 *   label = @Translation("Sum"),
 *   description = @Translation("The total votes."),
 *   deriver = "Drupal\rate\Plugin\Derivative\RateVoteResultFunction",
 * )
 */
class RateSum extends RateVoteResultBase {

  /**
   * {@inheritdoc}
   */
  public function calculateResult($votes) {
    $total = 0;
    $votes = $this->getVotesForField($votes);
    foreach ($votes as $vote) {
      $total += (int) $vote->getValue();
    }
    return $total;
  }

}

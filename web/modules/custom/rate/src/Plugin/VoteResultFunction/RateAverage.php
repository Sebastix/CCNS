<?php

namespace Drupal\rate\Plugin\VoteResultFunction;

use Drupal\rate\RateVoteResultBase;

/**
 * An average of a set of votes.
 *
 * @VoteResultFunction(
 *   id = "rate_average",
 *   label = @Translation("Average"),
 *   description = @Translation("The average vote value."),
 *   deriver = "Drupal\rate\Plugin\Derivative\RateVoteResultFunction",
 * )
 */
class RateAverage extends RateVoteResultBase {

  /**
   * {@inheritdoc}
   */
  public function calculateResult($votes) {
    $total = 0;
    $votes = $this->getVotesForField($votes);
    foreach ($votes as $vote) {
      $total += (int) $vote->getValue();
    }
    if ($total == 0) {
      return 0;
    }
    return ($total / count($votes));
  }

}

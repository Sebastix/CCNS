<?php

namespace Drupal\rate\Plugin\VoteResultFunction;

use Drupal\rate\RateVoteResultBase;

/**
 * A sum of a set of votes.
 *
 * @VoteResultFunction(
 *   id = "rate_count",
 *   label = @Translation("Count"),
 *   description = @Translation("The number of votes cast."),
 *   deriver = "Drupal\rate\Plugin\Derivative\RateVoteResultFunction",
 * )
 */
class RateCount extends RateVoteResultBase {

  /**
   * {@inheritdoc}
   */
  public function calculateResult($votes) {
    $votes = $this->getVotesForField($votes);
    return count($votes);
  }

}

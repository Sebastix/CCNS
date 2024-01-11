<?php

namespace Drupal\rate\Plugin\VoteResultFunction;

use Drupal\rate\RateVoteResultBase;

/**
 * An average of a set of votes.
 *
 * @VoteResultFunction(
 *   id = "rate_sum_up",
 *   label = @Translation("Sum upvotes"),
 *   description = @Translation("The total upvotes."),
 *   deriver = "Drupal\rate\Plugin\Derivative\RateVoteResultFunction",
 * )
 */
class RateSumUp extends RateVoteResultBase {

  /**
   * {@inheritdoc}
   */
  public function calculateResult($votes) {
    $total = 0;
    $votes = $this->getVotesForField($votes);
    foreach ($votes as $vote) {
      if ($vote->getValue() > 0) {
        $total += (int) $vote->getValue();
      }
    }
    return $total;
  }

}

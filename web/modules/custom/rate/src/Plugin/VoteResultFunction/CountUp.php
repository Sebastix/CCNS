<?php

namespace Drupal\rate\Plugin\VoteResultFunction;

use Drupal\votingapi\VoteResultFunctionBase;

/**
 * The total number of positive votes.
 *
 * @VoteResultFunction(
 *   id = "rate_count_up",
 *   label = @Translation("Number of votes up"),
 *   description = @Translation("The number of positive votes cast.")
 * )
 */
class CountUp extends VoteResultFunctionBase {

  /**
   * {@inheritdoc}
   */
  public function calculateResult($votes) {
    $up = 0;
    foreach ($votes as $vote) {
      if ($vote->getValue() > 0) {
        $up++;
      }
    }
    return $up;
  }

}

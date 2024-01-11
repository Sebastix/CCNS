<?php

namespace Drupal\rate;

/**
 * Interface for rate vote results plugins.
 */
interface RateVoteResultInterface {

  /**
   * Get all votes for a field.
   */
  public function getVotesForField($votes);

}

<?php

namespace Drupal\rate;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Rate widget entity.
 */
interface RateWidgetInterface extends ConfigEntityInterface {

  /**
   * Set the rate widget label.
   *
   * @param string $label
   *   The rate widget label.
   *
   * @return \Drupal\message\RateWidgetInterface
   *   Returns the rate widget instance.
   */
  public function setLabel($label);

  /**
   * Get the rate widget label.
   *
   * @return string
   *   Returns the rate widget label.
   */
  public function getLabel();

  /**
   * Set the available voting buttons as options for the rate widget.
   */
  public function setOptions(array $options);

  /**
   * Return the rate widget voting buttons as options.
   *
   * @return array
   *   Array of the message template settings.
   */
  public function getOptions();

  /**
   * Return a single voting button as option by key.
   *
   * @param string $key
   *   The key to return.
   * @param mixed $default_value
   *   The default value to use in case the key is missing. Defaults to NULL.
   *
   * @return mixed
   *   The value of the option or the default value if none found.
   */
  public function getOption($key, $default_value = NULL);

  /**
   * Check if the rate widget is new.
   *
   * @return bool
   *   Returns TRUE is the rate widget is new.
   */
  public function isLocked();

}

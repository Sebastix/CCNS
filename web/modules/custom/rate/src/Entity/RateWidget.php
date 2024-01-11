<?php

namespace Drupal\rate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\rate\RateWidgetInterface;

/**
 * Defines the Rate Widget configuration entity.
 *
 * @ConfigEntityType(
 *   id = "rate_widget",
 *   label = @Translation("Rate widget"),
 *   config_prefix = "rate_widget",
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\rate\RateWidgetForm",
 *       "edit" = "Drupal\rate\RateWidgetForm",
 *       "delete" = "Drupal\rate\Form\RateWidgetDeleteForm",
 *       "template" = "Drupal\rate\RateWidgetForm",
 *     },
 *     "list_builder" = "Drupal\rate\RateWidgetListBuilder",
 *   },
 *   admin_permission = "administer rate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/rate/add",
 *     "edit-form" = "/admin/structure/rate/{rate_widget}/edit",
 *     "delete-form" = "/admin/structure/rate/{rate_widget}/delete",
 *     "template-form" = "/admin/structure/rate/{rate_widget}/template",
 *     "collection" = "/admin/structure/rate_widgets"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "template",
 *     "value_type",
 *     "entity_types",
 *     "comment_types",
 *     "options",
 *     "voting",
 *     "display",
 *     "results",
 *   }
 * )
 */
class RateWidget extends ConfigEntityBase implements RateWidgetInterface {

  /**
   * The machine name of this rate widget.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the rate widget.
   *
   * @var string
   */
  protected $label;

  /**
   * The template for the rate widget.
   *
   * @var string
   */
  protected $template;

  /**
   * The entities the rate widget is attached to.
   *
   * @var array
   */
  protected $entity_types = [];

  /**
   * The comments the rate widget is attached to.
   *
   * @var array
   */
  protected $comment_types = [];

  /**
   * The the options to vote on.
   *
   * @var array
   */
  protected $options = [];

  /**
   * The voting settings of the widget.
   *
   * @var array
   */
  protected $voting = [];

  /**
   * The display settings of the widget.
   *
   * @var array
   */
  protected $display = [];

  /**
   * The result settings of the widget.
   *
   * @var array
   */
  protected $results = [];

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($key, $default_value = NULL) {
    if (isset($this->options[$key])) {
      return $this->options[$key];
    }
    return $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return !$this->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Check if the widget used a deadline field and delete it.
    foreach ($entities as $rate_widget) {
      $use_deadline = $rate_widget->get('voting')['use_deadline'];
      $current_entities = $rate_widget->get('entity_types');
      $field_name = 'field_rate_vote_deadline';
      if ($use_deadline == 1 && !empty($current_entities)) {
        foreach ($current_entities as $entity) {
          $parameter = explode('.', $entity);
          $field_config = FieldConfig::loadByName($parameter[0], $parameter[1], $field_name);
          if (!empty($field_config)) {
            $field_config->delete();
          }
        }
      }
    }

    // Clear the rate widget cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
    // Clear cached field definitions to remove rate widget extra field.
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
  }

}

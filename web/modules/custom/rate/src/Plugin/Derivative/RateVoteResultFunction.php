<?php

namespace Drupal\rate\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;

/**
 * Deriver base class for rate widget vote calculations.
 */
class RateVoteResultFunction extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityField;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityType;

  /**
   * Constructs a RateVoteResultFunction instance.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityField = $entity_field_manager;
    $this->entityType = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];
    $widgets = $this->entityType->getStorage('rate_widget')->loadMultiple();
    if ($widgets) {
      foreach ($widgets as $widget => $widget_variables) {
        $entities = $widget_variables->get('entity_types');
        if ($entities) {
          foreach ($entities as $entity) {
            $parameter = explode('.', $entity);
            $plugin_id = $parameter[0] . '.' . $parameter[1] . '.' . $widget;
            $this->derivatives[$plugin_id] = $base_plugin_definition;
          }
        }
      }
    }
    return $this->derivatives;
  }

}

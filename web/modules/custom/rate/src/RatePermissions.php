<?php

namespace Drupal\rate;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\comment\CommentManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to return permissions based on entity type for rate module.
 *
 * @package Drupal\rate
 */
class RatePermissions implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The config factory wrapper to fetch settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  protected $commentManager;

  /**
   * Constructs Permissions object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager service, or NULL if not available.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, CommentManagerInterface $comment_manager = NULL) {
    $this->config = $config_factory->get('rate.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->commentManager = $comment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $comment_manager = $container->has('comment.manager') ? $container->get('comment.manager') : NULL;
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $comment_manager
    );
  }

  /**
   * Get permissions for Rate module.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];
    $widgets = $this->entityTypeManager->getStorage('rate_widget')->loadMultiple();

    // No need to continue without widgets.
    if (empty($widgets)) {
      return $permissions;
    }

    foreach ($widgets as $widget) {
      $entities = $widget->get('entity_types');
      if ($entities && count($entities) > 0) {
        foreach ($entities as $entity) {
          $parameter = explode('.', $entity);
          $entity_type_id = $parameter[0];
          $bundle = $parameter[1];
          if ($bundle == $entity_type_id) {
            $perm_index = 'cast rate vote on ' . $entity_type_id . ' of ' . $bundle;
            $permissions[$perm_index] = [
              'title' => $this->t('Can vote on :type', [':type' => $entity_type_id]),
            ];
          }
          else {
            $perm_index = 'cast rate vote on ' . $entity_type_id . ' of ' . $bundle;
            $permissions[$perm_index] = [
              'title' => $this->t('Can vote on :type type of :bundle', [':bundle' => $bundle, ':type' => $entity_type_id]),
            ];
          }
        }
      }

      $comments = $widget->get('comment_types');
      if ($this->commentManager && $comments && count($comments) > 0) {
        foreach ($comments as $comment) {
          $parameter = explode('.', $comment);
          $entity_type_id = $parameter[0];
          $bundle = $parameter[1];
          // Get the comment fields attached to the bundle.
          $fields = $this->commentManager->getFields($parameter[0]);
          foreach ($fields as $fid => $field) {
            if (in_array($bundle, $field['bundles'])) {
              if ($bundle == $entity_type_id) {
                $perm_index = 'cast rate vote on ' . $fid . ' on ' . $entity_type_id . ' of ' . $bundle;
                $permissions[$perm_index] = [
                  'title' => $this->t('Can vote on :comment on :type', [':comment' => $fid, ':type' => $entity_type_id]),
                ];
              }
              else {
                $perm_index = 'cast rate vote on ' . $fid . ' on ' . $entity_type_id . ' of ' . $bundle;
                $permissions[$perm_index] = [
                  'title' => $this->t('Can vote on :comment on :type type of :bundle', [
                    ':comment' => $fid,
                    ':type' => $entity_type_id,
                    ':bundle' => $bundle,
                  ]),
                ];
              }
            }
          }
        }
      }
    }

    return $permissions;
  }

}

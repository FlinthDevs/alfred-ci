<?php

namespace Drupal\vdg_commercial_space\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Commercial space subtitle block.
 *
 * @ExtraFieldDisplay(
 *   id = "commercial_space_subtitle",
 *   label = @Translation("Commercial space subtitle (VDG)"),
 *   bundles = {
 *     "node.commercial_spaces",
 *   }
 * )
 */
class CommercialSpaceSubtitle extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field helper.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityFieldHelperInterface $entity_field_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityFieldHelper = $entity_field_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vdg_core.utility.entity_field_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $type = $this->entityFieldHelper->getvalue($entity, 'field_commercial_space_type');
    $address = $this->entityFieldHelper->getvalue($entity, 'field_address_full');
    $build = [
      '#theme' => 'vdg_commercial_space_chapo',
      '#type' => $type,
      '#address' => $address,
    ];
    return $build;
  }

}

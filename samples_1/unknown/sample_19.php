<?php

namespace Drupal\vdg_block_place\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Informations block.
 *
 * @ExtraFieldDisplay(
 *   id = "block_info",
 *   label = @Translation("Information block (VDG)"),
 *   bundles = {
 *     "block_content.places",
 *   }
 * )
 */
class BlockInfo extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field helper.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldHelperInterface $entity_field_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('vdg_core.utility.entity_field_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Cache\CacheableMetadata $cache */
    $cache = new CacheableMetadata();
    $build = [];

    $place_page = $this->entityFieldHelper->getReferencedEntity($entity, 'field_place_page');
    if ($place_page != NULL) {
      $cache->addCacheableDependency($place_page);

      $poi = $this->entityFieldHelper->getReferencedEntity($place_page, 'field_poi');
      if ($poi != NULL) {
        $cache->addCacheableDependency($poi);

        $coordonates = $this->entityFieldHelper->getgeofieldvalue($poi, 'field_wgs84_coordinate');
        $build = [
          '#theme' => 'vdg_block_place',
          '#title' => $this->entityFieldHelper->getvalue($poi, 'title'),
          '#description' => $this->entityFieldHelper->getvalue($poi, 'field_description'),
          '#content_place_url' => $place_page->toUrl(),
          '#lng' => $coordonates['lon'],
          '#lat' => $coordonates['lat'],
        ];
      }
    }

    $cache->applyTo($build);
    return $build;
  }

}

<?php

namespace Drupal\vdg_city_map\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Place page link.
 *
 * @ExtraFieldDisplay(
 *   id = "place_page_link",
 *   label = @Translation("Place page link (VDG)"),
 *   bundles = {
 *     "node.poi",
 *   }
 * )
 */
class PlacePageLink extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configPage = config_pages_config('config_contents_lists');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $elements = [];
    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);
    $cache->addCacheTags(['node:type:place_page']);

    $node_place_ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'place_page')
      ->condition('field_poi', $entity->id())
      ->condition('status', NodeInterface::PUBLISHED)
      ->range(0, 1)
      ->execute();

    if (!empty($node_place_ids)) {
      $node_place_id = array_shift($node_place_ids);
      $node_place_url = Url::fromRoute('entity.node.canonical', ['node' => $node_place_id]);

      $elements = [
        '#type' => 'link',
        '#title' => $entity->label(),
        '#url' => $node_place_url,
        '#attributes' => [
          'class' => [
            'vdg-place-page-link',
          ],
        ],
      ];
    }

    $cache->applyTo($elements);
    return $elements;
  }

}

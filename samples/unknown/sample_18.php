<?php

namespace Drupal\vdg_block_index\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\vdg_core\Utility\EntityFieldHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Transliterator;

/**
 * Index of menu link selected in index block.
 *
 * @ExtraFieldDisplay(
 *   id = "index_menu_link",
 *   label = @Translation("Index (VDG)"),
 *   bundles = {
 *     "block_content.index",
 *   }
 * )
 */
class Index extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field helper service.
   *
   * @var \Drupal\vdg_core\Utility\EntityFieldHelperInterface
   */
  protected $entityFieldHelper;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldHelperInterface $entity_field_helper,
    MenuLinkTreeInterface $menu_tree
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldHelper = $entity_field_helper;
    $this->menuTree = $menu_tree;
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
      $container->get('vdg_core.utility.entity_field_helper'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $elements = [];
    $results = [];
    $letter_in_results = [];
    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);

    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link */
    $menu_link = $this->entityFieldHelper->getReferencedEntity($entity, 'field_menu');

    if (!empty($menu_link)) {
      $cache->addCacheableDependency($menu_link);
      $cache->addCacheTags(['config:system.menu.' . $menu_link->getMenuName()]);
      $cache->addCacheContexts(['url']);

      $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_link->getMenuName());

      $parameters->setRoot($menu_link->getPluginId());

      $parameters->setMaxDepth(1);

      // Load the tree based on this set of parameters.
      $tree = $this->menuTree->load($menu_link->getMenuName(), $parameters);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];

      $tree = $this->menuTree->transform($tree, $manipulators);

      if (!empty($tree)) {
        $tree = array_shift($tree);

        if (!empty($tree->subtree)) {
          /** @var \Drupal\Core\Menu\MenuLinkTreeElement $child */
          foreach ($tree->subtree as $child) {
            $child_link = $child->link;

            if (!empty($child_link)) {
              $first_letter = $this->getFirstLetter($child_link->getTitle());

              $options = $child_link->getOptions();
              $options = array_merge_recursive($options, [
                'attributes' => [
                  'class' => [
                    'gras',
                  ],
                ],
              ]);

              $results[$first_letter][] = Link::createFromRoute($child_link->getTitle(),
                $child_link->getRouteName(),
                $child_link->getRouteParameters(),
                $options
              )->toString();
            }
          }
          ksort($results);
          $letter_in_results = array_keys($results);
        }
      }

      $elements = [
        '#theme' => 'vdg_block_index_index_result',
        '#letter_list' => range('a', 'z'),
        '#letter_in_results' => $letter_in_results,
        '#index_results' => $results,
      ];
    }

    return $elements;
  }

  /**
   * Get first letter of title to generate index result key.
   *
   * @param string $title
   *   The title of link.
   *
   * @return string
   *   The first letter.
   */
  protected function getFirstLetter(string $title) {
    $first_letter = substr($title, 0, 1);
    $first_letter = strtolower($first_letter);

    // Accent.
    $transliterator = Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    $first_letter = $transliterator->transliterate($first_letter);

    // Number.
    $first_letter = str_replace(range(0, 9), '#', $first_letter);

    return $first_letter;
  }

}

<?php

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

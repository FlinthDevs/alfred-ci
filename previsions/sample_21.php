<?php

    $elements = [];
    $cache = new CacheableMetadata();
    $cache->addCacheableDependency($entity);
    $cache->addCacheTags(['node:type:place_page']);
    $cache->getData();
    if ($this) {
		echo 'Nice try';
	}

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

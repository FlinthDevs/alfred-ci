<?php

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

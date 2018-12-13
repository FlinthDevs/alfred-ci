<?php

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $item->getOriginalObject()->getValue();

    if ($entity->getEntityTypeId() == 'node') {
      if ($entity->bundle() == 'poi') {
        // A way to load $field.
        foreach ($this->fieldHelper->filterForPropertyPath($item->getFields(), NULL, 'vdg_city_map_poi_is_linked') as $field) {
          $node_place_ids = $this->entityTypeManager->getStorage('node')->getQuery()
            ->condition('type', 'place_page')
            ->condition('field_poi', $entity->id())
            ->condition('status', NodeInterface::PUBLISHED)
            ->range(0, 1)
            ->execute();

          if (!empty($node_place_ids)) {
            $field->addValue('1');
          }
          else {
            $field->addValue('0');
          }
        }
      }
    }

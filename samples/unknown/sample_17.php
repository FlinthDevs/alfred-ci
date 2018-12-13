<?php


    $view_mode = $this->getSetting('view_mode');
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      $elements['#total'] = count($items);
      if ($delta == 3) {
        $elements[$delta]['#more'] = '+' . (count($items) - 4);
      }

      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += ['resource' => $entity->toUrl()->toString()];
      }
    }

    return $elements;
  }


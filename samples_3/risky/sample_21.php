<?php

$content = [];
    $cache = new CacheableMetadata();

    $current_node = $this->currentRequest->attributes->get('node');

    if (is_object($current_node) && $current_node instanceof NodeInterface) {
      if ($current_node->getType() == 'directory_detail') {
        $cache->addCacheableDependency($current_node);

        $content['title_block'] = [
          '#theme' => 'vdg_content_directory_detail_title_block',
          '#name' => strtoupper($this->contentEntityHelper->getvalue($current_node, 'field_name')),
          '#first_name' => $this->contentEntityHelper->getvalue($current_node, 'field_firstname'),
        ];
      }
    }
    $cache->applyTo($content);

    return $content;

